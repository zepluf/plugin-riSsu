<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php

use zenmagick\base\Runtime;

/**
 * Plugin for Simple SEO URL support.
 *
 * @package org.zenmagick.plugins.zm_ssu
 * @author DerManoMann
 */
define('TABLE_LINKS_ALIASES', DB_PREFIX.'ssu_aliases');
define('TABLE_SSU_CACHE', DB_PREFIX.'ssu_cache');
class ZMRiSsuPlugin extends Plugin {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('RI Simple SEO URL', 'Simple SEO URL for ZenMagick (using native ZenMagick functions)', '${plugin.version}');
        $this->setContext('storefront');
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }

	/**
     * {@inheritDoc}
     */
    public function install() {
        parent::install();
        
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/install.sql")), $this->messages_);

        $this->addConfigValue('Alias Status', 'aliasStatus', 'false', 'Link alias allows you to replace any specific link by another link. After setting this to true, you can go to Admin->Extras->Simple SEO URL Manager and set link aliases',
            'widget@ZMBooleanFormWidget#name=aliasStatus&default=false&label=Use alias&style=checkbox');
        
        $this->addConfigValue('Auto Alias Status', 'autoAliasStatus', 'false', 'Let SSU automatically remove identifiers from links, you have to have ssu alias on',
            'widget@ZMBooleanFormWidget#name=autoAliasStatus&default=false&label=Auto alias&style=checkbox');
        
        $this->addConfigValue('Multi language status', 'multiLanguageStatus', 'false', 'No need to turn this on unless your site uses multi-languages',
            'widget@ZMBooleanFormWidget#name=multiLanguageStatus&default=false&label=Use multilanguage&style=checkbox');
        
        $this->addConfigValue('Hide default language identifer in link', 'hideDefaultLanguageStatus', 'true', 'This option is useful for sites that use multi-languages. You can tell SSU to not add language identifier into the links for the default language',
            'widget@ZMBooleanFormWidget#name=defaultLanguageStatus&default=false&label=Include default identifer&style=checkbox');                
        
        $this->addConfigValue('File Extension', 'fileExtension', '', 'Set the file extension you want (with the dot). Recommend: leave it blank. For more info please read the docs');
        
        $this->addConfigValue('Name delimiter', 'nameDelimiter', '-', 'Set delimiter to replace all non alpha-numeric characters in product/category names',       
            'widget@ZMSelectFormWidget#name=identifier&options='.rawurlencode('.=.&-=-'));
        
        $this->addConfigValue('Id delimiter', 'idDelimiter', '-', 'Set delimiter separate product/category names and their ids',
            'widget@ZMSelectFormWidget#name=identifier&options='.rawurlencode('.=.&-=-'));
        
        $this->addConfigValue('Include category in product link', 'categoryInProductStatus', 'true', 'if you do not want category name in product link you can turn this off',
            'widget@ZMBooleanFormWidget#name=categoryInProductStatus&default=true&label=Include category&style=checkbox');
        
        $this->addConfigValue('Max Category Level', 'maxCategoryLevel', '2', 'When you visit sub categories, SSU will stack the name of the sub cat and their parent cats into the link. You may want to limit the number of category names should be in a link');
        
        $this->addConfigValue('Category separator', 'categorySeparator', '/', 'Set separator to separate category names');
                
        $this->addConfigValue('Minimum word length', 'minimumWordLength', '0', 'You can set a minimum word length here so SSU will remove any word shorter than then length from the product/category names displayed on the links. 1 or less mean no limit');
        
        $this->addConfigValue('Maximum name length', 'maximumNameLength', '0', 'You can set a maximum length here so SSU will trim your product/category names displayed on links to the set length. 0 or less means no limit');        
        
        $this->addConfigValue('Page Exclude List', 'pageExcludeList', 'advanced_search_result,redirect,popup_image_additional,download,wordpress', 'Set the list of pages that should be excluded from using seo style links, separated by comma with no blank space. Do not change this if you are not sure what you are doing');
                
    }
    
	/**
     * {@inheritDoc}
     */
    public function remove($keepSettings=false) {
        parent::remove($keepSettings);
        ZMDbUtils::executePatch(file(ZMDbUtils::resolveSQLFilename($this->getPluginDirectory()."sql/uninstall.sql")), $this->messages_);
    }
    
    /**
     * {@inheritDoc}
     */
    public function init() {
        parent::init();
        Runtime::getSettings()->add('zenmagick.http.request.urlRewriter', 'ZMSsuUrlRewriter');
                   
        // do some hacky hardcode here, we will go back and rewrite this later, hopefully
        // load the default config file
        require (dirname(__FILE__).'/lib/config.php');	
        
        $ssuConfig['exclude']['pages'] = explode(',' ,$this->get('pageExcludeList'));
        $ssuConfig['exclude']['queries'] = explode(',' ,$this->get('queryExcludeList'));
        
        // load the config class
        require (dirname(__FILE__).'/lib/core.php');	
           
        if(file_exists(dirname(__FILE__).'/lib/local.config.php')){
        	require (dirname(__FILE__).'/lib/local.config.php');	
        	
        	foreach ($ssuLocalConfig as $key => $config)
        		foreach ($config as $subKey => $subConfig)
        			if(!is_array($subConfig))
        				$ssuConfig[$key][$subKey] = $ssuLocalConfig[$key][$subKey];
        			else
        				$ssuConfig[$key][$subKey] = array_merge($ssuConfig[$key][$subKey], $ssuLocalConfig[$key][$subKey]);
        }
        
        // set identifiers
        foreach($ssuConfig['pages'] as $key => $value){
        	$ssuConfig['pages'][$key]['identifier'] = $this->get('idDelimiter').$value['identifier'].$this->get('idDelimiter');
        	if(isset($value['alias'])) $ssuConfig['aliases'][$value['alias']] = $key;
        }
        
        SSUConfig::init($ssuConfig);
    }

}
