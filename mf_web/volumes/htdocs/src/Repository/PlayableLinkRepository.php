<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Framework\Database\DbEntityManager;
use MF\Framework\DataStructure\AppObject;
use MF\Model\PlayableLinkModel;

class PlayableLinkRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private PlayableLinkModel $model,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $link): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable_link VALUES (:id, :playable_id, :name, :type, :url);');
        $dbArray = $this->em->toDbValue($link);
        $stmt->execute($dbArray);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_playable_link WHERE link_id = :?;');
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return null !== $data ? $this->em->toAppData($data, $this->model, 'link') : null;
    }

    public function remove(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable_link WHERE link_id = ?;');
        $stmt->execute([$id]);
    }

    public function update(AppObject $link): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable_link SET link_playable_id = :playable_id, link_name = :name, link_type = :type, link_url = :url WHERE link_id = :id;');
        $stmt->execute($this->em->toDbValue($link));
    }
}