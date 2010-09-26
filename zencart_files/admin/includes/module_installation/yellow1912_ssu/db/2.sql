SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Exclude list', 'SSU_EXCLUDE_LIST', 'advanced_search_result,redirect,popup_image_additional,download,wordpress', 'Set the list of pages that should be excluded from using seo style links, separated by comma with no blank space. Do not change this if you are not sure what you are doing', @t4, 1, NOW(), NOW(), NULL, NULL);