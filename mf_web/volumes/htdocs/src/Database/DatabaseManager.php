<?php

namespace MF\Database;

use LM\WebFramework\Configuration;
use MF\Enum\LinkType;
use LM\WebFramework\Constraints\IUploadedImageConstraint;
use LM\WebFramework\Constraints\StringConstraint;
use MF\Enum\PlayableType;
use MF\Model\PlayableModel;
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
        
        if ($config->getBoolSetting('dev')) {
            $this->pdo->exec("CREATE DATABASE IF NOT EXISTS $dbName");
        }

        $this->pdo->exec('USE ' . $dbName);
        
        if ($config->getBoolSetting('dev')) {
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_author.sql'), StringConstraint::MAX_LENGTH));
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_member.sql'), StringConstraint::MAX_LENGTH));
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable.sql'), StringConstraint::MAX_LENGTH, implode(',', array_map(function ($case) {return "'$case->value'";}, PlayableType::cases()))));
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_contribution.sql'), StringConstraint::MAX_LENGTH));
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_review.sql'), StringConstraint::MAX_LENGTH));
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_category.sql'), StringConstraint::MAX_LENGTH, StringConstraint::REGEX_DASHES));
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_article.sql'), StringConstraint::MAX_LENGTH, StringConstraint::REGEX_DASHES, IUploadedImageConstraint::FILENAME_REGEX));
            $this->pdo->exec(sprintf(file_get_contents(dirname(__FILE__) . '/../../sql/e_playable_link.sql'), StringConstraint::MAX_LENGTH, Url::MAX_LENGTH, LinkType::Download->value, LinkType::HomePage->value, LinkType::Other->value));
    
            $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article.sql'));
            $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_article_published.sql'));
            $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_category.sql'));
            $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_playable.sql'));
            $this->pdo->exec(file_get_contents(dirname(__FILE__) . '/../../sql/v_person.sql'));
        }
    }

    public function getPdo(): PDO {
        return $this->pdo;
    }
}