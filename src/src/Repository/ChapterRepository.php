<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Database\DatabaseManager;
use MF\Model\ChapterModelFactory;
use MF\Model\ModelFactory;
use OutOfBoundsException;

class ChapterRepository implements IUpdatableIdRepository
{
    public function __construct(
        private ChapterModelFactory $model,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private ModelFactory $modelFactory,
    ) {

    }

    public function add(AppObject $appObject): string
    {
        $this->dbManager->run(
            'INSERT INTO e_chapter SET chapter_id = :id, chapter_book_id = :book_id, chapter_title = :title, chapter_order = :order;',
            $this->em->toDbValue($appObject),
        );

        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $stmt = $this->dbManager->run(
            'DELETE FROM e_chapter WHERE chapter_id = ?;',
            [$id],
        );
    }

    public function find(string $id): ?AppObject
    {
        $data = $this->dbManager->fetchRows(
            'SELECT * FROM v_book WHERE chapter_id = ?;',
            [$id],
        );

        if (0 === count($data)) {
            return null;
        }
        return $this->em->convertDbRowsToAppObject($data, $this->modelFactory->getChapterModel());
    }

    public function findAll(): array
    {

        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_chapter ORDER BY chapter_title;');

        $chapters = $this->em->convertDbRowsToEntityList($dbRows, $this->model->create());
        return $chapters;
    }

    public function findOne(string $id): AppObject
    {
        $chapter = $this->find($id);
        if (null === $chapter) {
            throw new OutOfBoundsException();
        }
        return $chapter;
    }

    public function update(AppObject $entity, string $persistedId): void
    {
        $stmt = $this->dbManager->getPdo()->prepare('UPDATE e_chapter SET chapter_id = :id, chapter_book_id = :book_id, chapter_order = :order, chapter_title = :title WHERE chapter_id = :persisted_id;');
        $stmt->execute($this->em->toDbValue($entity) + ['persisted_id' => $persistedId]);
    }
}