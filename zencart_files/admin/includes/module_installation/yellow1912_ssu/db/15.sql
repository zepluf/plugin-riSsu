SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Set default language identifier', 'SSU_MULTI_LANGUAGE_DEFAULT_IDENTIFIER', 'true', 'This option is useful for sites that use multi-languages. You can tell SSU to not add language identifier into the links for the default language.', @t4, 1, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');