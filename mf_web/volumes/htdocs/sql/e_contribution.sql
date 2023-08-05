CREATE TABLE IF NOT EXISTS e_contribution (
    contribution_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    contribution_author_id VARCHAR(%1$s) NOT NULL,
    contribution_playable_id VARCHAR(%1$s) NOT NULL,
    contribution_is_author BOOLEAN NOT NULL,
    contribution_summary VARCHAR(%1$s),
    FOREIGN KEY (contribution_playable_id) REFERENCES e_playable(playable_id),
    FOREIGN KEY (contribution_author_id) REFERENCES e_author(author_id) ON UPDATE CASCADE
)