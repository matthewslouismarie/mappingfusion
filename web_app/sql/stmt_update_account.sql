UPDATE e_account
SET
    account_id = :id,
    account_password = :password,
    account_author_id = :author_id
WHERE account_id = :persisted_id
;