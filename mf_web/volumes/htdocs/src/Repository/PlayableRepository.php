<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\StringModel;
use MF\DataStructure\SqlFilename;
use MF\Repository\Exception\EntityNotFoundException;
use MF\Model\ContributionModelFactory;
use MF\Model\ModelFactory;
use MF\Model\PlayableLinkModelFactory;
use MF\Model\PlayableModelFactory;

class PlayableRepository implements IRepository
{
    public function __construct(
        private ContributionRepository $contributionRepository,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
        private ModelFactory $modelFactory,
        private PlayableLinkRepository $linkRepo,
    ) {
    }

    public function add(AppObject $playable): string
    {
        $dbArray = $this->em->toDbValue($playable);
        $this->dbManager->run(
            'INSERT INTO e_playable VALUES (:id, :name, :release_date_time, :type, :game_id);',
            $dbArray,
        );
        return $this->dbManager->getLastInsertId();
    }

    public function addOrUpdate(AppObject $playable, ?string $previousId = null, bool $add = false): void {
        $dbArray = $this->em->toDbValue($playable);

        $this->dbManager->getPdo()->beginTransaction();
        if ($add) {
            $this->add($playable);
        } else {
            $this->dbManager->runFilename('stmt_update_playable', $dbArray + ['previous_id' => $previousId ?? $dbArray['id']]);
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

        $this->dbManager->getPdo()->commit();
    }

    public function delete(string $id): void {
        $stmt = $this->dbManager->getPdo()->prepare('DELETE FROM e_playable WHERE playable_id = ?;');
        $stmt->execute([$id]);
    }

    /**
     * @todo Fetch playable too
     */
    public function find(string $id): ?AppObject {
        $dbRows = $this->dbManager->fetchRowsFromQueryFile(
            new SqlFilename('stmt_find_playable_full.sql'),
            [
                'id' => $id,
            ],
        );

        if (0 === count($dbRows)) {
            return null;
        }

        $model = $this->modelFactory->getPlayableModel(contributions: true, game: true, links: true, mods: true);
        return $this->em->convertDbRowsToAppObject($dbRows, $model);
    }

    /**
     * @return AppObject[]
     */
    public function findAll(): array {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_playable;');
        $playables = $this->em->convertDbRowsToList($dbRows, $this->modelFactory->getPlayableModel());
        return $playables;
    }

    /**
     * @return AppObject[]
     */
    public function findFrom(string $authorId): array
    {
        $model = $this->modelFactory->getPlayableModel()->addProperty('article_id', new StringModel(isNullable: true));
        $dbRows = $this->dbManager->fetchRowsFromQueryFile(new SqlFilename('stmt_find_playables_from_author.sql'), [$authorId]);

        return $this->em->convertDbRowsToEntityList($dbRows, $model);
    }

    public function findOne(string $id): AppObject {

        return $this->find($id) ?? throw new EntityNotFoundException();
    }

    public function update(AppObject $entity, string $previousId): void {
        // $stmt = $this->dbManager->getPdo()->prepare('UPDATE e_playable SET ')
        $this->addOrUpdate($entity, $previousId);
    }
}