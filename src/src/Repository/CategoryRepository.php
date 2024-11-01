<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\EntityModel;
use MF\Repository\Exception\EntityNotFoundException;
use MF\Model\CategoryModelFactory;
use MF\Model\ModelFactory;
use UnexpectedValueException;

class CategoryRepository implements IUpdatableIdRepository
{
    private EntityModel $model;

    public function __construct(
        private CategoryModelFactory $categoryModelFactory,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private ModelFactory $modelFactory,
    ) {
        $this->model = $modelFactory->getCategoryModel();
    }

    public function add(AppObject $category): string
    {
        $this->dbManager->run('INSERT INTO e_category VALUES (:id, :name, :parent_id);', $this->em->toDbValue($category));
        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $stmt = $this->dbManager->run('DELETE FROM e_category WHERE category_id = ?;', [$id]);
    }

    public function find(string $id): ?AppObject
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_category WHERE category_id = ? LIMIT 1;', [$id]);
        if (0 === count($dbRows)) {
            return null;
        } elseif (1 === count($dbRows)) {
            return $this->em->convertDbRowsToAppObject($dbRows, $this->model);
        } else {
            throw new UnexpectedValueException();
        }
    }

    /**
     * @return array<string, AppObject> An array of categories, indexed by ID.
     */
    public function findAll(): array
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_category;');
        $categories = $this->em->convertDbRowsToList($dbRows, $this->model);
        $categoriesByKey = [];
        foreach ($categories as $cat) {
            $categoriesByKey[$cat[$this->model->getIdKey()]] = $cat;
        }

        return $categoriesByKey;
    }

    private function findChildren(array $categories, AppObject $parent): AppObject {
        $foundChildren = [];
        foreach ($categories as $cat) {
            if ($cat['parent_id'] == $parent['id']) {
                $foundChildren[] = $cat->set('children', $this->findChildren($categories, $cat));
            }
        }
        return $parent->set('children', $foundChildren);
    }

    public function findAllRoot(): array
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_category WHERE category_parent_id IS NULL;');
        return $this->em->convertDbRowsToList($dbRows, $this->model);
    }

    public function findOne(string $id): AppObject
    {
        return $this->find($id);
    }

    public function findWithChildren(string $id): AppObject
    {
        $dbRows = $this->dbManager->fetchRows(
            'SELECT * FROM v_category WHERE category_id = :id OR category_parent_id = :id;',
            ['id' => $id],
        );

        if (0 === count($dbRows)) {
            throw new EntityNotFoundException();
        }

        $children = [];
        $parent = null;
        foreach ($dbRows as $r) {
            if ($id === $r['category_id']) {
                $parentModel = null !== $r['category_parent_id'] ? $this->categoryModelFactory->create() : null;
                $model = $this->categoryModelFactory->create(parentCategory: $parentModel);
                $parent = $this->em->convertDbRowsToAppObject($r, $model);
                if (null === $parentModel) {
                    $parent = $parent->set('parent', null);
                }
            } else {
                $children[] = $this->em->convertDbRowsToAppObject($r, $this->model);
            }
        }
        if (null === $parent) {
            throw new UnexpectedValueException();
        }
        $parent = $parent->set('children', $children);
        return $parent;
    }

    public function update(AppObject $category, string $persistedId): void
    {
        $stmt = $this->dbManager->run(
            'UPDATE e_category SET category_id = :id, category_name = :name, category_parent_id = :parent_id WHERE category_id = :persisted_id;',
            $this->em->toDbValue($category) + ['persisted_id' => $persistedId],
        );
    }
}