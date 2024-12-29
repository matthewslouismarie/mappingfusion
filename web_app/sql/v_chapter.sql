CREATE OR REPLACE VIEW v_chapter AS SELECT
    e_chapter.*,
    e_book.*,
    e_chapter_index.chapter_index_chapter_id AS article_chapter_id,
    e_article.*
FROM e_chapter
    LEFT JOIN e_book ON chapter_book_id = book_id
    LEFT JOIN e_chapter_index ON chapter_id = chapter_index_chapter_id
    LEFT JOIN e_article ON chapter_index_article_id = article_id
;