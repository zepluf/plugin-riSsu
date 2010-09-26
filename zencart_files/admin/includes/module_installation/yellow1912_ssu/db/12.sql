SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Set Category Separator', 'SSU_CATEGORY_SEPARATOR', '/', 'Set separator to separate category names.',  @t4, 1, NOW(), NOW(), NULL, NULL);