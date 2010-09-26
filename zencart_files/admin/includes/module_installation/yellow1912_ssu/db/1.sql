SET @t4=0;
SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Set max category level', 'SSU_MAX_LEVEL', '2', 'When you visit sub categories, SSU will stack the name of the sub cat and their parent cats into the link. You may want to limit the number of category names should be in a link', @t4, 1, NOW(), NOW(), NULL, NULL);