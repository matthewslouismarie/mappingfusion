CREATE OR REPLACE VIEW v_article_published AS SELECT
    *
FROM v_article
WHERE article_is_published = 1
;