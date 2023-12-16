<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Framework\Database\DbEntityManager;
use MF\Framework\DataStructures\AppObject;
use MF\Exception\Database\EntityNotFoundException;
use MF\Model\ContributionModel;
use MF\Model\PlayableLinkModel;
use MF\Model\PlayableModel;

class PlayableRepository implements IRepository
{
    public function __construct(
        private DatabaseManager $conn,
        private PlayableModel $model,
        private DbEntityManager $em,
        private PlayableLinkRepository $linkRepo,
        private ContributionRepository $contributionRepository,
    ) {
    }

    public function add(AppObject $playable): void {
        $dbArray = $this->em->toDbValue($playable);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable VALUES (:id, :name, :release_date_time, :game_id);');
        $stmt->execute($dbArray);
        $playable->set('id', $this->conn->getPdo()->lastInsertId());
    }

    public function addOrUpdate(AppObject $playable, ?string $previousId = null, bool $add = false): void {
        $dbArray = $this->em->toDbValue($playable);

        $this->conn->getPdo()->beginTransaction();
        if ($add) {
            $this->add($playable);
        } else {
            $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable SET playable_id = :id, playable_name = :name, playable_game_id = :game_id, playable_release_date_time = :release_date_time WHERE playable_id = :previous_id;');
            $stmt->execute($dbArray + ['previous_id' => $previousId ?? $dbArray['id']]);
        }

        $linkIds = [];
        foreach ($playable->links as $link) {
            $link = $link->set('playable_id', $playable->id);
            if (null === $link->id) {
                $linkIds[] = $this->linkRepo->add($link);
            } else {
                $this->linkRepo->update($link);
                $linkIds[] = $link->id;
            }
        }
        $this->linkRepo->filterOutPlayableLinks($playable->id, $linkIds);

        $contribIds = [];
        foreach ($playable->contributions as $c) {
            if (null === $c->id) {
                $contribIds[] = $this->contributionRepository->add($c);
            } else {
                $this->contributionRepository->update($c);
                $contribIds[] = $c->id;
            }
        }
        $this->contributionRepository->filterOutPlayableContributions($playable->id, $contribIds);

        $this->conn->getPdo()->commit();
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable WHERE playable_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT v_playable.*, game.playable_id AS game_id, game.playable_name AS game_name, game.playable_release_date_time AS game_release_date_time, game.playable_game_id AS game_game_id FROM v_playable LEFT JOIN e_playable AS game ON v_playable.playable_game_id = game.playable_id WHERE v_playable.playable_id = ?;');
        $stmt->execute([$id]);

        if (0 === $stmt->rowCount()) {
            return null;
        }

        $rows = $stmt->fetchAll();
        $linkModel = new PlayableLinkModel();
        $linkIds = [null];
        $links = [];
        $contributions = [];
        $contributionModel = new ContributionModel();
        $contribIds = [null];
        for ($i = 0; $i < count($rows); $i++) {
            if (!in_array($rows[$i]['link_id'], $linkIds, true)) {
                $linkIds[] = $rows[$i]['link_id'];
                $links[] = $this->em->toAppData($rows[$i], $linkModel, 'link');
            }
            if (!in_array($rows[$i]['contribution_id'], $contribIds, true)) {
                $contribIds[] = $rows[$i]['contribution_id'];
                $contributions[] = $this->em->toAppData($rows[$i], $contributionModel, 'contribution');
            }
        }

        $gameModel = null !== $rows[0]['playable_game_id'] ? new PlayableModel() : null;
        $playable = $this->em->toAppData($rows[0], new PlayableModel($gameModel), 'playable');
        return $playable->set('links', $links)->set('contributions', $contributions);
    }

    /**
     * @return AppObject[]
     */
    public function findAll(): array {
        $results = $this->conn->getPdo()->query('SELECT * FROM e_playable;')->fetchAll();
        $playables = [];
        foreach ($results as $row) {
            $playables[] = $this->em->toAppData($row, $this->model, 'playable');
        }
        return $playables;
    }

    /**
     * @return AppObject[]
     */
    public function findFrom(string $authorId): array {
        $stmt = $this->conn->getPdo()->prepare('SELECT DISTINCT * FROM v_playable WHERE author_id = ? GROUP BY playable_id;');
        $stmt->execute([$authorId]);
        $playables = [];
        foreach ($stmt->fetchAll() as $row) {
            $playables[] = $this->em->toAppData($row, $this->model, 'playable');
        }
        return $playables;
    }

    public function findOne(string $id): AppObject {

        return $this->find($id) ?? throw new EntityNotFoundException();
    }
}