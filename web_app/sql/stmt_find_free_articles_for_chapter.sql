SELECT *
FROM e_article
LEFT JOIN e_chapter_index
ON article_id = chapter_index_article_id
WHERE chapter_index_id IS NULL;