CREATE TABLE IF NOT EXISTS `ssu_aliases` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) NOT NULL,
  `link_alias` varchar(255) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `alias_type` varchar(100) NOT NULL DEFAULT 'none',
  `referring_id` int(10) NOT NULL DEFAULT '0',
  `permanent_link` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `link_url` (`link_url`,`link_alias`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;