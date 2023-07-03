CREATE TABLE IF NOT EXISTS e_playable_link (
    link_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    link_playable_id VARCHAR(%1$s) NOT NULL,
    link_name VARCHAR(%1$s) NOT NULL,
    link_type ENUM('%3$s', '%4$s', '%5$s') NOT NULL,
    link_url VARCHAR(%2$s) NOT NULL,
    FOREIGN KEY (link_playable_id) REFERENCES e_playable (playable_id) ON UPDATE CASCADE
)