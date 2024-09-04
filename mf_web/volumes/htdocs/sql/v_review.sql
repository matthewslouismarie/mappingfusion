CREATE OR REPLACE VIEW v_review AS SELECT
    e_review.*,
    e_playable.*
FROM e_review
LEFT JOIN e_playable ON review_playable_id = playable_id
;