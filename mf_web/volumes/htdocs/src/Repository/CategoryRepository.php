<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Framework\Database\DbEntityManager;
use MF\Framework\DataStructure\AppObject;
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

    public function add(AppObject $category): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_category VALUES (:id, :name);');
        $stmt->execute($this->em->toDbValue($category));
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

    public function findOne(string $id): AppObject {
        return $this->find($id);
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_category;')->fetchAll();
        $entities = [];
        foreach ($results as $r) {
            $entities[] = $this->em->toAppData($r, $this->model, 'category');
        }
        return $entities;
    }

    public function update(AppObject $category, ?string $previousId = null): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_category SET category_id = :id, category_name = :name WHERE category_id = :previous_id;');
        $stmt->execute($this->em->toDbValue($category) + ['previous_id' => $previousId ?? $category->id]);
    }
}