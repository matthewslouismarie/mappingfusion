<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\IModel;
use LM\WebFramework\Model\ListModel;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;
use MF\Database\DatabaseManager;
use MF\Model\BookModel;
use MF\Model\ChapterModel;
use OutOfBoundsException;

// It was you, Oswald!
class BookRepository implements IRepository
{
    public function __construct(
        private BookModel $model,
        private DatabaseManager $conn,
        private DbEntityManager $em,
    ) {
        $this->model = new BookModel(
            new ChapterModel(
                new AbstractEntity([
                    'id' => new SlugModel(),
                    'title' => new StringModel(),
                ])
            )
        );
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
        $stmt = $this->conn->getPdo()->prepare("SELECT * FROM v_book WHERE book_id = ?;");
        $stmt->execute([$id]);

        $rows = $stmt->fetchAll();

        if (0 === count($rows)) {
            return null;
        }

        return $this->em->toAppData($rows, $this->model, 'book');
    }

    public function findAll(): AppObject {

        $dbRows = $this->conn->getPdo()->query("SELECT * FROM v_book ORDER BY book_title;")->fetchAll();

        $books = $this->em->toAppData($dbRows, new ListModel($this->model), 'book');
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