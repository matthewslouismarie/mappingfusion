<?php

namespace MF\Database;

use LM\WebFramework\Configuration;
use LM\WebFramework\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Constraints\StringConstraint;
use MF\Enum\LinkType;
use MF\Enum\PlayableType;
use MF\Model\Url;
use PDO;
use PDOException;

class DatabaseManager
{
    const UNEXISTING_DB_CODE = '42000';

    private PDO $pdo;
    private string $dbName;
    private string $dbPwd;
    private string $dbUsername;
    private string $dbHost;

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

    public function getPdo(): PDO {
        return $this->pdo;
    }

    private function createDatabase(): void {
        $this->pdo->exec("CREATE DATABASE $this->dbName");
        
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_author.sql'), StringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_member.sql'), StringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable.sql'), StringConstraint::MAX_LENGTH, implode(',', array_map(function ($case) {return "'$case->value'";}, PlayableType::cases()))));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_contribution.sql'), StringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_review.sql'), StringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_category.sql'), StringConstraint::MAX_LENGTH, StringConstraint::REGEX_DASHES));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_book.sql'), StringConstraint::MAX_LENGTH, StringConstraint::REGEX_DASHES));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_chapter.sql'), StringConstraint::MAX_LENGTH, StringConstraint::REGEX_DASHES));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_article.sql'), StringConstraint::MAX_LENGTH, StringConstraint::REGEX_DASHES, IUploadedImageConstraint::FILENAME_REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable_link.sql'), StringConstraint::MAX_LENGTH, Url::MAX_LENGTH, LinkType::Download->value, LinkType::HomePage->value, LinkType::Other->value));

        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article_published.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_category.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_playable.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_person.sql'));
    }
}