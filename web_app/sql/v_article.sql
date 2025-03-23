CREATE OR REPLACE VIEW v_article AS SELECT
    e_article.*,
    e_author.author_id AS writer_id,
    e_author.author_name AS writer_name,
    e_category.*,
    e_book.*,
    e_chapter.*,
    e_chapter_index.*,
    e_review.*,
    e_playable.*,
    game.playable_id AS game_id,
    game.playable_name AS game_name
FROM e_article
    LEFT JOIN e_category ON article_category_id = category_id
    LEFT JOIN (e_chapter_index, e_chapter, e_book) ON (article_id = chapter_index_article_id AND chapter_index_chapter_id = chapter_id AND chapter_book_id = book_id)
    LEFT JOIN e_review ON article_id = review_article_id
    LEFT JOIN e_playable ON review_playable_id = playable_id
    LEFT JOIN e_playable AS game ON e_playable.playable_game_id = game.playable_id
    LEFT JOIN (e_account, e_author) ON (article_writer_id = account_id AND account_author_id = author_id)
;