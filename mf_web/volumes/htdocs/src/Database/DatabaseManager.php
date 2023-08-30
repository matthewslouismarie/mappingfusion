<?php

namespace MF\Database;

use MF\Configuration;
use MF\Constraint\IFileConstraint;
use MF\Constraint\LongStringConstraint;
use MF\Constraint\SlugConstraint;
use MF\Enum\LinkType;
use MF\Model\Url;
use PDO;

class DatabaseManager
{
    private PDO $pdo;

    public function __construct(
        Configuration $config,
    ) {
        $dbName = $config->getSetting('DB_NAME');
        $dbPwd = $config->getSetting('DB_PASSWORD');
        $dbUsername = $config->getSetting('DB_USERNAME');
        $dbHost = $config->getSetting('DB_HOST');
        $this->pdo = new PDO("mysql:host=$dbHost", $dbUsername, $dbPwd, [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS $dbName");
        $this->pdo->exec('USE ' . $dbName);
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_member.sql'), LongStringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_author.sql'), LongStringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable.sql'), LongStringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_contribution.sql'), LongStringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_review.sql'), LongStringConstraint::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_category.sql'), LongStringConstraint::MAX_LENGTH, SlugConstraint::REGEX_DASHES));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_article.sql'), LongStringConstraint::MAX_LENGTH, SlugConstraint::REGEX_DASHES, IFileConstraint::FILENAME_REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable_link.sql'), LongStringConstraint::MAX_LENGTH, Url::MAX_LENGTH, LinkType::Download->value, LinkType::HomePage->value, LinkType::Other->value));

        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article.sql'));
        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_playable.sql'));
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }
}