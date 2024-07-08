CREATE OR REPLACE VIEW v_book AS SELECT
    e_book.*,
    e_chapter.*,
    e_article.article_id,
    e_article.article_chapter_id,
    e_article.article_title
FROM e_book
    LEFT JOIN e_chapter ON book_id = chapter_book_id
    LEFT JOIN e_article ON chapter_id = article_chapter_id
;