SELECT (@t4:=configuration_group_id) as t4 
FROM configuration_group
WHERE configuration_group_title= 'Simple SEO URL';

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, last_modified, date_added, use_function, set_function) VALUES ('Set Link Alias Status', 'SSU_LINK_ALIAS', 'false', 'Link alias allows you to replace any specific link by another link. After setting this to true, you can go to Admin->Extras->Simple SEO URL Manager and set link aliases', @t4, 1, NOW(), NOW(), NULL, 'zen_cfg_select_option(array(\'true\', \'false\'),');