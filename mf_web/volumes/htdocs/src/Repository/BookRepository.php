<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Database\DatabaseManager;
use MF\Model\BookModel;
use OutOfBoundsException;

// It was you, Oswald!
class BookRepository implements IRepository
{
    public function __construct(
        private BookModel $model,
        private DatabaseManager $conn,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $appObject): void {
        $dbArray = $this->em->toDbValue($appObject);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_book SET book_id = :id, book_title = :title;');
        $stmt->execute($dbArray);
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_book WHERE book_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare("SELECT * FROM e_book WHERE book_id = ?;");
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();

        if (0 === count($data)) {
            return null;
        }

        return $this->em->toAppData($data[0], $this->model, 'book');
    }

    public function findAll(): array {

        $results = $this->conn->getPdo()->query("SELECT * FROM e_book ORDER BY book_title;")->fetchAll();

        $books = [];
        foreach ($results as $r) {

            $books[] = $this->em->toAppData($r, $this->model, 'book');
        }
        return $books;
    }

    public function findOne(string $id): AppObject {
        $book = $this->find($id);
        if (null === $book) {
            throw new OutOfBoundsException();
        }
        return $book;
    }

    public function update(AppObject $appObject, ?string $previousId = null): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_book SET book_id = :id, book_title = :title WHERE book_id = :previous_id;');
        $stmt->execute(['previous_id' => $previousId ?? $appObject['id']] + $this->em->toDbValue($appObject));
    }
}