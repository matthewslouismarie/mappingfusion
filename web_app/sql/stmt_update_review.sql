UPDATE e_review
SET
    review_article_id = :article_id,
    review_playable_id = :playable_id,
    review_rating = :rating,
    review_body = :body,
    review_cons = :cons,
    review_pros = :pros
WHERE review_id = :persisted_id
;