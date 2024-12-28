UPDATE e_member
SET
    member_id = :id,
    member_password = :password,
    member_author_id = :author_id
WHERE member_id = :persisted_id
;