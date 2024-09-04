<?php

namespace MF\Database;

use LM\WebFramework\Configuration;
use LM\WebFramework\Model\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Model\Factory\SlugModelFactory;
use LM\WebFramework\Model\Factory\UrlModelFactory;
use LM\WebFramework\Model\Factory\VarcharModelFactory;
use LM\WebFramework\Validation\Validator;
use MF\DataStructure\SqlFilename;
use MF\Enum\LinkType;
use MF\Enum\PlayableType;
use PDO;
use PDOException;
use PDOStatement;
use UnexpectedValueException;

/**
 * @todo Move part of it in lm-web-framework.
 * @todo Add caching mechanism for database queries.
 */
class DatabaseManager
{
    const UNEXISTING_DB_CODE = '42000';
    const SMALLINT_UNSIGNED_MAX = 65535;
    const TINYINT_UNSIGNED_MAX = 255;

    private PDO $pdo;
    private string $dbHost;
    private string $dbName;
    private string $dbPwd;
    private string $dbUsername;

    public function __construct(
        Configuration $config,
        private SlugModelFactory $slugModelFactory,
    ) {
        $dbConfig = $config->getConfigAppData()->getValueAsAppObject('db');
        $this->dbName = $dbConfig->getValueAsString('name');
        $this->dbPwd = $dbConfig->getValueAsString('password');
        $this->dbUsername = $dbConfig->getValueAsString('username');
        $this->dbHost = $dbConfig->getValueAsString('host');
        $this->pdo = new PDO(
            "mysql:host={$this->dbHost}",
            $this->dbUsername,
            $this->dbPwd,
            [
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        );

        try {
            $this->pdo->exec("USE {$this->dbName};");
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

    /**
     * @todo Remove.
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    private function createDatabase(): void
    {
        $this->pdo->exec("CREATE DATABASE {$this->dbName};");
        
        $this->pdo->exec("USE {$this->dbName};");

        $this->runFilename(
            'e_author.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
        );

        $this->runFilename(
            'e_member.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
        );

        $this->runFilename(
            'e_playable.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
			implode(',', array_map(function ($case) {return "'$case->value'";}, PlayableType::cases())),
        );

        $this->runFilename(
            'e_contribution.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
        );

        $this->runFilename(
            'e_review.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
        );

        $this->runFilename(
            'e_category.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
			SlugModelFactory::SLUG_REGEX,
        );

        $this->runFilename(
            'e_book.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
			SlugModelFactory::SLUG_REGEX,
        );

        $this->runFilename(
            'e_chapter.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
			SlugModelFactory::SLUG_REGEX,
        );

        $this->runFilename(
            'e_article.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
			SlugModelFactory::SLUG_REGEX,
			IUploadedImageConstraint::FILENAME_REGEX,
        );

        $this->runFilename(
            'e_chapter_index.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
			SlugModelFactory::SLUG_REGEX,
			IUploadedImageConstraint::FILENAME_REGEX,
        );
    
        $this->runFilename(
            'e_playable_link.sql',
			[],
			VarcharModelFactory::MAX_LENGTH,
			UrlModelFactory::URL_MAX_LENGTH,
			LinkType::Download->value,
			LinkType::HomePage->value,
			LinkType::Other->value,
        );

        $this->runFilename('v_book.sql');
        $this->runFilename('v_article.sql');
        $this->runFilename('v_article_published.sql');
        $this->runFilename('v_category.sql');
        $this->runFilename('v_playable.sql');
        $this->runFilename('v_person.sql');
    }

    public function dropDatabase(): void
    {
        $this->pdo->exec("DROP DATABASE {$this->dbName};");
    }

    public function prepare(string $query): PDOStatement
    {
        $stmt = $this->pdo->prepare($query);
        if (false === $stmt) {
            throw new UnexpectedValueException('PDO::prepare returned false!');
        }
        return $stmt;
    }

    public function fetchFirstRow(string $query, array $arguments): ?array
    {
        $rows = $this->fetchRows($query, $arguments, 1);
        return $rows[0] ?? null;
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

    public function fetchRowsFromQueryFile(SqlFilename $fileShortName, array $queryArgs, string ...$fileArgs): array
    {

        $filePath = realpath(dirname(__FILE__) . "/../../sql/{$fileShortName}");
        $query = sprintf(file_get_contents($filePath), ...$fileArgs);
        $stmt = $this->prepare($query);
        $stmt->execute($queryArgs);
        return $stmt->fetchAll();
    }

    public function run(string $query, array $arguments): void
    {
        $stmt = $this->prepare($query);
        $stmt->execute($arguments);
    }

    public function runFilename(string $fileShortName, array $arguments = [], string ...$fileArgs): void
    {
        (new Validator($this->slugModelFactory->getSlugModel()))->validate($fileShortName);
        $filePath = realpath(dirname(__FILE__) . "/../../sql/{$fileShortName}");
        $query = sprintf(file_get_contents($filePath), ...$fileArgs);
        $this->run($query, $arguments);
    }
}