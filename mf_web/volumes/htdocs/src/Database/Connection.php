<?php

namespace MF\Database;

use MF\Configuration;
use MF\Model\LongString;
use MF\Model\Slug;
use MF\Model\SlugFilename;
use PDO;

class Connection
{
    private PDO $pdo;

    public function __construct(
        Configuration $config,
    ) {
        $dbName = $config->getSetting('DB_NAME');
        $dbPwd = $config->getSetting('DB_PASSWORD');
        $dbUsername = $config->getSetting('DB_USERNAME');
        $dbHost = $config->getSetting('DB_HOST');
        $this->pdo = new PDO("mysql:host=$dbHost", $dbUsername, $dbPwd, [PDO::ATTR_PERSISTENT => true]);
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS ${dbName}");
        $this->pdo->exec('USE ' . $dbName);
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_member.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_author.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_contribution.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_review.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_category.sql'), LongString::MAX_LENGTH, Slug::REGEX));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_article.sql'), LongString::MAX_LENGTH, Slug::REGEX, SlugFilename::REGEX));

        $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article.sql'));
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }
}