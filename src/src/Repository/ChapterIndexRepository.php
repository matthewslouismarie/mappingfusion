<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Database\DatabaseManager;
use MF\Model\ModelFactory;

class ChapterIndexRepository implements IConstIdRepository
{
    public function __construct(
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private ModelFactory $modelFactory,
    ) {
    }

    public function add(AppObject $entity): string
    {
        $dbData = $this->em->toDbValue($entity);
        $this->dbManager->runFilename('tr_chapter_index_add.sql', $dbData);

        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_chapter_index WHERE chapter_index_id = ?;', [$id]);
    }

    public function update(AppObject $entity): void
    {
        $dbData = $this->em->toDbValue($entity);
        $this->dbManager->runFilename('tr_chapter_index_update.sql', $dbData + ['persisted_id' => $entity['id']]);
    }

    public function find(string $id): ?AppObject
    {
        $row = $this->dbManager->fetchFirstRow('SELECT * FROM e_chapter_index WHERE chapter_index_id = ?;', [$id]);
        return $this->em->convertDbRowsToAppObject($row, $this->modelFactory->getChapterIndexModel());
    }
}