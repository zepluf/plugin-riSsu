DROP TABLE IF EXISTS ssu_cache;

CREATE TABLE `ssu_cache` (
  `id` int(11) NOT NULL auto_increment,
  `type` varchar(55) NOT NULL,
  `referring_id` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `type` (`type`,`referring_id`,`file`)
) ENGINE=MyISAM;