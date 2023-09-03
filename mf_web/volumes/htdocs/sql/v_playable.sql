CREATE OR REPLACE VIEW v_playable AS SELECT
    e_playable.*,
    e_playable_link.*,
    e_contribution.*
FROM e_playable
    LEFT JOIN e_playable_link ON playable_id = link_playable_id
    LEFT JOIN e_contribution ON playable_id = contribution_playable_id
;