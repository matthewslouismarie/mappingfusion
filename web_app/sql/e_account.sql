CREATE TABLE e_account (
    account_id VARCHAR(%1$s) PRIMARY KEY,
    account_password VARCHAR(%1$s) NOT NULL,
    account_author_id VARCHAR(%1$s) NOT NULL,
    account_uuid UUID NOT NULL,
    FOREIGN KEY (account_author_id) REFERENCES e_author(author_id) ON UPDATE CASCADE
)