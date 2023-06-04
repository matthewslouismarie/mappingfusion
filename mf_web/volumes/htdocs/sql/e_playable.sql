CREATE TABLE IF NOT EXISTS e_playable (
    p_id VARCHAR(%1$s) PRIMARY KEY,
    p_name VARCHAR(%1$s) NOT NULL,
    p_author_id VARCHAR(%1$s),
    p_game_id VARCHAR(%1$s),
    FOREIGN KEY (p_author_id) REFERENCES e_author (p_id) ON UPDATE CASCADE,
    FOREIGN KEY (p_game_id) REFERENCES e_playable (p_id) ON UPDATE CASCADE
)