<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Database\DbEntityManager;
use MF\DataStructure\AppObject;
use MF\DataStructure\AppObjectFactory;
use MF\Model\Category;
use MF\Model\CategoryModel;
use UnexpectedValueException;

class CategoryRepository implements IRepository
{
    public function __construct(
        private AppObjectFactory $appObjectFactory,
        private CategoryModel $model,
        private DatabaseManager $conn,
        private DbEntityManager $em,
    ) {
    }

    public function add(array $categoryScalarArray): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_category VALUES (:id, :name);');
        $stmt->execute($this->em->toDbValue($categoryScalarArray));
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
            return $this->em->toAppObject($data[0], $this->model, 'category');
        } else {
            throw new UnexpectedValueException();
        }
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_category;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppObject($r, $this->model, 'category');
        }
        return $entities;
    }

    public function update(string $previousId, array $category): void {
        if ($previousId === $category['id']) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_category SET category_name = :category_name WHERE category_id = :category_id;');
            $stmt->execute($category);
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($category);
            $this->delete($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}