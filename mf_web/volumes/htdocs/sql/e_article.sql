CREATE TABLE IF NOT EXISTS e_article (
    p_id VARCHAR(%1$s) PRIMARY KEY,
    p_author VARCHAR(%1$s) NOT NULL,
    p_title VARCHAR(%1$s) NOT NULL,
    p_content TEXT NOT NULL,
    p_creation_datetime TIMESTAMP DEFAULT NOW(),
    p_last_update_datetime TIMESTAMP,
    FOREIGN KEY (p_author) REFERENCES t_member (c_username)
)