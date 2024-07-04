<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Database\DatabaseManager;
use MF\Model\CategoryModel;
use MF\Model\ChapterModel;
use OutOfBoundsException;

class ChapterRepository implements IRepository
{
    public function __construct(
        private ChapterModel $model,
        private DatabaseManager $conn,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $appObject): void {
        $dbArray = $this->em->toDbValue($appObject);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_chapter SET chapter_id = :id, chapter_book_id = :book_id, chapter_title = :title;');
        $stmt->execute($dbArray);
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_chapter WHERE chapter_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare("SELECT * FROM e_chapter WHERE chapter_id = ?;");
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();

        if (0 === count($data)) {
            return null;
        }

        return $this->em->toAppData($data[0], $this->model, 'chapter');
    }

    public function findAll(): array {

        $results = $this->conn->getPdo()->query("SELECT * FROM e_chapter ORDER BY chapter_title;")->fetchAll();

        $chapters = [];
        foreach ($results as $r) {

            $chapters[] = $this->em->toAppData($r, $this->model, 'chapter');
        }
        return $chapters;
    }

    public function findOne(string $id): AppObject {
        $chapter = $this->find($id);
        if (null === $chapter) {
            throw new OutOfBoundsException();
        }
        return $chapter;
    }
}