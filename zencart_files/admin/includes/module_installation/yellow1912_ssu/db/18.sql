SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Cache reset time', 'SSU_CACHE_RESET_TIME', '', 'This value is updated automatically, do not edit', @t4, 18, NOW(), NOW(), NULL, NULL);