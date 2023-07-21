<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Model\Playable;

class PlayableRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private PlayableLinkRepository $linkRepo,
    ) {
    }

    public function add(Playable $playable, bool $ignoreLinks = true): void {
        if ($ignoreLinks) {
            $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable VALUES (:playable_id, :playable_name, :playable_release_date_time, :playable_game_id);');
            $data = $playable->toArray();
            unset($data['playable_stored_links']);
            $stmt->execute($data);
        }
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable WHERE playable_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?Playable {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM v_playable WHERE playable_id = ?;');
        $stmt->execute([$id]);

        if (0 === $stmt->rowCount()) {
            return null;
        }
        
        $links = [];
        $firstRow = $stmt->fetch();

        $row = $firstRow;
        while (false !== $row) {
            if (null !== $row['link_playable_id']) {
                $links[] = $row;
            }
            $row = $stmt->fetch();
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

    public function update(string $previousId, Playable $playable, bool $ignoreLinks = false, array $linksToRemove = []): void {
        if ($previousId === $playable->getId()) {
            $data = $playable->toArray();
            unset($data['playable_stored_links']);

            if ($ignoreLinks) {
                $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable SET playable_name = :playable_name, playable_game_id = :playable_game_id, playable_release_date_time = :playable_release_date_time WHERE playable_id = :playable_id;');
                $stmt->execute($data);
            } else {
                $this->conn->getPdo()->beginTransaction();
                foreach ($linksToRemove as $linkId) {
                    $this->linkRepo->remove($linkId);
                }
                if (null !== $playable->getStoredLinks()) {
                    foreach ($playable->getStoredLinks() as $link) {
                        if (null === $link->getId()) {
                            $this->linkRepo->add($link);
                        } else {
                            $this->linkRepo->update($link);
                        }
                    }
                }
                $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable SET playable_name = :playable_name, playable_game_id = :playable_game_id, playable_release_date_time = :playable_release_date_time WHERE playable_id = :playable_id;');
                $stmt->execute($data);
                $this->conn->getPdo()->commit();
            }
        } else {
            $this->conn->getPdo()->beginTransaction();
            $this->add($playable);
            $this->delete($previousId);
            $this->conn->getPdo()->commit();
        }
    }
}