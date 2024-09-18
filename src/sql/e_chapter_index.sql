CREATE TABLE e_chapter_index (
    chapter_index_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chapter_index_article_id VARCHAR(%1$s) NOT NULL,
    chapter_index_chapter_id VARCHAR(%1$s) NOT NULL,
    chapter_index_order TINYINT UNSIGNED NOT NULL,
    UNIQUE (chapter_index_article_id),
    UNIQUE (chapter_index_chapter_id, chapter_index_order),
    FOREIGN KEY (chapter_index_article_id) REFERENCES e_article (article_id) ON UPDATE CASCADE,
    FOREIGN KEY (chapter_index_chapter_id) REFERENCES e_chapter (chapter_id) ON UPDATE CASCADE
);