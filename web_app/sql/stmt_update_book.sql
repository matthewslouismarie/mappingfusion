UPDATE e_book
SET
    book_id = :id,
    book_title = :title,
    book_introduction = :introduction
WHERE book_id = :persisted_id
;