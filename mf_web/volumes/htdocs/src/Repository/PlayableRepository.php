<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use MF\Database\DbEntityManager;
use MF\DataStructure\AppObject;
use MF\DataStructure\AppObjectFactory;
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
        private AppObjectFactory $appObjectFactory,
    ) {
    }

    public function add(AppObject $playable): void {
        $dbArray = $this->em->toDbValue($playable);
        $stmt = $this->conn->getPdo()->prepare('INSERT INTO e_playable VALUES (:id, :name, :release_date_time, :game_id);');
        $stmt->execute($dbArray);
    }

    public function delete(string $id): void {
        $stmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable WHERE playable_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->conn->getPdo()->prepare('SELECT * FROM v_playable WHERE playable_id = ?;');
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

        $playable = $this->em->toAppData($rows[0], new PlayableModel(new PlayableModel()), 'playable');
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

    public function findOne(string $id): AppObject {

        return $this->find($id) ?? throw new EntityNotFoundException();
    }

    public function update(AppObject $playable, ?string $previousId = null): void {
        $dbArray = $this->em->toDbValue($playable);

        $this->conn->getPdo()->beginTransaction();
        $stmt = $this->conn->getPdo()->prepare('UPDATE e_playable SET playable_id = :id, playable_name = :name, playable_game_id = :game_id, playable_release_date_time = :release_date_time WHERE playable_id = :previous_id;');
        $stmt->execute($dbArray + ['previous_id' => $previousId ?? $dbArray['id']]);

        foreach ($playable->links as $link) {
            if (null === $link->id) {
                $this->linkRepo->add($link);
            } else {
                $this->linkRepo->update($link);
            }
        }
        foreach ($playable->contributions as $c) {
            if (null === $c->id) {
                $this->contributionRepository->add($c);
            } else {
                $this->contributionRepository->update($c);
            }
        }
        // foreach ($linksToRemove as $linkId) {
        //     $this->linkRepo->remove($linkId);
        // }
        // if (key_exists('links', $dbArray)) {
        //     foreach ($dbArray['links'] as $link) {
        //         if (null === $link['id']) {
        //             $this->linkRepo->add($link);
        //         } else {
        //             $this->linkRepo->update($link);
        //         }
        //     }
        //     $linkStmt = $this->conn->getPdo()->prepare('DELETE FROM e_playable_line WHERE link_playable_id = :playable_id AND link_id NOT IN :links;');
        //     $linkStmt->execute(['links' => array_map(fn ($value) => $value['id'], $dbArray['links']), 'playable_id' => $dbArray['id']]);
        // }

        $this->conn->getPdo()->commit();
    }
}