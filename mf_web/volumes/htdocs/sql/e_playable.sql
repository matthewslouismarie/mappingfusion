CREATE TABLE IF NOT EXISTS e_playable (
    playable_id VARCHAR(%1$s) PRIMARY KEY,
    playable_name VARCHAR(%1$s) NOT NULL,
    playable_release_date_time TIMESTAMP NOT NULL,
    playable_type ENUM(%2$s) NOT NULL,
    playable_game_id VARCHAR(%1$s),
    FOREIGN KEY (playable_game_id) REFERENCES e_playable (playable_id) ON UPDATE CASCADE
)