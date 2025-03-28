<?php

namespace MF\Repository;

use LM\WebFramework\DataStructures\AppList;
use MF\Database\DatabaseManager;
use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\Type\EntityModel;
use LM\WebFramework\Model\Type\StringModel;
use MF\DataStructure\SqlFilename;
use MF\Repository\Exception\EntityNotFoundException;
use MF\Model\ModelFactory;
use RuntimeException;

class PlayableRepository implements IUpdatableIdRepository
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

    public function update(AppObject $entity, ?string $persistedId = null): void
    {
        $previousEntity = $this->find($persistedId);

        $dbArray = $this->em->toDbValue($entity);

        $this->dbManager->getPdo()->beginTransaction();


        $this->dbManager->runFilename(
            'stmt_update_playable.sql',
            $dbArray + ['persisted_id' => $persistedId ?? $dbArray['id']],
        );

        $this->persistListProperty(
            $this->modelFactory->getPlayableLinkModel(),
            $this->linkRepo,
            $entity['links'],
            $previousEntity['links'],
        );

        $this->persistListProperty(
            $this->modelFactory->getContributionModel(author: false),
            $this->contributionRepository,
            $entity['contributions'],
            $previousEntity['contributions'],
        );

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

        $model = $this->modelFactory->getPlayableModel(
            contributions: true,
            game: true,
            links: true,
            mods: true,
        );
        return $this->em->convertDbRowsToAppObject($dbRows, $model, $entityRowIndex);
    }

    /**
     * @return AppList[]
     */
    public function findAll(): AppList
    {
        $dbRows = $this->dbManager->fetchRows('SELECT * FROM e_playable;');
        $playables = $this->em->convertDbRowsToList($dbRows, $this->modelFactory->getPlayableModel());
        return $playables;
    }

    /**
     * @return AppObject[]
     */
    public function findFromAuthor(string $authorId): AppList
    {
        $model = $this->modelFactory->getPlayableModel()->addProperty('article_id', new StringModel(isNullable: true));
        $dbRows = $this->dbManager->fetchRowsFromQueryFile(new SqlFilename('stmt_find_playables_from_author.sql'), [$authorId]);

        return $this->em->convertDbRowsToEntityList($dbRows, $model);
    }

    public function findOne(string $id): AppObject
    {
        return $this->find($id) ?? throw new EntityNotFoundException();
    }

    /**
     * Persists a list of entities, removing existing entities not part of the new list.
     * 
     * @param $model              The model of the entities.
     * @param $repo               The repository of the entities.
     * @param $newEntityList      The new entity list to persist.
     * @param $previousEntityList The previous, currently-persisted entity list.
     */
    public function persistListProperty(
        EntityModel $model,
        IConstIdRepository $repo,
        AppList $newEntityList,
        AppList $previousEntityList,
    ): void {
        $persistedIds = [];
        foreach ($newEntityList as $entity) {
            $entityId = $entity[$model->getIdKey()];
            $persistedEntitiesWithSameId = $previousEntityList->filter(fn ($e) => $entityId === $e[$model->getIdKey()]);
            if (null === $entityId) {
                $entityId = $repo->add($entity);
            } elseif (0 === $persistedEntitiesWithSameId->count()) {
                $repo->add($entity);
            } elseif (1 === $persistedEntitiesWithSameId->count()) {
                $persistedEntity = $persistedEntitiesWithSameId[0];
                if (!$persistedEntity->isEqual($entity)) {
                    $repo->update($entity);
                }
            } else {
                throw new RuntimeException('Multiple persisted entities were found with the same identifier.');
            }
            $persistedIds[] = $entityId;
        }
        foreach ($previousEntityList as $entity) {
            $entityId = $entity[$model->getIdKey()];
            if (!in_array($entityId, $persistedIds)) {
                $repo->delete($entityId);
            }
        }
    }
}