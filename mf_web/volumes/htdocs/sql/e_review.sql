CREATE TABLE IF NOT EXISTS e_review (
    p_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    p_playable_id VARCHAR(%1$s) NOT NULL,
    p_rating DECIMAL(3,1) UNSIGNED NOT NULL CHECK(p_rating >= 1 AND p_rating <= 5),
    p_body TEXT,
    p_cons TEXT,
    p_pros TEXT,
    FOREIGN KEY (p_playable_id) REFERENCES e_playable (p_id)
)