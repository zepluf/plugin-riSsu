ALTER TABLE links_aliases ADD `alias_type` varchar(100) NOT NULL DEFAULT 'none';
ALTER TABLE links_aliases ADD `referring_id` int(10) NOT NULL DEFAULT '0';