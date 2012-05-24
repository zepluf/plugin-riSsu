<?php
/**
* @package Pages
* @copyright Copyright 2008-2009 RubikIntegration.com
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: link.php 346 2010-09-16 02:03:27Z yellow1912 $
*/
use zenmagick\base\Runtime;
use zenmagick\base\ZMObject;


class ZMRiSsuLink extends ZMObject{
    protected $original_uri;
    protected $redirect_type = 0;
    protected $request_uri;
    protected $current_page;
    protected $plugin;

    /**
     * Rebuilds $_GET array
     * Takes care of redirection if needed
     */
    public function __construct(){
        parent::__construct();
        $this->plugin = Runtime::getContainer()->get('plugins')->getPluginForId('riSsu');
    }

    public static function instance() {
        return Runtime::getContainer()->get('ZMRiSsuLink');
    }

    public function curPageURL() {
        global $request_type;
        if($request_type == 'SSL' && ENABLE_SSL == 'true')
            $pageURL = HTTPS_SERVER;
        else
            $pageURL = HTTP_SERVER;

        return $pageURL.$this->request_uri;
    }

    /*
     * Builds the ssu links
     * Takes the same params as zencart zen_href_link function
     */
    public function link($page = '', $parameters = '', $connection = 'NONSSL', $add_session_id = true, $search_engine_safe = true, $static = false, $use_dir_ws_catalog = true, $browser_safe = true){
    	$request = Runtime::getContainer()->get('request');
//        $secure = $request->isSecure();
        $this->curent_page = $link = $sid = '';

        $languages_code = Runtime::getContainer()->get('session')->getLanguageCode();

        // if this is anything other than index.php, dont ssu it
        if(strpos($page, '.php') !== false && strpos($page, 'index.php') === false) return false;

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
            if (empty($page)) return false;

            $this->curent_page = $page;
            // if this page is our exclude list, let zen handle the job
            if($this->checkPageExcludedList($page)) return false;

            $parameters = $this->parseParams($languages_code, $page, $parameters, $extension);
        }

        // Build session id
        $session = $request->getSession();
        if ( ($add_session_id == true) && $session->isStarted() && (SESSION_FORCE_COOKIE_USE == 'False') ) {
            if (defined('SID') && !ZMLangUtils::isEmpty(SID)) {
                $sid = SID;
            } elseif ( ($connection == 'SSL' && ENABLE_SSL == 'true') || ($connection == 'NONSSL') ) {
                if ($http_domain != $https_domain) { // @todo this will never work!
                    $sid = $session->getName() . '=' . $session->getId();
                }
            }
        }

        if(empty($parameters['static']) && $page == 'index')
            $page = '';

        // build the http://www.site.com
        $link = ($connection == 'SSL' && ENABLE_SSL == 'true') ? HTTPS_SERVER . DIR_WS_HTTPS_CATALOG : HTTP_SERVER . DIR_WS_CATALOG;

        $link = trim($link, '/');

        $languages_code = ZMLangUtils::asBoolean($this->plugin->get('multiLanguageStatus')) ? $languages_code : '';

        // append language code if:
        // multi lang is on AND we use default lang id OR We dont use default lang id but the current lang is not default
        if(ZMLangUtils::asBoolean($this->plugin->get('multiLanguageStatus')) &&
                    (!ZMLangUtils::asBoolean($this->plugin->get('defaultLanguageStatus')) ||
                     (ZMLangUtils::asBoolean($this->plugin->get('defaultLanguageStatus')) && $languages_code != DEFAULT_LANGUAGE)))
            $link .= "/$languages_code";

        if(!empty($page))
            $link .= '/'.$page;

        if(!empty($parameters['static']))
            $link .= '/'.$parameters['static'];

        if(!empty($page) || !empty($parameters['static'])){
            if(!empty($extension))
                $link .= $extension;
             else
                 $link .= '/';
        }
        else
            $link .= '/';

        // append sid
        if(!empty($sid))
            if(empty($parameters['dynamic']))
                $parameters['dynamic'] = $sid;
            else
                $parameters['dynamic'] .= '&'.$sid;

        if(!empty($parameters['dynamic']))
            $link .= '?'.$parameters['dynamic'];

        return $browser_safe ? str_replace('&', '&amp;', $link) : $link;
    }

    /*
     * Takes the parameters in the query string and turns that to our nice looking link
     */
    function parseParams(&$languages_code, &$page, $parameters, &$extension){
        $set_alias_cache = $set_cache = false;
        $params = $excluded_queries = array();
        $languages_id = Runtime::getContainer()->get('session')->getLanguageId();
        $_get = array('main_page' => $page);
        $extension = $this->plugin->get('fileExtension');

        // parse into an array
        parse_str($parameters, $parameters);

        // parse language
        if(isset($parameters['language']) && !empty($parameters['language']) && ($languages_id = $this->getLanguagesID($parameters['language'])) !== false){
            $languages_code = $parameters['language'];
            if(ZMLangUtils::asBoolean($this->plugin->get('multiLanguageStatus'))){
                unset($parameters['language']);
            }
        }

        // do we find this page in the parser mapping?
	$mapped_page = SSUConfig::registry('pages', $page);
        if(!empty($mapped_page)){
            $params['dynamic'] = http_build_query($mapped_page['parser']::instance()->getDynamicQueryKeys($parameters));

            $cache_filename = md5($mapped_page['parser']::instance()->getCacheKey($page, $parameters)).'_'.$languages_code;

            if(($cache = ZMRiSsuCache::instance()->read($cache_filename, 'pc', true)) !== false){
                list($page, $params['static'], $extension) = explode("|", $cache);
                return array('static' => $params['static'], 'dynamic' => $params['dynamic']);
            }
            $mapped_page['parser']::instance()->processPage($page, $mapped_page['alias']);
            $params['static'] = implode('/', $mapped_page['parser']::instance()->getStaticQueryKeys($parameters, $mapped_page['identifier'], $languages_id, $languages_code));
            $extension = isset($mapped_page['extension']) ? $mapped_page['extension'] : $this->plugin->get('fileExtension');

            $set_cache = true;
        }
        else
            $params['dynamic'] = http_build_query($parameters);

        // some alias stuffs
        if(ZMLangUtils::asBoolean($this->plugin->get('aliasStatus'))){
            if(!empty($params['static']))
            ZMRiSsuAlias::instance()->linkToAlias($params['static']);
            if(!empty($page))
            ZMRiSsuAlias::instance()->linkToAlias($page);
        }

        if($set_cache){
            ZMRiSsuCache::instance()->write($cache_filename, 'pc', $page.'|'.$params['static'].'|'.$extension, true);
        }

        // here we will attempt to get the cache
        // note that there is a draw back here: we are attemthing to read cache file for every single link
        // but on another hand we may avoid querying the database for aliases
        return array('static' => isset($params['static']) ? $params['static'] : '', 'dynamic' => isset($params['dynamic']) ? $params['dynamic'] : '');
    }

    function checkPageExcludedList($string){
        if(!empty($string) && in_array($string, SSUConfig::registry('exclude', 'pages')))
            return true;
        return false;
    }

    function getLanguagesID($languages_code){
        if(($language = Runtime::getContainer()->get('languageService')->getLanguageForCode($languages_code)) != null)
            return $language->getId();
        return false;
    }

    function rel(){
        if (defined('ROBOTS_PAGES_TO_SKIP') && in_array($this->current_page, explode(",", constant('ROBOTS_PAGES_TO_SKIP')))
        || $current_page_base=='down_for_maintenance') return "'nofollow'";
    }
}
