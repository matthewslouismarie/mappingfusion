CREATE OR REPLACE VIEW v_book AS SELECT
    e_book.*,
    e_chapter.*,
    e_chapter_index.*,
    e_article.*
FROM e_book
    LEFT JOIN e_chapter ON book_id = chapter_book_id
    LEFT JOIN e_chapter_index ON chapter_id = chapter_index_chapter_id
    LEFT JOIN e_article ON chapter_index_article_id = article_id
;