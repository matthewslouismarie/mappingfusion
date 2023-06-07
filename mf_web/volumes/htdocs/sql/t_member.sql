CREATE TABLE IF NOT EXISTS t_member (
    member_username VARCHAR(%1$s) PRIMARY KEY,
    member_password VARCHAR(%1$s) NOT NULL
)