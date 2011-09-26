DROP TABLE IF EXISTS links_aliases;

CREATE TABLE `links_aliases` (
  `id` int(10) NOT NULL auto_increment,
  `link_url` varchar(255) NOT NULL,
  `link_alias` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `link_url` (`link_url`)
) ENGINE=MyISAM;