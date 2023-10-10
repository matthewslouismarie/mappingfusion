CREATE TABLE IF NOT EXISTS e_article (
    article_id VARCHAR(%1$s) PRIMARY KEY CHECK(article_id REGEXP '%2$s'),
    article_author_id VARCHAR(%1$s) NOT NULL,
    article_category_id VARCHAR(%1$s) NOT NULL,
    article_body TEXT NOT NULL,
    article_is_featured BOOLEAN NOT NULL,
    article_is_published BOOLEAN NOT NULL,
    article_sub_title VARCHAR(%1$s),
    article_title VARCHAR(%1$s) NOT NULL CHECK(article_title != ''),
    article_cover_filename VARCHAR(%1$s) NOT NULL CHECK (article_cover_filename REGEXP '%3$s'),
    article_creation_date_time TIMESTAMP NOT NULL DEFAULT NOW(),
    article_last_update_date_time TIMESTAMP NOT NULL DEFAULT NOW(),
    FOREIGN KEY (article_author_id) REFERENCES e_member (member_id) ON UPDATE CASCADE,
    FOREIGN KEY (article_category_id) REFERENCES e_category (category_id) ON UPDATE CASCADE
)