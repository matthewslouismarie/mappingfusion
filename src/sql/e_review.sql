CREATE TABLE e_review (
    review_id SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    review_article_id VARCHAR(%1$s) NOT NULL,
    review_playable_id VARCHAR(%1$s) NOT NULL,
    review_rating DECIMAL(3,1) UNSIGNED NOT NULL CHECK(review_rating >= 1 AND review_rating <= 5),
    review_body TEXT NOT NULL,
    review_cons TEXT NOT NULL,
    review_pros TEXT NOT NULL,
    FOREIGN KEY (review_playable_id) REFERENCES e_playable (playable_id) ON UPDATE CASCADE,
    UNIQUE KEY (review_article_id)
)