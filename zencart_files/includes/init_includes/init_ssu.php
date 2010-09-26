<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: init_ssu.php 320 2010-02-23 16:14:08Z yellow1912 $
*/
// load the default config file
require (DIR_FS_CATALOG.DIR_WS_CLASSES.'ssu/config.php');	

// load the config class
require (DIR_FS_CATALOG.DIR_WS_CLASSES.'ssu/core.php');	

if(file_exists(DIR_FS_CATALOG.DIR_WS_CLASSES.'ssu/local.config.php')){
	require (DIR_FS_CATALOG.DIR_WS_CLASSES.'ssu/local.config.php');	
	
	foreach ($ssuLocalConfig as $key => $config)
		foreach ($config as $subKey => $subConfig)
			if(!is_array($subConfig))
				$ssuConfig[$key][$subKey] = $ssuLocalConfig[$key][$subKey];
			else
				$ssuConfig[$key][$subKey] = array_merge($ssuConfig[$key][$subKey], $ssuLocalConfig[$key][$subKey]);
}

$ssuConfig['configs']['pages_excluded_list'] = explode(',', ltrim(SSU_EXCLUDE_LIST.',advanced_search_result,checkout,redirect,popup_image_additional,search,download,wordpress', ','));

$ssuConfig['configs']['queries_excluded_list'] = explode(',', ltrim(SSU_QUERY_EXCLUDE_LIST.',zenid,gclid,number_of_uploads,number_of_downloads,action,sort,page,disp_order,filter_id,alpha_filter_id,currency,keyword,search_in_description,attributes_filter_id,mix_price,max_price,payment_error,error,inc_subcat', ','));

SSUConfig::init($ssuConfig);

// load the core classes
foreach(SSUConfig::registry('cores') as $class)
	require(SSUConfig::registry('paths', 'cores')."{$class}.php");	

// set identifiers
foreach(SSUConfig::registry('identifiers') as $key => $identifier){
	if(is_array($identifier))
		foreach($identifier as $sub_key => $sub_identifier)
			$identifiers[$sub_key] = SSU_ID_DELIMITER.$sub_identifier.SSU_ID_DELIMITER;
	else
		$identifiers = SSU_ID_DELIMITER.$identifier.SSU_ID_DELIMITER;
	SSUConfig::register('identifiers', $key, $identifiers);
}

// init plugins
foreach(SSUConfig::registry('plugins') as $className => $classArray){
	foreach($classArray as $plugin){
		require(SSUConfig::registry('paths', 'plugins')."$className/{$plugin}.php");	
	}
}