<?php
/**
* @package Pages
* @copyright Copyright 2008-2009 RubikIntegration.com
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: link.php 346 2010-09-16 02:03:27Z yellow1912 $
*/
	class SSULink{
		protected $original_uri;
		protected $redirect_type	=	0;
		protected $request_uri;
		protected $current_page;
		/**
		 * Rebuilds $_GET array
		 * Takes care of redirection if needed
		 */
		public function parseURL(){
			global $request_type;

			// get out if SSU is off or this is not an index.php page		
			if(!SSUConfig::registry('configs', 'status') || (end(explode(DIRECTORY_SEPARATOR, $_SERVER["SCRIPT_FILENAME"])) != 'index.php'))
				return false;
				
			// remove the catalog dir from the link	
			$catalog_dir = SSUConfig::registry('paths', 'catalog');
			$regex = array('/'.str_replace('/','\/', $catalog_dir).'/');
			$this->request_uri = urldecode($this->requestUri());
			$this->original_uri = trim($catalog_dir=='/' ? $this->request_uri : preg_replace($regex,'', $this->request_uri, 1), '/');

			// if the index.php is in the url, lets see if we need to rebuild the path and redirect.
			if((strpos($this->original_uri, 'index.php') !== false)){
				if(!isset($_GET['main_page']) || empty($_GET['main_page'])){ 
					// we can redirect to the shop url without the index.php
					if($request_type == 'SSL' && ENABLE_SSL == 'true')
						$link = HTTPS_SERVER . DIR_WS_HTTPS_CATALOG;
					else 
						$link = HTTP_SERVER . DIR_WS_CATALOG;
						
					$this->redirect($link);
					exit();
				}
				
				else if($this->checkPageExcludedList($_GET['main_page']))
					return false;
				$this->redirect_type = 2;
				return false;
			}
						
			// if we are using multi-lang, then we should have language code at the very beginning
			if(SSUConfig::registry('configs', 'multilang_status')){
				$languages_code = current(explode('/', $this->original_uri, 2));
				
				$is_languages_code = array_key_exists($languages_code, SSUConfig::registry('languages'));

				// if this is the default language, we may need redirection here
				if(SSUConfig::registry('configs', 'multilang_default_identifier')){
					if($languages_code == DEFAULT_LANGUAGE)
						$this->redirect_type = 1;
					elseif(!$is_languages_code)
							$languages_code = DEFAULT_LANGUAGE;
					// a quick hack to redirect site.com/ru to site.com/ru/ to be consistent
					elseif(strpos($current_page_url = $this->curPageURL(), "$languages_code/") === false){
						$this->redirect($current_page_url.'/');
					}
				}
				elseif(!$is_languages_code){
					$languages_code = DEFAULT_LANGUAGE;
					$this->redirect_type = 1;
				}
				
				if($is_languages_code){
					$this->original_uri   = trim(substr($this->original_uri, 2), '/');
				}
				$ssu_get['language'] = $languages_code;
			}
				
			
			if(empty($this->original_uri)){
				$ssu_get['main_page'] = 'index';
			}
			else{			
				// if we have a link like this http://site.com/en/?blah=blahblah, we assume it is an index page
				if(substr($this->original_uri, 0, 1) == '?'){
					parse_str(trim($this->original_uri, '?'), $temp_ssu_get);
					$ssu_get = array_merge($temp_ssu_get, $ssu_get);
					$this->rebuildENV($ssu_get, $catalog_dir);
					$this->redirect_type = 1;
					return false;
				}
				
				// if we are using link alias, lets attempt to get the parsed content from cache
				if(SSUConfig::registry('configs', 'alias_status')){
					$uri_parts = explode('?', $this->original_uri);
					if(SSUAlias::linkToAlias($uri_parts[0]) > 0){

						if($request_type == 'SSL' && ENABLE_SSL == 'true')
						$this->redirect(HTTPS_SERVER . DIR_WS_HTTPS_CATALOG.implode("&", $uri_parts));
						else 
						$this->redirect(HTTP_SERVER . DIR_WS_CATALOG.implode("&", $uri_parts));
					}
					else{
						SSUAlias::aliasToLink($uri_parts[0]);
						$this->original_uri = isset($uri_parts[1]) ? $uri_parts[0].'?'.$uri_parts[1] : $uri_parts[0];
					}
				}
				
				$this->original_uri = str_replace(array('&amp;','&','=','?'),'/', $this->original_uri);

			  // explode the params link into an array
				$parts = explode('/', preg_replace('/\/\/+/', '/', $this->original_uri));		

				// identify and assign main page
				if(!isset($ssu_get['main_page'])){
					$parsers = SSUConfig::registry('plugins', 'parsers');
					foreach($parsers as $key => $parser)
						if(call_user_func_array(array("{$parser}Parser", "identifyPage"), array(&$parts, &$ssu_get))){
							unset($parsers[$key]);
							$this->redirect_type = 1;
							break;
						}
						
					// found nothing?
					if(!isset($ssu_get['main_page'])){
						$ssu_get['main_page'] = $parts[0];
						unset($parts[0]);
					}
				}

				/*
				 * This is where we loop thru the query parts and put things into their places
				 * We need to do it this way because we want to keep the generated GET array in the correct order.
				 */
				$parts 		 = array_values($parts);
				$parts_count = count($parts);
				for($counter = 0; $counter < $parts_count; $counter++){
					$parser_encountered = false;
					foreach($parsers as $key => $parser){
						if(call_user_func_array(array("{$parser}Parser", "identifyName"), array($parts[$counter]))){
							call_user_func_array(array("{$parser}Parser", "updateGet"), array($parts[$counter], &$ssu_get));
							$this->redirect_type = 1;
							$parser_encountered = true;
							unset($parsers[$key]);
							break;
						}
					}
					if(!$parser_encountered)
						$ssu_get[$parts[$counter]] = isset($parts[$counter+1]) ? $parts[++$counter] : '';
				}

				// remove extension, it's in the link just for show 
				$extension = SSUConfig::registry('configs', 'extension');
				if(!empty($extension))
					$ssu_get['main_page'] = str_replace(".$extension", '', $ssu_get['main_page']);
				}
				$this->rebuildENV($ssu_get, $catalog_dir);
			
			return true;
		}
		
		/*
		 * If our current link contains names, we want to make sure the names are correct, 
		 * otherwise we do a redirection
		 */
		public function postParseURL(){
        global $request_type;
        if($this->redirect_type==1){
             $params = '';
             // here we will attempt to rebuild the link using $_get array, and see if it matches the current link
             // we want to take out zenid however
             $page = '';
             $temp = $_GET;

             if(isset($temp['main_page'])) {$page = $temp['main_page']; unset($temp['main_page']);}
             if(SSUConfig::registry('configs', 'multilang_status') && $_SESSION['languages_code'] == $temp['language']) {unset($temp['language']);}
             // no need to include session id
             if(isset($temp[zen_session_name()])) unset($temp[zen_session_name()]);
            
             foreach($temp as $key => $value)
                $params .= '&' . $key . '=' . urlencode($value);

            $regenerated_link = $this->ssu_link($page, $params, $request_type, true, true, false, true, false);
            
        }
        elseif($this->redirect_type==2){
            $regenerated_link = $this->ssu_link($this->original_uri, '', $request_type, true, true, false, true, false);
        }

        if($regenerated_link !== false && ($this->curPageURL() != $regenerated_link)){

            $this->redirect($regenerated_link);
        }
    }  
		
		public function curPageURL() {
			global $request_type;
			if($request_type == 'SSL' && ENABLE_SSL == 'true')
				$pageURL = HTTPS_SERVER;
			else 
				$pageURL = HTTP_SERVER;

		 	return $pageURL.$this->request_uri;
		}
		
		// currently we support only 1 type of redirection: 301 permanent redirection
		protected function redirect($link){
			if($link === false) $link = SSUConfig::registry('paths', 'link');
			
			// Set POST form info / alpha testing
			if($_SERVER["REQUEST_METHOD"] == 'POST')
				$_SESSION['ssu_post'] = $_POST;
			
			Header( "HTTP/1.1 301 Moved Permanently" );
			Header( "Location: $link" );
			exit;
		}
		
		function requestUri() {
			// for iis ISAPI Rewrite
			if (isset($_SERVER['HTTP_X_REWRITE_URL'])){
				$uri = $_SERVER['HTTP_X_REWRITE_URL'];
			}
		  elseif (isset($_SERVER['REQUEST_URI'])) {
		    $uri = $_SERVER['REQUEST_URI'];
		  }
		  else {
		    if (isset($_SERVER['argv'])) {
		      $uri = $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['argv'][0];
		    }
		    elseif (isset($_SERVER['QUERY_STRING'])) {
		      $uri = $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
		    }
		    else {
		      $uri = $_SERVER['SCRIPT_NAME'];
		    }
		  }
		  return $uri;
		}

		/**
		 * Enter description here...
		 *
		 * @param unknown_type $_get
		 */
		protected function rebuildENV($ssu_get, $catalog_dir){
			$_GET = $ssu_get;
			$_REQUEST = array_merge($_REQUEST, $_GET);
			// rebuild $PHP_SELF which is used by ZC in several places
			$GLOBALS['PHP_SELF'] = $catalog_dir.'index.php';
			
			// Catch POST form info in case we were redirected here / alpha testing
			if(isset($_SESSION['ssu_post'])){
				$_POST = $_SESSION['ssu_post'];
				$_REQUEST = array_merge($_REQUEST, $_POST);
				unset($_SESSION['ssu_post']);
			}
		}
		
		/* 
		 * Builds the ssu links
		 * Takes the same params as zencart zen_href_link function
		 */
		public function ssu_link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true, $browser_safe = true){
			global $request_type, $session_started, $http_domain, $https_domain;
			$this->curent_page = $link = $sid = '';
			$languages_code = SSUConfig::registry('configs', 'languages_code');

			// if we SSU is off, we return the task to zen's original function.
			if(!SSUConfig::registry('configs', 'status'))
				return false;
				
			// if this is anything other than index.php, dont ssu it
			if(strpos($page, '.php') !== false && strpos($page, 'index.php') === false)
				return false;
			
			if(!empty($parameters) || !empty($page)){
				// this is for the way ZC builds ezpage links. $page is empty and $parameters contains main_page
				// remember. non-static links always have index.php?main_page=
				// so first we check if this is static
				if(strpos($page, 'main_page=') !== false){
					$parameters = $page;
				}
				
				// remove index.php? if exists
				if(($index_start = strpos($parameters, 'index.php?')) !== false) $parameters = substr($parameters, $index_start+10);

				// put the "page" into $page, and the rest into $parameters
				if((strpos($parameters, 'main_page=')) !== false){
					parse_str($parameters, $_get);
					$page = $_get['main_page'];
					unset($_get['main_page']);
					$parameters = http_build_query($_get);	
				}
				elseif($static && empty($parameters)){
					return false;
				}
				
				// if we reach this step with an empty $page, let zen handle the job
				if(empty($page))
					return false;
				
				$this->curent_page = $page;
				// if this page is our exclude list, let zen handle the job
				if($this->checkPageExcludedList($page)) return false;				
				
				$parameters = $this->parseParams($languages_code, $page, $parameters);
			}
					
			// Build session id
			if ( ($add_session_id == true) && ($session_started == true) && (SESSION_FORCE_COOKIE_USE == 'False') ) {
				if (defined('SID') && zen_not_null(SID)) {
					$sid = SID;
				} elseif ( ( ($request_type == 'NONSSL') && ($connection == 'SSL') && (ENABLE_SSL == 'true') ) || ( ($request_type == 'SSL') && ($connection == 'NONSSL') ) ) {
					if ($http_domain != $https_domain) {
						$sid = zen_session_name() . '=' . zen_session_id();
					}
				}
			}

			if((substr($parameters, 0, 1) == '?' || empty($parameters)) && $page == 'index')
				$page = '';
				
			// build the http://www.site.com
			if($connection == 'SSL' && ENABLE_SSL == 'true')
				$link = HTTPS_SERVER . ($use_dir_ws_catalog ? DIR_WS_HTTPS_CATALOG : '');
			else 
				$link = HTTP_SERVER . ($use_dir_ws_catalog ? DIR_WS_CATALOG : '');
			
			$link = trim($link, '/');
						
			$languages_code = SSUConfig::registry('configs', 'multilang_status') ? $languages_code : '';
			
			// append language code if:
			// multi lang is on AND we use default lang id OR We dont use default lang id but the current lang is not default
			if(SSUConfig::registry('configs', 'multilang_status') && 
						(!SSUConfig::registry('configs', 'multilang_default_identifier') ||
						 (SSUConfig::registry('configs', 'multilang_default_identifier') && $languages_code != DEFAULT_LANGUAGE)))
				$link .= "/$languages_code";

			if(!empty($page))
				if(empty($parameters)){
					$extension = SSUConfig::registry('configs', 'extension');
					$extension = empty($extension) ? $extension : ".$extension";
					$link .= "/$page$extension";
				}
				else 
					$link .= "/$page";
			
			if(!empty($parameters))
				$link .= "/$parameters";
			
			if(!empty($page))
				$link = str_replace('/?', '?', $link);
			
			// add slash if both page and param are empty
			if(empty($page) && empty($parameters)){
				$link = trim($link, '/');
				$link .= '/';
			}
				
			// append sid
			if(!empty($sid))
				$link .= (strpos($link , '?') ? '&' : '?').$sid;
				
			return $browser_safe ? str_replace('&', '&amp;', $link) : $link;
		}
		
		/* 
		 * Takes the parameters in the query string and turns that to our nice looking link
		 */
		function parseParams(&$languages_code, &$page, $parameters){
			$set_alias_cache = $set_cache	= false;
			$query_string = $params = $excluded_queries = '';
			$languages_id = SSUConfig::registry('configs', 'languages_id');
			$_get = array('main_page' => $page);
			
			if(!empty($parameters)){
				$parameters_string = trim($parameters,' ?&');
				
				// we shall not include the excluded query in our cache
				parse_str($parameters_string, $parameters);
				$excluded_queries = array();
				
				// parse language
				if(isset($parameters['language']) && !empty($parameters['language']) && ($languages_id = $this->getLanguagesID($parameters['language'])) !== false){
					$languages_code = $parameters['language'];
					if(SSUConfig::registry('configs', 'multilang_status')){
						unset($parameters['language']);
					}
				}
				
				foreach($parameters as $key => $value){
					if($value=="")
					 	unset($parameters[$key]);	
					if($this->checkQueryExcludedList($key)){
						$excluded_queries[$key] = $value;
						unset($parameters[$key]);
					}
				}
				$excluded_queries = http_build_query($excluded_queries);
			}
			
			// here we will attempt to get the cache
			// note that there is a draw back here: we are attemthing to read cache file for every single link
			// but on another hand we may avoid querying the database for aliases
			$parameters_string = !empty($parameters) ? http_build_query($parameters) : '';
			$cache_filename = md5(trim("$page&$parameters_string", "&"));
			if(($params = SSUCache::read("{$cache_filename}_{$languages_code}", 'pc', true)) !== false){
				list($page, $params) = explode("|", $params);
				return trim("$params?$excluded_queries", "?");
			}
			
			// from this point on, it means we have no cache file	
			if(!empty($parameters)){	
				// we will use this to get the cache name						
				$parsers = SSUConfig::registry('plugins', 'parsers');
				
				// kind of a hack here, but we will check if this page exisits first
				if(file_exists(DIR_FS_CATALOG.DIR_WS_MODULES."pages/$page/header_php.php")){
					foreach($parsers as $key => $parser){
						if(call_user_func_array(array("{$parser}Parser", "identifyPage2"), array(&$page, $parameters_string)) !== false){
//						$set_cache = true;
						}
						elseif(call_user_func_array(array("{$parser}Parser", "identifyParam"), array($parameters_string)) !== false){
//						$set_cache = true;
						}
						else 
							unset($parsers[$key]);
					}
					// to avoid having to querying the database for aliases, we will always cache a "real" page
					$set_cache = true;
				}
				// take out the empty variables
				$params = array();
				
				foreach($parameters as $key => $value){
					if($value == 0 || !empty($value)){
						$params[] = $key;
						$params[] = ($value);
						$_get[$key] = $value;
					}
				}
								
				$parameters = $params;
				
				$pc_id_list = array();
				foreach($parsers as $key => $parser){
					$key = call_user_func_array(array("{$parser}Parser", "getStatic"), array('identifier'));
					$pc_id_list[$key] = call_user_func_array(array("{$parser}Parser", "parseParam"), array(&$_get, &$parameters, $languages_id, $languages_code));
				}

				$params = implode('/', $parameters);	
				
				while(strpos($params,'//') !== false) $params = str_replace('//', '/', $params);
			}
			
			if(SSUConfig::registry('configs', 'alias_status')){	
				//$alias_cache_filename = md5(trim("$page/$params", "/"));
							
				if(!empty($params))
				SSUAlias::linkToAlias($params);
				if(!empty($page))
				SSUAlias::linkToAlias($page);
				// comment out to reduce cache files
				//if($set_alias_cache = !SSUCache::exists($alias_cache_filename, 'aliases', true)){
				//	SSUCache::write($alias_cache_filename, 'aliases', http_build_query($_get), true);
				//}
			}
			
			if($set_cache){			
				SSUCache::write("{$cache_filename}_{$languages_code}", 'pc', "$page|$params", true); 
				//foreach ($pc_id_list as $type => $id)
					//SSUCache::saveCachePath(array($id), $type, "{$cache_filename}_{$languages_code}");
			}
			
			// we cache the whole link so that we dont have to recalculate it again
			$params .= !empty($excluded_queries) ? '?'.trim($excluded_queries,'&') : '';

			return $params;
		}
		
		function checkPageExcludedList($string){
			if(in_array($string, SSUConfig::registry('configs', 'pages_excluded_list')))
				return true;
			return false;
		}
		
		function checkQueryExcludedList($page){
			if(in_array($page, SSUConfig::registry('configs', 'queries_excluded_list')))
				return true;
			return false;
		}
		
		function getLanguagesID($languages_code){
			if(!isset($_SESSION['ssu_languages_code'][$languages_code])){
				global $db;
				$languages_query = "select languages_id from " . TABLE_LANGUAGES . " 
			                          WHERE code = '$languages_code' LIMIT 1";
	
    		$languages = $db->Execute($languages_query);
    		if($languages->RecordCount() > 0)
    			$_SESSION['ssu_languages_code'][$languages_code] = $languages->fields['languages_id'];
    		else 
    			$_SESSION['ssu_languages_code'][$languages_code] = false;
	    			
			}
    		return $_SESSION['ssu_languages_code'][$languages_code];
		}
		
		function rel($params){
			if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($this->current_page, explode(",", constant('ROBOTS_PAGES_TO_SKIP'))) 
        || $current_page_base=='down_for_maintenance') echo "rel='nofollow $params'";
        if(!empty($params)) echo "rel='$params'"; 
		}
		
		function canonical(){
			// is this a product page?
			switch($_GET['main_page']){
				case 'index':
					
				break;
				default:
					if(key_exists($_GET['main_page'], SSUConfig::registry('identifiers', 'products')) && isset($_GET['products_id'])){
						echo zen_href_link($_GET['main_page'], 'products_id='.$_GET['main_page']);
					}
				break;
			}
	}
}