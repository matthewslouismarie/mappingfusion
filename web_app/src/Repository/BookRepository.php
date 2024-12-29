<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\EntityModel;
use MF\Database\DatabaseManager;
use MF\DataStructure\SqlFilename;
use MF\Model\BookModelFactory;
use OutOfBoundsException;

// It was you, Oswald!
class BookRepository implements IUpdatableIdRepository
{
    private EntityModel $model;

    public function __construct(
        private BookModelFactory $bookModelFactory,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
    ) {
        $this->model = $this->bookModelFactory->createWithChapterModel();
    }

    public function add(AppObject $appObject): string
    {
        $stmt = $this->dbManager->run(
            'INSERT INTO e_book SET book_id = :id, book_title = :title;',
            $this->em->toDbValue($appObject),
        );
        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_book WHERE book_id = ?;', [$id]);
    }

    public function find(string $id): ?AppObject
    {
        $dbRows = $this->dbManager->fetchRowsFromQueryFile(new SqlFilename('stmt_find_book.sql'), ['id' => $id]);

        if (0 === count($dbRows)) {
            return null;
        }

        return $this->em->convertDbRowsToAppObject($dbRows, $this->model);
    }

    public function findAll(): array
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM v_book ORDER BY book_title;');

        return $this->em->convertDbRowsToList($dbRows, $this->model);
    }

    public function findOne(string $id): AppObject {
        $book = $this->find($id);
        if (null === $book) {
            throw new OutOfBoundsException();
        }
        return $book;
    }

    public function update(AppObject $appObject, string $persistedId): void
    {
        $this->dbManager->runFilename(
            'stmt_update_book.sql',
            ['persisted_id' => $persistedId] + $this->em->toDbValue($appObject),
        );
    }
}