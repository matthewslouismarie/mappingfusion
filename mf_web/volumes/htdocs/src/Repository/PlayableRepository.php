<?php

namespace MF\Repository;

use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\StringModel;
use MF\DataStructure\SqlFilename;
use MF\Repository\Exception\EntityNotFoundException;
use MF\Model\ModelFactory;

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

    public function add(AppObject $entity): string
    {
        $dbArray = $this->em->toDbValue($entity);
        $this->dbManager->run(
            'INSERT INTO e_playable VALUES (:id, :name, :release_date_time, :type, :game_id);',
            $dbArray,
        );
        return $this->dbManager->getLastInsertId();
    }

    public function update(AppObject $entity, ?string $previousId = null): void
    {
        $previousEntity = $this->find($previousId);

        $dbArray = $this->em->toDbValue($entity);

        $this->dbManager->getPdo()->beginTransaction();


        $this->dbManager->runFilename('stmt_update_playable.sql', $dbArray + ['previous_id' => $previousId ?? $dbArray['id']]);

        $this->persistListProperty($this->linkRepo, $entity['links'], $previousEntity['links']);
        $this->persistListProperty($this->contributionRepository, $entity['contributions'], $previousEntity['contributions']);

        $this->dbManager->getPdo()->commit();
    }

    public function delete(string $id): void
    {
        $this->dbManager->run('DELETE FROM e_playable WHERE playable_id = ?;', [$id]);
    }

    public function find(string $id): ?AppObject
    {
        $dbRows = $this->dbManager->fetchRowsFromQueryFile(
            new SqlFilename('stmt_find_playable_full.sql'),
            [
                'id' => $id,
            ],
        );

        if (0 === count($dbRows)) {
            return null;
        }

        $entityRowIndex = null;
        foreach ($dbRows as $rowIndex => $row) {
            if ($row['playable_id'] === $id) {
                $entityRowIndex = $rowIndex;
                break;
            }
        }

        $model = $this->modelFactory->getPlayableModel(contributions: true, game: true, links: true, mods: true);
        return $this->em->convertDbRowsToAppObject($dbRows, $model, $entityRowIndex);
    }

    /**
     * @return AppObject[]
     */
    public function findAll(): array
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_playable;');
        $playables = $this->em->convertDbRowsToList($dbRows, $this->modelFactory->getPlayableModel());
        return $playables;
    }

    /**
     * @return AppObject[]
     */
    public function findFromAuthor(string $authorId): array
    {
        $model = $this->modelFactory->getPlayableModel()->addProperty('article_id', new StringModel(isNullable: true));
        $dbRows = $this->dbManager->fetchRowsFromQueryFile(new SqlFilename('stmt_find_playables_from_author.sql'), [$authorId]);

        return $this->em->convertDbRowsToEntityList($dbRows, $model);
    }

    public function findOne(string $id): AppObject
    {
        return $this->find($id) ?? throw new EntityNotFoundException();
    }

    public function persistListProperty(IRepository $repo, array $entities, array $previousEntities): void
    {
        $ids = [];
        foreach ($entities as $entity) {
            if (null === $entity['id']) {
                $ids[] = $repo->add($entity);
            } else {
                $repo->update($entity, $entity['id']);
                $ids[] = $entity['id'];
            }
        }
        foreach ($previousEntities as $entity) {
            if (!in_array($entity['id'], $ids, strict: true)) {
                $repo->delete($entity['id']);
            }
        }
    }
}