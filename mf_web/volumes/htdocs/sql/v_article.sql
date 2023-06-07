CREATE VIEW IF NOT EXISTS v_article AS SELECT
    e_article.*,
    e_category.*,
    e_playable.*,
    e_review.*
FROM e_article
    LEFT JOIN e_category ON article_category_id = category_id
    LEFT JOIN e_review ON article_review_id = review_id
    LEFT JOIN e_playable ON playable_game_id = playable_id
;