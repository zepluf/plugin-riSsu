DROP TABLE IF EXISTS links_aliases;

CREATE TABLE IF NOT EXISTS `ssu_aliases` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `link_url` varchar(255) NOT NULL,
  `link_alias` varchar(255) NOT NULL,
  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `alias_type` varchar(100) NOT NULL DEFAULT 'none',
  `referring_id` int(10) NOT NULL DEFAULT '0',
  `permanent_link` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE `link_url` ( `link_url` , `link_alias` )
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS ssu_cache;

CREATE TABLE `ssu_cache` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(55) NOT NULL,
  `referring_id` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `type` (`type`,`referring_id`,`file`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;