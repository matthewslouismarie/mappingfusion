UPDATE e_playable
SET
    playable_id = :id,
    playable_name = :name,
    playable_type = :type,
    playable_game_id = :game_id,
    playable_release_date_time = :release_date_time
WHERE playable_id = :persisted_id
;