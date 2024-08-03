<?php

namespace MF\Database;

use LM\WebFramework\Configuration;
use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Factory\UploadedImageModelFactory;
use LM\WebFramework\Model\Factory\UrlModelFactory;
use LM\WebFramework\Model\Factory\VarcharModelFactory;
use LM\WebFramework\Model\SlugModel;
use LM\WebFramework\Validator\ModelValidator;
use MF\Enum\LinkType;
use MF\Enum\PlayableType;
use PDO;
use PDOException;
use PDOStatement;
use UnexpectedValueException;

class DatabaseManager
{
    /**
     * @todo Theses const should go in lm-web-framework.
     */
    const UNEXISTING_DB_CODE = '42000';
    const SMALLINT_UNSIGNED_MAX = 65535;
    const TINYINT_UNSIGNED_MAX = 255;

    private PDO $pdo;
    private SlugModelFactory $slugModelFactory;
    private string $dbHost;
    private string $dbName;
    private string $dbPwd;
    private string $dbUsername;

    public function __construct(
        Configuration $config,
    ) {
        $this->dbName = $config->getSetting('DB_NAME');
        $this->dbPwd = $config->getSetting('DB_PASSWORD');
        $this->dbUsername = $config->getSetting('DB_USERNAME');
        $this->dbHost = $config->getSetting('DB_HOST');
        $this->pdo = new PDO("mysql:host=$this->dbHost", $this->dbUsername, $this->dbPwd, [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        try {
            $this->pdo->exec('USE ' . $this->dbName);
        } catch (PDOException $e) {
            if (self::UNEXISTING_DB_CODE !== $e->getCode()) {
                throw $e;
            }
            else {
                $this->createDatabase();
            }
        }
    }

    public function getLastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }

    private function createDatabase(): void {
        $this->pdo->exec("CREATE DATABASE $this->dbName");
        
        $this->pdo->exec('USE ' . $this->dbName . ';');

        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_author.sql'), VarcharModelFactory::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_member.sql'), VarcharModelFactory::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable.sql'), VarcharModelFactory::MAX_LENGTH, implode(',', array_map(function ($case) {return "'$case->value'";}, PlayableType::cases()))));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_contribution.sql'), VarcharModelFactory::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_review.sql'), VarcharModelFactory::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_category.sql'), VarcharModelFactory::MAX_LENGTH, SlugModelFactory::SLUG_REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_book.sql'), VarcharModelFactory::MAX_LENGTH, SlugModelFactory::SLUG_REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_chapter.sql'), VarcharModelFactory::MAX_LENGTH, SlugModelFactory::SLUG_REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_article.sql'), VarcharModelFactory::MAX_LENGTH, SlugModelFactory::SLUG_REGEX, UploadedImageModelFactory::FILENAME_REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_chapter_index.sql'), VarcharModelFactory::MAX_LENGTH, SlugModelFactory::SLUG_REGEX, UploadedImageModelFactory::FILENAME_REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable_link.sql'), VarcharModelFactory::MAX_LENGTH, UrlModelFactory::URL_MAX_LENGTH, LinkType::Download->value, LinkType::HomePage->value, LinkType::Other->value));

        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_book.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article_published.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_category.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_playable.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_person.sql'));
    }

    public function prepare(string $query): PDOStatement {
        $stmt = $this->pdo->prepare($query);
        if (false === $stmt) {
            throw new UnexpectedValueException('PDO::prepare returned false!');
        }
        return $stmt;
    }

    public function fetchRows(string $query, array $arguments = [], ?int $maxNumberOfRows = null): array
    {
        if (0 === count($arguments)) {
            $dbRows = $this->pdo->query($query)->fetchAll();
        } else {
            $stmt = $this->prepare($query);
            $stmt->execute($arguments);
            $dbRows = $stmt->fetchAll();
        }
        if (null !== $maxNumberOfRows && count($dbRows) > $maxNumberOfRows) {
            throw new UnexpectedValueException('Fetched rows exceed maximum number.');
        }
        return $dbRows;
    }

    public function fetchNullableRow(string $query, array $arguments): ?array
    {
        $rows = $this->fetchRows($query, $arguments, 1);
        return $rows[0] ?? null;
    }

    public function run(string $query, array $arguments): void
    {
        $stmt = $this->prepare($query);
        $stmt->execute($arguments);
    }

    public function runFilename(string $fileShortName, array $arguments): void
    {
        (new ModelValidator($this->slugModelFactory->getSlugModel()))->validate($fileShortName);
        $filePath = realpath(dirname(__FILE__) . "/../../sql/{$fileShortName}.sql");
        $query = file_get_contents($filePath);
        $this->run($query, $arguments);
    }
}