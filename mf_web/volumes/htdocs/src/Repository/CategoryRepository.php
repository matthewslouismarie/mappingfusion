<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Exception\Database\EntityNotFoundException;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\CategoryModel;
use UnexpectedValueException;

class CategoryRepository implements IRepository
{
    public function __construct(
        private CategoryModel $model,
        private DatabaseManager $conn,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $category): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_category VALUES (:id, :name, :parent_id);');
        $stmt->execute($this->em->toDbValue($category));
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_category WHERE category_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_category WHERE category_id = ? LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return $this->em->toAppData($data[0], $this->model, 'category');
        } else {
            throw new UnexpectedValueException();
        }
    }

    /**
     * @return array<string, AppObject> An array of categories, indexed by ID.
     */
    public function findAll(): array {
        $rows = $this->conn->getPdo()->query('SELECT * FROM e_category')->fetchAll();
        $categories = [];
        foreach ($rows as $row) {
            $c = $this->em->toAppData($row, $this->model, 'category')->set('children', []);
            $categories[$row['category_id']] = $c;
        }

        return $categories;
    }

    private function findChildren(array $categories, AppObject $parent): AppObject {
        $foundChildren = [];
        foreach ($categories as $cat) {
            if ($cat->parentId == $parent->id) {
                $foundChildren[] = $cat->set('children', $this->findChildren($categories, $cat));
            }
        }
        return $parent->set('children', $foundChildren);
    }

    public function findAllRoot(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_category WHERE category_parent_id IS NULL;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppData($r, $this->model, 'category');
        }
        return $entities;
    }

    public function findOne(string $id): AppObject {
        return $this->find($id);
    }

    public function findWithChildren(string $id): AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM v_category WHERE category_id = :id OR category_parent_id = :id;');
        $stmt->execute(['id' => $id]);

        $rows = $stmt->fetchAll();
        if (0 === count($rows)) {
            throw new EntityNotFoundException();
        }

        $children = [];
        $parent = null;
        foreach ($rows as $r) {
            if ($id === $r['category_id']) {
                $parentModel = null !== $r['category_parent_id'] ? new CategoryModel() : null;
                $model = new CategoryModel(parentCategory: $parentModel);
                $parent = $this->em->toAppData($r, $model, 'category');
                if (null === $parentModel) {
                    $parent = $parent->set('parent', null);
                }
            } else {
                $children[] = $this->em->toAppData($r, $this->model, 'category');
            }
        }
        if (null === $parent) {
            throw new UnexpectedValueException();
        }
        $parent = $parent->set('children', $children);
        return $parent;
    }

    public function update(AppObject $category, ?string $previousId = null): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_category SET category_id = :id, category_name = :name, category_parent_id = :parent_id WHERE category_id = :previous_id;');
        $stmt->execute($this->em->toDbValue($category) + ['previous_id' => $previousId ?? $category->id]);
    }
}