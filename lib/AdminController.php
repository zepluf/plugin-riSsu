<?php

namespace plugins\riSsu;

use Symfony\Component\HttpFoundation\Request;

use plugins\riSimplex\Controller;
use plugins\riPlugin\Plugin;
use Symfony\Component\HttpFoundation\Response;

class AdminController extends Controller{

    public function __construct(){
        parent::__construct();
    }

    public function indexAction(Request $request){
        $this->view->getHelper('php::holder')->add('main', $this->view->render('riSsu::index.php'));
        return $this->render('riSsu::admin_layout');
    }

    public function aliasListAction(Request $request){
        global $db;
        $page = $_REQUEST['page'];  // get the requested page
        $limit = $_REQUEST['rows']; // get how many rows we want to have into the grid
        $sidx = $_REQUEST['sidx']; // get index row - i.e. user click to sort
        $sord = $_REQUEST['sord']; // get the direction
        if(!$sidx) $sidx =1;

        // connect to the database
        $alias_count = $db->Execute('SELECT COUNT(*) AS count FROM '.TABLE_LINKS_ALIASES);

        if($alias_count->fields['count'] >0 ) {
            $total_pages = ceil($alias_count->fields['count']/$limit);
        } else {
            $total_pages = 0;
        }

        if ($page > $total_pages) $page=$total_pages;
        $start = $limit*$page - $limit; // do not put $limit*($page - 1)
        if ($start<0) $start = 0;

        $search_query = 'SELECT * FROM '.TABLE_LINKS_ALIASES;
        //page=1&rows=25&sidx=id&sord=desc&nd=1232788016273&_search=true&searchField=id&searchOper=eq&searchString=aa
        if(isset($_GET['_search'])){
            $searchField = $_GET['searchField'];
            $searchString = $_GET['searchString'];
            $op = '';
            switch($_GET['searchOper']){
                case 'eq':
                    $op = '=';
                    break;
                case 'cn':
                    $op = 'LIKE';
                    $searchString = "%$searchString%";
                    break;
            }
            if(!empty($searchField) && (strlen($searchString) > 0) && !empty($op))
            $search_query .= " WHERE $searchField $op '$searchString'";
        }

        $search_query .= " ORDER BY ".$sidx." ".$sord. " LIMIT ".$start." , ".$limit;

        $aliases = $db->Execute($search_query);

        // Construct the json data
        $response->page = $page; // current page
        $response->total = $total_pages; // total pages
        $response->records = $alias_count->fields['count']; // total records

        while(!$aliases->EOF) {
            $status='Disabled';
            $permanent='No';
            //if($aliases->fields['status']) $status='Enabled';
            //if($aliases->fields['permanent_link']) $permanent='Yes';
            $response->rows[]=
            array('id'		=>	$aliases->fields['id'],
	    				'cell'	=>	array($aliases->fields['id'],$aliases->fields['link_url'],$aliases->fields['link_alias'],/*$aliases->fields['languages_id'],*/$aliases->fields['status'], $aliases->fields['permanent_link'])
            );
            $aliases->MoveNext();
        }

        return new Response(
            json_encode($response)
        );

    }

    public function aliasEditAction(Request $request){
        global $db;
        $new_data = array('link_url' 	=> $this->parseName($request->get('link_url')),
						'link_alias' 	=> $this->parseName($request->get('link_alias')),
						'status' 		=> $request->get('status'),
						'permanent_link'=> $request->get('permanent_link')
						);
										

    	switch($_POST['oper']){
    	    case 'add':
    	        zen_db_perform(TABLE_LINKS_ALIASES, $new_data, 'insert');    	        
    	        break;
    
    	    case 'edit':
    	        zen_db_perform(TABLE_LINKS_ALIASES, $new_data, 'update', "id = '{$_POST['id']}'");
    	        break;
    
    	    case 'del';
    	    $db->Execute('DELETE FROM '.TABLE_LINKS_ALIASES." WHERE id IN ({$_POST['id']})");
    	    break;
    	}
		
    	$response = array('affected_row_count' => mysql_affected_rows($db->link));
    	
    	return new Response(
            json_encode($response)
        );
    }

    public function resetAction(Request $request){
        $counter = 0;
        switch($request->get('folder')){
            case 'all':
               $counter = Plugin::get('riCache.Cache')->remove('ssu/'); 
               break;
            default:
               $counter = Plugin::get('riCache.Cache')->remove('ssu/' . $request->get('folder'));
               break;        
        }    

        $this->view->getHelper('php::holder')->add('main', ri('%counter% files removed', array('%counter%' => $counter)))
        ->add('main', $this->view->render('riSsu::index.php'));
        return $this->render('riSsu::admin_layout');
    }
    
    private function removeCache($id_list){
		global $db;
		if(!is_array($id_list)) $id_list = array($id_list);
		foreach($id_list as $id){
			// delete alias cache
			$cache_folder = SSUConfig::registry('paths', 'cache')."aliases/".chunk_split(substr($alias_cache->fields['file'] , 0, 4), 1, '/');
			$cache_folder = rtrim($cache_folder, '/').'/';
			if(@unlink($cache_folder.$alias_cache->fields['file'])){
				// now we have to delete all things related
				$db->Execute("DELETE FROM ".TABLE_SSU_CACHE." WHERE file='{$alias_cache->fields['file']}' AND type='aliases'");
			}
			
			// get the related cache files
			$pc = $db->Execute("SELECT * FROM ".TABLE_LINKS_ALIASES." WHERE id = $id");
			$pc_cache = $db->Execute("SELECT * FROM ".TABLE_SSU_CACHE." WHERE referring_id = {$pc->fields['referring_id']} and type = '{$pc->fields['alias_type']}'");
			while (!$pc_cache->EOF) {
				$cache_folder = SSUConfig::registry('paths', 'cache')."pc/".chunk_split(substr($pc_cache->fields['file'] , 0, 4), 1, '/');
				$cache_folder = rtrim($cache_folder, '/').'/';
				if(@unlink($cache_folder.$pc_cache->fields['file'])){
					// now we have to delete all things related
					$db->Execute("DELETE FROM ".TABLE_SSU_CACHE." WHERE file='{$pc_cache->fields['file']}' AND type!='aliases'");
				}
				$pc_cache->MoveNext();
			}
		}
	}
	
	private function parseName($name){
    	$name = trim($name, ' /');
    	$name = "/$name/";
    	return $name;
    }
}