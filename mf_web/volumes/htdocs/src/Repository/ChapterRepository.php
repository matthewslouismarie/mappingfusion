<?php

namespace MF\Repository;

use LM\WebFramework\Database\DbEntityManager;
use LM\WebFramework\DataStructures\AppObject;
use LM\WebFramework\Model\AbstractEntity;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Model\StringModel;
use MF\Database\DatabaseManager;
use MF\Model\BookModelFactory;
use MF\Model\ChapterModelFactory;
use OutOfBoundsException;

class ChapterRepository implements IRepository
{
    public function __construct(
        private ChapterModelFactory $model,
        private DatabaseManager $dbManager,
        private DbEntityManager $em,
    ) {

    }

    public function add(AppObject $appObject): string
    {
        $this->dbManager->run(
            'INSERT INTO e_chapter SET chapter_id = :id, chapter_book_id = :book_id, chapter_title = :title, chapter_order = :order;',
            $this->em->toDbValue($appObject),
        );

        return $this->dbManager->getLastInsertId();
    }

    public function delete(string $id): void
    {
        $stmt = $this->dbManager->getPdo()->prepare('DELETE FROM e_chapter WHERE chapter_id = ?;');
        $stmt->execute([$id]);
    }

    public function find(string $id): ?AppObject {
        $stmt = $this->dbManager->getPdo()->prepare("SELECT * FROM v_book WHERE chapter_id = ?;");
        $stmt->execute([$id]);

        $data = $stmt->fetchAll();

        if (0 === count($data)) {
            return null;
        }

        $model = new ChapterModelFactory(
            new AbstractEntity([
                'id' => new SlugModel(),
                'title' => new StringModel(),
            ]),
            new BookModelFactory(),
        );

        return $this->em->toAppData($data, $model, 'chapter');
    }

    public function findAll(): array {

        $results = $this->dbManager->getPdo()->query("SELECT * FROM e_chapter ORDER BY chapter_title;")->fetchAll();

        $chapters = [];
        foreach ($results as $r) {

            $chapters[] = $this->em->toAppData($r, $this->model, 'chapter');
        }
        return $chapters;
    }

    public function findOne(string $id): AppObject {
        $chapter = $this->find($id);
        if (null === $chapter) {
            throw new OutOfBoundsException();
        }
        return $chapter;
    }

    public function update(AppObject $entity, string $previousId): void {
        $stmt = $this->dbManager->getPdo()->prepare('UPDATE e_chapter SET chapter_id = :id, chapter_book_id = :book_id, chapter_order = :order, chapter_title = :title WHERE chapter_id = :previous_id;');
        $stmt->execute($this->em->toDbValue($entity) + ['previous_id' => $previousId]);
    }
}