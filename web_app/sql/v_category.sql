CREATE OR REPLACE VIEW v_category AS SELECT
    category.*,
    parent.category_id AS parent_id,
    parent.category_name AS parent_name,
    parent.category_parent_id AS parent_parent_id
FROM e_category AS category
    LEFT JOIN e_category AS parent ON category.category_parent_id = parent.category_id
;