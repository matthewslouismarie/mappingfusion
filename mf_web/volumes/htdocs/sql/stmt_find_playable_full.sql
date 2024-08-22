SELECT
    v_playable.*,
    game.playable_id AS game_id,
    game.playable_name AS game_name,
    game.playable_release_date_time AS game_release_date_time,
    game.playable_game_id AS game_game_id
FROM v_playable
LEFT JOIN e_playable AS game ON v_playable.playable_game_id = game.playable_id
WHERE v_playable.playable_id = :id OR v_playable.playable_game_id = :id
;