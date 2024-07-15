UPDATE e_chapter_index
SET
    chapter_index_id = :id,
    chapter_index_article_id = :article_id,
    chapter_index_chapter_id = :chapter_id,
    chapter_index_order = :order
WHERE
    chapter_index_id = :previous_id
;