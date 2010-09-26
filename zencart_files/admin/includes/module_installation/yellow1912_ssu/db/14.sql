ALTER TABLE `links_aliases` DROP INDEX `link_url`;
ALTER TABLE `links_aliases` DROP INDEX `link_alias`;
#ALTER TABLE `links_aliases` ADD UNIQUE `link_url` ( `link_url` , `link_alias` );