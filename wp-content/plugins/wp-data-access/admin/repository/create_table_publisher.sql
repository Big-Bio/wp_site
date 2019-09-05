CREATE TABLE {wp_prefix}{wpda_prefix}publisher{wpda_postfix} (
  pub_id              mediumint(9)  NOT NULL AUTO_INCREMENT,
  pub_name            varchar(100)  NOT NULL,
  pub_table_name      varchar(64)   NOT NULL,
  pub_column_names    varchar(4096) DEFAULT '*',
  pub_responsive      enum('Yes', 'No'),
  pub_responsive_cols tinyint unsigned,
  pub_responsive_type enum('Modal', 'Collaped', 'Expanded'),
  pub_responsive_icon enum('Yes', 'No'),
  pub_format          text,
  PRIMARY KEY (pub_id),
  UNIQUE KEY (pub_name)
);