SELECT *
FROM v_book
WHERE book_id = :id
ORDER BY chapter_order, chapter_index_order
;