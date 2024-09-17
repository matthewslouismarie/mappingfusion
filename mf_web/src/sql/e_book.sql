CREATE TABLE e_book (
    book_id VARCHAR(%1$s) PRIMARY KEY CHECK(book_id REGEXP '%2$s'),
    book_title VARCHAR(%1$s) NOT NULL CHECK(book_title != '')
)