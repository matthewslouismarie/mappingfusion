CREATE OR REPLACE VIEW v_playable AS SELECT
    e_playable.*,
    e_playable_link.*
FROM e_playable
    LEFT JOIN e_playable_link ON playable_id = link_playable_id
;