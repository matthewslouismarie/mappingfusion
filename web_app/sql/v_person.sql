CREATE OR REPLACE VIEW v_person AS SELECT
    e_account.*,
    e_author.*
FROM e_account
    LEFT JOIN e_author ON account_author_id = author_id
;