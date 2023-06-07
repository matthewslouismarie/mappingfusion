CREATE TABLE IF NOT EXISTS e_category (
    category_id VARCHAR(%1$s) PRIMARY KEY CHECK(category_id REGEXP '%2$s'),
    category_name VARCHAR(%1$s) NOT NULL CHECK(category_name != '')
)