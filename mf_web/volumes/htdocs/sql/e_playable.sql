CREATE TABLE IF NOT EXISTS e_playable (
    playable_id VARCHAR(%1$s) PRIMARY KEY,
    playable_name VARCHAR(%1$s) NOT NULL,
    playable_author_id VARCHAR(%1$s),
    playable_game_id VARCHAR(%1$s),
    FOREIGN KEY (playable_author_id) REFERENCES e_author (author_id) ON UPDATE CASCADE,
    FOREIGN KEY (playable_game_id) REFERENCES e_playable (playable_id) ON UPDATE CASCADE
)