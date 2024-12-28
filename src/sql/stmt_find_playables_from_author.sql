SELECT DISTINCT
    v_playable.*,
    v_article_published.article_id AS playable_article_id
FROM v_playable
LEFT JOIN v_article_published ON v_playable.playable_id = v_article_published.playable_id
WHERE author_id = ?
GROUP BY v_playable.playable_id
;