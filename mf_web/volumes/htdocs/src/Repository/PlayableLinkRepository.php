<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use MF\Model\PlayableLinkModelFactory;

class PlayableLinkRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private PlayableLinkModelFactory $model,
        private DbEntityManager $em,
    ) {
    }

    public function add(AppObject $link): string {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable_link VALUES (:id, :playable_id, :name, :type, :url);');
        $dbArray = $this->em->toDbValue($link);
        $stmt->execute($dbArray);
        return $this->conn->getPdo()->lastInsertId();
    }

    public function delete(string $id): void
    {
        $this->conn->run(
            'DELETE FROM e_playable_link WHERE playable_link_id = :id;',
            [
                'id' => $id,
            ],
        );
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

    public function filterOutPlayableLinks(string $playableId, array $linkIds): void {
        if (0 === count($linkIds)) {
            $delLinkStmt = $this->conn->getPdo()->prepare("DELETE FROM e_playable_link WHERE link_playable_id = ?;");
            $delLinkStmt->execute([$playableId]);
        } else {
            $inQuery = str_repeat('?,', count($linkIds) - 1) . '?';
            $delLinkStmt = $this->conn->getPdo()->prepare("DELETE FROM e_playable_link WHERE link_playable_id = ? AND link_id NOT IN ($inQuery);");
            $delLinkStmt->execute(array_merge_recursive([$playableId], $linkIds));
        }
    }

    public function update(AppObject $link, ?string $previousId = null): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable_link SET link_playable_id = :playable_id, link_name = :name, link_type = :type, link_url = :url WHERE link_id = :previous_id;');
        $stmt->execute($this->em->toDbValue($link) + ['previous_id' => $previousId ?? $link['id']]);
    }
}