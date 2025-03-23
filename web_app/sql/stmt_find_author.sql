SELECT *
FROM e_author
LEFT JOIN e_account ON author_id = account_author_id
WHERE author_id = ?
LIMIT 1
;