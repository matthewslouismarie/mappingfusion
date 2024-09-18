CREATE TABLE e_chapter (
    chapter_id VARCHAR(%1$s) PRIMARY KEY CHECK(chapter_id REGEXP '%2$s'),
    chapter_book_id VARCHAR(%1$s) NOT NULL,
    chapter_title VARCHAR(%1$s) NOT NULL CHECK(chapter_title != ''),
    chapter_order TINYINT UNSIGNED NOT NULL,
    FOREIGN KEY (chapter_book_id) REFERENCES e_book (book_id) ON UPDATE CASCADE,
    UNIQUE (chapter_book_id, chapter_order)
)