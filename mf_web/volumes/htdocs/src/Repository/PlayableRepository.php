<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\Exception\InvalidEntityArrayException;
use MF\Model\Playable;
use MF\Model\PlayableLink;

class PlayableRepository
{
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function add(Playable $playable): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable VALUES (:playable_id, :playable_name, :playable_release_date_time, :playable_game_id);');
        $stmt->execute($playable->toArray());
    }

    public function addLink(PlayableLink $link): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable_link VALUES (null, :link_playable_id, :link_name, :link_type, :link_url);');
        $data = $link->toArray();
        unset($data['link_id']);

        $stmt->execute($data);
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable WHERE playable_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Playable {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_playable LEFT JOIN e_playable_link ON link_playable_id = playable_id WHERE (playable_id = ?);');
        $stmt->execute([$id]);

        if (0 === $stmt->rowCount()) {
            return null;
        }
        
        $links = [];
        $firstRow = $stmt->fetch();

        try {
            $row = $firstRow;
            while (false !== $row) {
                $links[] = $row;
                $row = $stmt->fetch();
            }
        } catch (InvalidEntityArrayException $e) {
        }

        $playable = Playable::fromArray($firstRow + ['playable_stored_links' => $links]);

        return $playable;
    }

    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_playable;')->fetchAll();
        $playables = [];
        foreach ($results as $r) {
            $playables[] = Playable::fromArray($r);
        }
        return $playables;
    }

    public function update(string $previousId, Playable $playable): void {
        if ($previousId === $playable->getId()) {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable SET playable_name = :playable_name, playable_game_id = :playable_game_id, playable_release_date_time = :playable_release_date_time WHERE playable_id = :playable_id;');
            $stmt->execute($playable->toArray());
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($playable);
            $this->delete($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}