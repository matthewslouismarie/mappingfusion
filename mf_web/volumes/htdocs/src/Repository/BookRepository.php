<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\Type\EntityModel;
use MF\Database\DatabaseManager;
use MF\Model\BookModelFactory;
use OutOfBoundsException;

// It was you, Oswald!
class BookRepository implements IRepository
{
    private EntityModel $model;

    public function __construct(
        private BookModelFactory $bookModelFactory,
        private DatabaseManager $conn,
        private DbEntityManager $em,
    ) {
        $this->model = $this->bookModelFactory->createWithChapterModel();
    }

    public function add(AppObject $appObject): string {
        $dbArray = $this->em->toDbValue($appObject);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_book SET book_id = :id, book_title = :title;');
        $stmt->execute($dbArray);
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_book WHERE book_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $dbRows = $this->conn->fetchRows("SELECT * FROM v_book WHERE book_id = ?;", [$id]);

        if (0 === count($dbRows)) {
            return null;
        }

        return $this->em->convertDbRowsToAppObject($dbRows, $this->model);
    }

    public function findAll(): array
    {
        $dbRows = $this->conn->fetchRows("SELECT * FROM v_book ORDER BY book_title;");

        return $this->em->convertDbRowsToList($dbRows, $this->model);
    }

    public function findOne(string $id): AppObject {
        $book = $this->find($id);
        if (null === $book) {
            throw new OutOfBoundsException();
        }
        return $book;
    }

    public function update(AppObject $appObject, ?string $previousId = null): void {
        $this->conn->run(
            'UPDATE e_book SET book_id = :id, book_title = :title WHERE book_id = :previous_id;',
            ['previous_id' => $previousId ?? $appObject['id']] + $this->em->toDbValue($appObject),
        );
    }
}