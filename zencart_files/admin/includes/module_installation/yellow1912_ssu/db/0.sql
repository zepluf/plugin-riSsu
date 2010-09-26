SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';
DELETE FROM configuration WHERE configuration_group_id = @t4;
DELETE FROM configuration_group WHERE configuration_group_id = @t4;

INSERT INTO configuration_group (`configuration_group_title`,`configuration_group_description`,`sort_order`,`visible`) VALUES ('Simple SEO URL', 'Set SSU Options', '1', '1');
UPDATE configuration_group SET sort_order = last_insert_id() WHERE configuration_group_id = last_insert_id();

SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('File extension', 'SSU_FILE_EXTENSION', '', 'Set the file extension you want (without the dot). Recommend: leave it blank. For more info please read the docs', @t4, 1, NOW(), NOW(), NULL, NULL), ('Name delimiter', 'SSU_NAME_DELIMITER', '-', 'Set delimiter to replace all non alpha-numeric characters in product/category names', @t4, 1, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(\'-\', \'.\'),'), ('ID delimiter', 'SSU_ID_DELIMITER', '-', 'Set delimiter separate product/category names and their ids', @t4, 1, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(\'-\', \'.\'),');