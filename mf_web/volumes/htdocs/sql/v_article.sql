CREATE VIEW IF NOT EXISTS v_article AS SELECT
    e_playable.p_name AS p_playable_name,
    e_playable.p_author_id AS p_playable_author_id,
    e_playable.p_game_id AS p_playable_author_id,
    e_review.*,
    e_category.p_name AS p_category_name,
    e_article.*
FROM e_article
    LEFT JOIN e_category ON article_category_id = e_category.category_id
    LEFT JOIN e_review ON article_review_id = e_review.p_id
    LEFT JOIN e_playable ON p_playable_id = e_playable.p_id
;