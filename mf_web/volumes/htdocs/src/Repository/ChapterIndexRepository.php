<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Database\DatabaseManager;
use MF\Model\ChapterIndexModel;

class ChapterIndexRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $db,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $entity): string
    {
        $dbData = $this->em->toDbValue($entity);
        $this->db->runFilename('tr_chapter_index_add', $dbData);

        return $this->db->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $this->db->run('DELETE FROM e_chapter_index WHERE chapter_index_id = ?;', [$id]);
    }

    public function update(AppObject $entity, string $previousId): void
    {
        $dbData = $this->em->toDbValue($entity);
        $this->db->runFilename('tr_chapter_index_update', $dbData + ['previous_id' => $previousId]);
    }

    public function find(string $id): ?AppObject
    {
        $row = $this->db->fetchNullableRow('SELECT * FROM e_chapter_index WHERE chapter_index_id = ?;', [$id]);
        return $this->em->toAppData($row, new ChapterIndexModel(), 'chapter_index');
    }
}