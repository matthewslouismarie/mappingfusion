SELECT
    a.*,
    e_author.*,
    e_playable_link.*,
    e_contribution.*
FROM %1$s AS a
LEFT JOIN (e_contribution, e_playable_link, e_author)
ON (playable_id = contribution_playable_id AND playable_id = link_playable_id AND contribution_author_id = author_id)
WHERE article_id = ?
;