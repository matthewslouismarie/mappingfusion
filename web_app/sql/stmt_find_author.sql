SELECT *
FROM e_author
LEFT JOIN e_member ON author_id = member_author_id
WHERE author_id = ?
LIMIT 1
;