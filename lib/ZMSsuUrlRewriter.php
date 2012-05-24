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
use zenmagick\base\Toolbox;
use zenmagick\http\request\rewriter\UrlRewriter;

/**
 * SSU rewriter.
 *
 * @author yellow1912
 * @package org.zenmagick.plugins.ssu
 */
class ZMSsuUrlRewriter implements UrlRewriter {


    public function decode($request) {
       try {
//       echo ZMRiSsuLink::instance()->link('category', 'language=jp&cPath=181');die();
//        die('testing');

        // do not do anything inside admin
        if (Toolbox::isContextMatch('admin')) return false;
        // get out if this is not an index.php page
        if (basename($_SERVER["SCRIPT_FILENAME"]) != 'index.php') return false;

        $plugin = Runtime::getContainer()->get('plugins')->getPluginForId('riSsu');

        // do not decode if this is in the excluded list
        if (Runtime::getContainer()->get('ZMRiSsuLink')->checkPageExcludedList($request->getRequestId())) return false;

        // remove the catalog dir from the link
        $catalog_dir = $request->getContext();

        $regex = array('/'.str_replace('/','\/', $catalog_dir).'/');

        $_request_uri = $this->getUri();

        $request_uri = rawurldecode($_request_uri);

        // we need to remove the extension first
        $extension = $plugin->get('fileExtension', '');
        if(!empty($extension)){
            $request_uri = str_replace($extension, '', $request_uri);
        }

        $original_uri = trim($catalog_dir=='/' ? $request_uri : preg_replace($regex,'', $request_uri, 1), '/');

        $first_param = current(explode('/', $original_uri, 2));

        // if the index.php is in the url, lets see if we need to rebuild the path and redirect.
        if((strpos($original_uri, 'index.php') !== false)){
            if(($requestId = $request->getParameter('main_page', null)) == null){
                // we can redirect to the shop url without the index.php
                $this->redirect($request->getPageBase(), $request);
            }
            else{
		        $secure = Runtime::getContainer()->get('sacsManager')->requiresSecurity($requestId) && ZMSettings::get('zenmagick.mvc.request.secure');
                if (($link = Runtime::getContainer()->get('ZMRiSsuLink')->link($requestId, http_build_query($_GET), $secure ? 'SSL' : 'NONSSL')) != false) {
                    $this->redirect($link, $request);
                }
                // else we should redirect to a page not found

            }
        }

        // if we are using multi-lang, then we should have language code at the very beginning
        if(ZMLangUtils::asBoolean($plugin->get('multiLanguageStatus'))){
            $languages_code = $first_param;

            $is_languages_code = array_key_exists($languages_code, SSUConfig::registry('languages'));

            // if this is the default language, we may need redirection here
            if(ZMLangUtils::asBoolean($plugin->get('defaultLanguageStatus'))){
                if($languages_code == ZMSettings::get('defaultLanguageCode'))
                    $redirect_type = 1;
                elseif(!$is_languages_code)
                    $languages_code = ZMSettings::get('defaultLanguageCode');
                // a quick hack to redirect site.com/ru to site.com/ru/ to be consistent
                elseif(strpos($current_page_url = $this->getUri(), "$languages_code/") === false){
                    $request->redirect($current_page_url, 301);
                }
            }
            elseif(!$is_languages_code){
                $languages_code = ZMSettings::get('defaultLanguageCode');
                $redirect_type = 1;
            }

            if($is_languages_code){
                $original_uri = trim(substr($original_uri, 2), '/');
		Runtime::getContainer()->get('session')->setLanguage(Runtime::getContainer()->get('languageService')->getLanguageForCode($languages_code));
            }
            // we commented this out and assign the language directly here because zencart will do a redirect if the language exists in GET
            // $ssu_get['language'] = $languages_code;

        }

        $ssu_get = array();

        if(empty($original_uri)){
            $ssu_get['main_page'] = 'index';
        } else {
            // if we have a link like this http://site.com/en/?blah=blahblah, we assume it is an index page
            if(substr($original_uri, 0, 1) == '?'){
                parse_str(trim($original_uri, '?'), $temp_ssu_get);
                $ssu_get = array_merge($temp_ssu_get, $ssu_get);
                $this->rebuildENV($ssu_get, $catalog_dir, $request);
                $redirect_type = 1;
                return false;
            }

            // if we are using link alias, lets attempt to get the parsed content from cache
            if(ZMLangUtils::asBoolean($plugin->get('aliasStatus'))){
                $uri_parts = explode('?', $original_uri);
                if(ZMRiSsuAlias::instance()->linkToAlias($uri_parts[0]) > 0){
                    $this->redirect($request->getPageBase().implode('&', $uri_parts));
                }
                else{
                    ZMRiSsuAlias::instance()->aliasToLink($uri_parts[0]);
                    $original_uri = isset($uri_parts[1]) ? $uri_parts[0].'?'.$uri_parts[1] : $uri_parts[0];
                }
            }

            $original_uri = str_replace(array('&amp;','&','=','?'),'/', $original_uri);

          // explode the params link into an array
            $parts = explode('/', preg_replace('/\/\/+/', '/', $original_uri));

            $pages = SSUConfig::registry('pages');
            // identify and assign main page
            if(!isset($ssu_get['main_page'])){
                // reverse alias first
                $aliases = SSUConfig::registry('aliases');
                if(isset($aliases[$parts[0]])) $parts[0] = $aliases[$parts[0]];

                foreach($pages as $page_name => $page){
                    if(strpos($parts[0], $page['identifier']) !== false){
                        $ssu_get['main_page'] = $page_name;
                        $ssu_get = array_merge($ssu_get, $page['parser']::instance()->reverseProcessParameter($parts[0]));
                        $redirect_type = 1;
                        unset($parts[0]);
                        break;
                    }
                }
                // found nothing?
                if(!isset($ssu_get['main_page'])){
                    $ssu_get['main_page'] = $parts[0];
                    unset($parts[0]);
                }
            }

            // we want to make sure there is no extra main_page query left
            if(($pos = array_search('main_page', $parts)) !== false){
                unset($parts[$pos]);
                unset($parts[$pos+1]);
            }

            /*
             * This is where we loop thru the query parts and put things into their places
             * We need to do it this way because we want to keep the generated GET array in the correct order.
             */
            $parts = array_values($parts);
            $parts_count = count($parts);
            for($counter = 0; $counter < $parts_count; $counter++){
				$parser_encountered = false;
                foreach($pages as $page_name => $page){
                    if(strpos($parts[$counter], $page['identifier']) !== false){
                        $ssu_get = array_merge($ssu_get, $page['parser']::instance()->reverseProcessParameter($parts[$counter]));
                        $redirect_type = 1;
                        $parser_encountered = true;
                        unset($parsers[$key]);
                        break;
                    }
                }
                if(!$parser_encountered)
                    $ssu_get[$parts[$counter]] = isset($parts[$counter+1]) ? $parts[++$counter] : '';
            }

            $this->rebuildENV($ssu_get, $catalog_dir, $request);
        }

        // added by mano
        global $code_page_directory;
        $code_page_directory = 'includes/modules/pages/'.$ssu_get['main_page'];

		$secure = Runtime::getContainer()->get('sacsManager')->requiresSecurity($ssu_get['main_page']) && ZMSettings::get('zenmagick.http.request.secure');

        $this->validateLink($ssu_get, $request, $_request_uri, $secure);
        return true;}catch(Exception $e){echo $e->getMessage();}
    }

    /**
     * {@inheritDoc}
     */
    public function rewrite($request, $args) {
        try {
            $requestId = $args['requestId'];
            $params = $args['params'];
            $secure = $args['secure'];
            $addSessionId = isset($args['addSessionId']) ? $args['addSessionId'] : true;
            $isStatic = isset($args['isStatic']) ? $args['isStatic'] : false;
            $useContext = isset($args['useContext']) ? $args['useContext'] : true;

            if (($link = Runtime::getContainer()->get('ZMRiSsuLink')->link($requestId, $params, $secure ? 'SSL' : 'NONSSL', $addSessionId, false, $isStatic, $useContext)) != false) {
                return $link;
            }

            return null;
        } catch(Exception $e) {
            echo $e->getMessage();
        }
    }

    /*
     * If our current link contains names, we want to make sure the names are correct,
     * otherwise we do a redirection
     */
    public function validateLink($_get, $request, $_request_uri, $secure){
         $params = '';
         // here we will attempt to rebuild the link using $_get array, and see if it matches the current link
         // we want to take out zenid however
         $page = '';

         if(isset($_get['main_page'])) {$page = $_get['main_page']; unset($_get['main_page']);}
         if(SSUConfig::registry('configs', 'multilang_status') && $request()->getLanguageCode() == $_get['language']) {unset($_get['language']);}
         // no need to include session id
         $session_name = $request->getSession()->getName();
         if(isset($_get[$session_name])) unset($_get[$session_name]);

         foreach($_get as $key => $value)
            $params .= '&' . $key . '=' . $value;

        $regenerated_link = Runtime::getContainer()->get('ZMRiSsuLink')->link($page, $params, $secure ? 'SSL' : 'NONSSL', true, true, false, true, false);

        $current_url = trim($request->getPageBase(), '/') . $_request_uri;
//		echo $regenerated_link;echo $current_url;die();
        if($regenerated_link != '' && ($current_url != $regenerated_link)){
            $this->redirect($regenerated_link, $request);
        }

        // init some stuffs for ZC
        global $current_page, $current_page_base, $code_page_directory, $page_directory;
        $current_page = $_GET['main_page'];
        $current_page_base = $current_page;
        //$code_page_directory = DIR_WS_MODULES . 'pages/' . $current_page_base;
        //$page_directory = $code_page_directory;
    }

    protected function redirect($link, $request){
        // Set POST form info / alpha testing
        if($_SERVER["REQUEST_METHOD"] == 'POST')
            $_SESSION['ssu_post'] = $_POST;
        $request->redirect($link, 302);
        exit();
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $_get
     */
    protected function rebuildENV($ssu_get, $catalog_dir, $request){
        $_GET = array_merge($_GET, $ssu_get);
        $_REQUEST = array_merge($_REQUEST, $_GET);
        // rebuild $PHP_SELF which is used by ZC in several places
        $GLOBALS['PHP_SELF'] = $catalog_dir.'index.php';

        // set zm request parameter
        foreach($ssu_get as $key => $value)
            $request->setParameter($key, $value);

        // Catch POST form info in case we were redirected here / alpha testing
        if(isset($_SESSION['ssu_post'])){
            $_POST = $_SESSION['ssu_post'];
            $_REQUEST = array_merge($_REQUEST, $_POST);
            unset($_SESSION['ssu_post']);
        }
    }

    function getUri() {
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
}
