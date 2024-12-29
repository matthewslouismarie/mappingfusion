SELECT *
FROM e_chapter_index
LEFT JOIN (e_chapter, e_book) ON (chapter_index_chapter_id = chapter_id AND book_id = chapter_book_id)
LEFT JOIN e_article ON chapter_index_article_id = article_id
WHERE chapter_index_id = :id
;