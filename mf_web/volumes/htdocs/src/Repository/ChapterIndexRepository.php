<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Database\DatabaseManager;
use MF\Model\ChapterIndexModelFactory;

class ChapterIndexRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $entity): string
    {
        $dbData = $this->em->toDbValue($entity);
        $this->dbManager->runFilename('tr_chapter_index_add', $dbData);

        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_chapter_index WHERE chapter_index_id = ?;', [$id]);
    }

    public function update(AppObject $entity, string $previousId): void
    {
        $dbData = $this->em->toDbValue($entity);
        $this->dbManager->runFilename('tr_chapter_index_update', $dbData + ['previous_id' => $previousId]);
    }

    public function find(string $id): ?AppObject
    {
        $row = $this->dbManager->fetchNullableRow('SELECT * FROM e_chapter_index WHERE chapter_index_id = ?;', [$id]);
        return $this->em->toAppData($row, new ChapterIndexModelFactory(), 'chapter_index');
    }
}