CREATE TABLE e_fido2_credential (
    fido2_credential_id PRIMARY KEY
    fido2_credential_user_name VARCHAR(%1$s) NOT NULL,
    FOREIGN KEY (article_writer_id) REFERENCES e_member (member_id) ON UPDATE CASCADE,
)