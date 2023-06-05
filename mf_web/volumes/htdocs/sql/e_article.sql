CREATE TABLE IF NOT EXISTS e_article (
    p_id VARCHAR(%1$s) PRIMARY KEY CHECK(p_id REGEXP '%2$s'),
    p_author_username VARCHAR(%1$s) NOT NULL,
    p_category_id VARCHAR(%1$s) NOT NULL,
    p_content TEXT NOT NULL,
    p_is_featured BOOLEAN NOT NULL,
    p_title VARCHAR(%1$s) NOT NULL CHECK(p_title != ''),
    p_cover_filename VARCHAR(%1$s) CHECK (p_cover_filename REGEXP '%3$s'),
    p_creation_datetime TIMESTAMP NOT NULL DEFAULT NOW(),
    p_last_update_datetime TIMESTAMP NOT NULL,
    p_review_id SMALLINT UNSIGNED,
    FOREIGN KEY (p_author_username) REFERENCES t_member (c_username),
    FOREIGN KEY (p_category_id) REFERENCES e_category (p_id),
    FOREIGN KEY (p_review_id) REFERENCES e_review (p_id)
)