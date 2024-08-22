UPDATE e_member
SET
    member_id = :id,
    member_author_id = :author_id
WHERE member_id = :old_id
;