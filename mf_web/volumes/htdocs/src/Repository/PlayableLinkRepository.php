<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Model\PlayableLink;

class PlayableLinkRepository
{
    public function __construct(
        private DatabaseManager $conn,
    ) {
    }

    public function add(PlayableLink $link): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable_link VALUES (null, ?, ?, ?, ?);');
        $data = $link->toArray('');
        $stmt->execute([$data['playable_id'], $data['name'], $data['type'], $data['url']]);
    }

    public function remove(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable_link WHERE link_id = ?;');
        $stmt->execute([$id]);
    }

    public function update(PlayableLink $link): void {
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable_link SET link_playable_id = :playable_id, link_name = :name, link_type = :type, link_url = :url WHERE link_id = :id;');
        $stmt->execute($link->toArray(''));
    }
}