<?php

namespace MF\Repository;

use MF\Database\Connection;
use MF\Model\Playable;
use UnexpectedValueException;

class PlayableRepository
{
    public function __construct(
        private Connection $conn,
    ) {
    }

    public function add(Playable $playable): void {
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable VALUES (:playable_id, :playable_name, :playable_game_id);');
        $stmt->execute($playable->toArray());
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable WHERE playable_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Playable {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM e_playable WHERE (playable_id = ?) LIMIT 1;');
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();
        if (0 === count($data)) {
            return null;
        } elseif (1 === count($data)) {
            return Playable::fromArray($data[0]);
        } else {
            throw new UnexpectedValueException();
        }
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
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable SET playable_name = :playable_name, playable_game_id = :playable_game_id WHERE playable_id = :playable_id;');
            $stmt->execute($playable->toArray());
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($playable);
            $this->delete($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}