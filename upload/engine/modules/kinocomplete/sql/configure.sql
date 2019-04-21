DROP TABLE IF EXISTS `{{database_prefix}}{{database_configuration_table}}`;
CREATE TABLE `{{database_prefix}}{{database_configuration_table}}` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(64) UNIQUE NOT NULL DEFAULT '',
  `value` TEXT NOT NULL DEFAULT ''
) ENGINE={{database_table_engine}};

DROP TABLE IF EXISTS `{{database_prefix}}{{database_feed_posts_table}}`;
CREATE TABLE `{{database_prefix}}{{database_feed_posts_table}}` (
  `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `postId` INT UNIQUE,
  `videoId` VARCHAR(256) NOT NULL DEFAULT '',
  `videoOrigin` VARCHAR(64) NOT NULL DEFAULT ''
) ENGINE={{database_table_engine}};
