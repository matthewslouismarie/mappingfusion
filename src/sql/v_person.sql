CREATE OR REPLACE VIEW v_person AS SELECT
    e_member.*,
    e_author.*
FROM e_member
    LEFT JOIN e_author ON member_author_id = author_id
;