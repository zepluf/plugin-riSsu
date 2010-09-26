SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Set minimum word length', 'SSU_MINIMUM_WORD_LENGTH', '0', 'You can set a minimum word length here so SSU will remove any word shorter than then length from the product/category names displayed on the links. 1 or less mean no limit', @t4, 1, NOW(), NOW(), NULL, NULL);

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Set maximum name length', 'SSU_MAX_NAME_LENGTH', '0', 'You can set a maximum length here so SSU will trim your product/category names displayed on links to the set length. 0 or less means no limit', @t4, 1, NOW(), NOW(), NULL, NULL);