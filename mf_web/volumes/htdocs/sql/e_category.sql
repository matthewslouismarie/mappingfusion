CREATE TABLE IF NOT EXISTS e_category (
    p_id VARCHAR(%1$s) PRIMARY KEY CHECK(p_id REGEXP '%2$s'),
    p_name VARCHAR(%1$s) NOT NULL CHECK(p_name != '')
)