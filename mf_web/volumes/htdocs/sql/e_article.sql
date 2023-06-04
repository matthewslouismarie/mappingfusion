CREATE TABLE IF NOT EXISTS e_article (
    p_id VARCHAR(%1$s) PRIMARY KEY,
    p_author_username VARCHAR(%1$s) NOT NULL,
    p_content TEXT NOT NULL,
    p_is_featured BOOLEAN NOT NULL,
    p_title VARCHAR(%1$s) NOT NULL,
    p_cover_filename VARCHAR(%1$s),
    p_creation_datetime TIMESTAMP NOT NULL DEFAULT NOW(),
    p_last_update_datetime TIMESTAMP NOT NULL,
    p_review_id SMALLINT UNSIGNED,
    FOREIGN KEY (p_author_username) REFERENCES t_member (c_username),
    FOREIGN KEY (p_review_id) REFERENCES e_review (p_id)
)