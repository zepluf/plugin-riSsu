<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: alias.php 337 2010-06-27 06:32:47Z yellow1912 $
*/
class ZMRiSsuAlias extends ZMObject{
    // store all
    private  $aliases = array();
    private  $links = array();

    // store only enabled aliases
    private  $_aliases = array();
    private  $_links = array();

    private $no_search = array('link_alias'=>array(), 'link_url' => array());

    public function __construct(){
        $this->plugin = ZMPlugins::instance()->getPluginForId('riSsu');
    }

    public static function instance() {
        return zenmagick\base\Runtime::getContainer()->get('ZMRiSsuAlias');
    }

    // Aliases needed to be queried on demand
    public function retrieveAliases() {
        $request = zenmagick\base\Runtime::getContainer()->get('request');
        $session = $request->getSession();
        if((null != $session->getVar('ssu_aliases_created_on')) && ($session->getVar('ssu_aliases_created_on') > (int)SSU_CACHE_RESET_TIME)) {
            $aliases = ZMRuntime::getDatabase()->query('SELECT * FROM '.TABLE_LINKS_ALIASES. ' ORDER BY length(link_alias) DESC');

            foreach($aliases as $alias) {
                $this->aliases[] = $alias['link_alias'];
                $this->links[] = $alias['link_url'];

                if($alias['status'] == 1){
                    $this->_aliases[] = $alias['link_alias'];
                    $this->_links[] = $alias['link_url'];
                }
            }
            $session->setVar('ssu_aliases_created_on', time());
            $this->no_search = array('link_alias' => array(), 'link_url' => array());
        } else {
            ZMRiSsuCache::instance()->read();
        }
    }

    // Aliases needed to be loaded on demand
    public function retrieveAliasesOnDemand($params, $field, $compare, $from, $to, $status=null){
        $params = explode('/',$params);
        foreach($params as $key => $value){
            $params[$key] = "/$value/";
            if(in_array($params[$key], $this->no_search[$field]))
                unset($params[$key]);
        }
        $elements_to_query = array_diff($params, $this->$compare);
        $id_list = array();
        if(count($elements_to_query) > 0)   {
            foreach($elements_to_query as $element){
                $element = addslashes($element);
                $conditions[] = "$field LIKE '%$element%' ";
            }
            $conditions = implode(' OR ', $conditions);
            $query_string = 'SELECT DISTINCT link_url, link_alias, id FROM '.TABLE_LINKS_ALIASES." WHERE ($conditions)";
            $query_string .= !empty($status) ? " AND status = $status" : '';
            $query_string .= " ORDER BY length(link_alias) DESC";
            $aliases = ZMRuntime::getDatabase()->query($query_string);
            foreach ($aliases as $alias) {
                array_push($this->$from, $alias['link_url']);
                array_push($this->$to, $alias['link_alias']);


                $id_list[] = $alias['id'];

                unset($elements_to_query[$alias[$field]]);
            }

            foreach ($elements_to_query as $element) {
                $this->no_search[$field][] = $element;
            }
        }

        return $id_list;
    }

    public function aliasToLink(&$params){
        $count = 0;
        $this->retrieveAliasesOnDemand($params, 'link_alias', 'aliases', 'links', 'aliases');
        $params = trim(str_replace($this->aliases, $this->links, "/$params/", $count), '/');
        return $count;
    }

    public function linkToAlias(&$params){
        $count = 0;
        $this->retrieveAliasesOnDemand($params, 'link_url', '_links', '_links', '_aliases', 1);
        $params = trim(str_replace($this->_links, $this->_aliases, "/$params/", $count), '/');
        return $count;
    }

    public function autoAlias($id, $name_field, $name, $_name){

		$name = rawurldecode($name);
		$_name = rawurldecode($_name);
        // if the alias happens to be the same with a define page, return.
        if(is_dir(ZC_INSTALL_PATH . 'includes/modules/pages/' . $_name))
            return;

        $conn = ZMRuntime::getDatabase();
        // if we are generating aliases, make sure we use the product name without the attribute string
        // $name = current(explode(':', $name));
        $name = "/$name/";
        $_name = "/$_name/";

        // always update first
        $sql = "UPDATE ".TABLE_LINKS_ALIASES." SET link_url=:link_url
            WHERE referring_id=:referring_id AND link_alias=:link_alias AND alias_type=:alias_type";
        $args = array('link_url' => $name, 'referring_id' => $id, 'link_alias' => $_name, 'alias_type' => $name_field);
        $conn->update($sql, $args, TABLE_LINKS_ALIASES);

        // do we have any permanent link?
        $sql = "SELECT count(*) as count FROM ".TABLE_LINKS_ALIASES." WHERE referring_id=:referring_id
            AND alias_type=:alias_type AND permanent_link = 1 AND status = 1";
        $count = $conn->querySingle($sql, array('referring_id' => $id, 'alias_type' => $name_field), TABLE_LINKS_ALIASES);
        if($count['count'] > 0) return;

        // check if we already have this alias, then do nothing
        $sql = "SELECT count(*) as count FROM ".TABLE_LINKS_ALIASES." WHERE
            referring_id=:referring_id AND alias_type=:alias_type AND link_url=:link_url
            AND link_alias=:link_alias AND STATUS = 1";
        $args = array('link_url' => $name, 'referring_id' => $id, 'link_alias' => $_name, 'alias_type' => $name_field);
        $count = $conn->querySingle($sql, $args, TABLE_LINKS_ALIASES);
        if ($count['count'] > 0) return;

        // check if the alias with the corresponding refering_id and type is already there
        $sql = "SELECT id, link_url, link_alias FROM ".TABLE_LINKS_ALIASES."
            WHERE referring_id=:referring_id AND alias_type=:alias_type AND status = 1";
        $aliases = $conn->query($sql, array('referring_id' => $id, 'alias_type' => $name_field), TABLE_LINKS_ALIASES);
        foreach ($aliases as $links_aliases) {
            // only if we dont have this exact key pair in the database yet
            if($links_aliases['link_url'] == $name && $links_aliases['link_alias'] != $_name){
                // disable the current link-alias
                $sql = "UPDATE ".TABLE_LINKS_ALIASES." SET status = 0 WHERE id = :id";
                $args = array('id' => $link_aliases['id']);
                $conn->update($sql, $args, TABLE_LINKS_ALIASES);
                // add a new one in
                $sql = "INSERT INTO ".TABLE_LINKS_ALIASES." (link_url, link_alias, alias_type, referring_id)
                    VALUES(:link_url, :link_alias, :alias_type, :referring_id)";
                $args = array('link_url' => $name, 'link_alias' => $_name, 'alias_type' => $name_field, 'referring_id' => $id);
                $conn->update($sql, $args, TABLE_LINKS_ALIASES);
                return;
            }
        }

        // check if we already have this link url, then we update referring id and type
        $sql = "SELECT * FROM ".TABLE_LINKS_ALIASES." WHERE link_url=:link_url";
        $links_aliases = $conn->querySingle($sql, array('link_url' => $name), TABLE_LINKS_ALIASES);
        if(null != $links_aliases){
            // update the referring_id and alias_type
            if($links_aliases['referring_id'] != $id && $links_aliases['alias_type'] == $name_field)
                $sql = "UPDATE ".TABLE_LINKS_ALIASES."
                SET referring_id=:referring_id AND alias_type=:alias_type WHERE id = :id";
                $args = array('referring_id' => $id, 'alias_type' => $name_field, 'id' => $links_aliases['id']);
                $conn->update($sql, $args, TABLE_LINKS_ALIASES);

            return;
        }

        // otherwise insert the new alias
        $sql = "SELECT COUNT(*) AS count FROM ".TABLE_LINKS_ALIASES." WHERE link_url=:link_url OR link_alias = :link_alias";
        $links_aliases = $conn->querySingle($sql, array('link_url' => $name, 'link_alias' => $_name), TABLE_LINKS_ALIASES);
        if ($links_aliases['count'] == 0) {
            $sql = "INSERT INTO ".TABLE_LINKS_ALIASES." (link_url, link_alias, alias_type, referring_id)
                VALUES(:link_url, :link_alias, :alias_type, :referring_id)";
            $args = array('link_url' => $name, 'link_alias' => $_name, 'alias_type' => $name_field, 'referring_id' => $id);
            $conn->update($sql, $args, TABLE_LINKS_ALIASES);

        }
    }

    private function insertCacheToDB($id_list, $pc_file_name, $alias_file_name){
        foreach ($id_list as $id){
            // @todo should this even be here anymore?
            $sql = 'INSERT INTO '.TABLE_SSU_ALIAS_CACHE."(links_aliases_id, file) VALUES($id, $pc_file_name, $alias_file_name)";
            $args = array();
            ZMRuntime::getDatabase()->update($sql, $args, TABLE_SSU_ALIAS_CACHE);

        }
    }
}
