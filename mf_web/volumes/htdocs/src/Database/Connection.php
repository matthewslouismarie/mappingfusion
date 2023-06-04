<?php

namespace MF\Database;

use MF\Configuration;
use MF\Model\LongString;
use PDO;

class Connection
{
    private PDO $pdo;

    public function __construct(Configuration $config) {
        $dbName = $config->getSetting('DB_NAME');
        $dbPwd = $config->getSetting('DB_ROOT_PWD');
        $this->pdo = new PDO("mysql:host=mf_db", 'root', $dbPwd, [PDO::ATTR_PERSISTENT => true]);
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS ${dbName}");
        $this->pdo->exec('USE ' . $dbName);
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/t_member.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_author.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_review.sql'), LongString::MAX_LENGTH));
        $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_article.sql'), LongString::MAX_LENGTH));
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }
}