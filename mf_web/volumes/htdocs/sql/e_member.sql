CREATE TABLE IF NOT EXISTS e_member (
    member_id VARCHAR(%1$s) PRIMARY KEY,
    member_password VARCHAR(%1$s) NOT NULL,
    member_author_id VARCHAR(%1$s) NOT NULL,
    FOREIGN KEY (member_author_id) REFERENCES e_author(author_id) ON UPDATE CASCADE
)