CREATE OR REPLACE VIEW v_article_published AS SELECT
    e_article.*,
    e_category.*,
    e_playable.*,
    e_review.*,
    game.playable_id AS game_id,
    game.playable_name AS game_name,
    game.playable_release_date_time AS game_release_date_time,
    game.playable_game_id AS game_game_id
FROM e_article
    LEFT JOIN e_category ON article_category_id = category_id
    LEFT JOIN e_review ON article_id = review_article_id
    LEFT JOIN e_playable ON review_playable_id = playable_id
    LEFT JOIN e_playable AS game ON e_playable.playable_game_id = game.playable_id
WHERE article_is_published = 1
;