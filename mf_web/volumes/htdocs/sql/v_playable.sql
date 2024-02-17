CREATE OR REPLACE VIEW v_playable AS SELECT
    playable.*,
    game.playable_id AS game_id,
    game.playable_name AS game_name,
    game.playable_release_date_time AS game_release_date_time,
    game.playable_type AS game_type,
    game.playable_game_id AS game_game_id,
    e_playable_link.*,
    e_contribution.*,
    e_author.*
FROM e_playable AS playable
    LEFT JOIN e_playable_link ON playable.playable_id = link_playable_id
    LEFT JOIN e_contribution ON playable.playable_id = contribution_playable_id
    LEFT JOIN e_author ON contribution_author_id = author_id
    LEFT JOIN e_playable AS game ON playable.playable_game_id = game.playable_id
;