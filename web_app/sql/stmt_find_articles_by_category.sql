WITH RECURSIVE CategoryHierarchy AS (
    SELECT
        category_id,
        category_name,
        category_parent_id
    FROM e_category AS rootcat
    WHERE rootcat.category_id = ?

    UNION ALL

    SELECT
        descendantcat.category_id,
        descendantcat.category_name,
        descendantcat.category_parent_id
    FROM e_category AS descendantcat
    JOIN CategoryHierarchy ch ON descendantcat.category_parent_id = ch.category_id
)
SELECT *
FROM v_article_published AS a
JOIN CategoryHierarchy ch ON a.article_category_id = ch.category_id
ORDER BY article_creation_date_time
DESC
;