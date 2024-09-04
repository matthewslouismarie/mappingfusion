UPDATE e_author
SET
    author_id = :id,
    author_name = :name,
    author_avatar_filename = :avatar_filename
WHERE author_id = :previous_id
;