UPDATE e_playable_link
SET
    link_playable_id = :playable_id,
    link_name = :name,
    link_type = :type,
    link_url = :url
WHERE link_id = :id
;