CREATE OR REPLACE VIEW v_article AS SELECT
    e_article.*,
    e_member.member_id AS writer_id,
    e_member.member_author_id AS writer_author_id,
    e_category.*,
    e_chapter_index.*,
    e_review.*,
    e_playable.*,
    game.playable_id AS game_id,
    game.playable_name AS game_name
FROM e_article
    LEFT JOIN e_category ON article_category_id = category_id
    LEFT JOIN e_chapter_index ON article_id = chapter_index_article_id
    LEFT JOIN e_review ON article_id = review_article_id
    LEFT JOIN e_playable ON review_playable_id = playable_id
    LEFT JOIN e_playable AS game ON e_playable.playable_game_id = game.playable_id
    LEFT JOIN (e_member, e_author) ON (article_writer_id = member_id AND member_author_id = author_id)
;