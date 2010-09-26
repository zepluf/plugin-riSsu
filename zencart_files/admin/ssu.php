<?php
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: ssu.php 316 2010-02-21 14:49:37Z yellow1912 $
*/
set_time_limit(0);
require('includes/application_top.php');
require_once(DIR_WS_CLASSES.'module_installer.php');
require_once(DIR_WS_CLASSES.'ssu.php');
require_once(DIR_FS_CATALOG . DIR_WS_CLASSES.'ri_template.php');

$module_installer = new module_installer();
$module_installer->set_module('yellow1912_ssu');
$module_installer->upgrade_module();

$ri_template = new RITemplate(true);

switch($_GET['action']){
	case 'reset_cache':
		$ri_template->set('file_counter', SSUManager::resetCache($_GET['folder']));
		$ri_template->setView('reset_cache_folder.php');
	break;
	case 'reset_cache_timer':
		SSUManager::resetCacheTimer();
		$ri_template->setView('reset_cache_timer.php');
	break;
	case 'check_and_fix_cache':
		$ri_template->set('file_counter', SSUManager::checkAndFixCache());
		$ri_template->setView('reset_cache_folder.php');
	break;
	case 'link_aliases':
		SSUAlias::retrieveAliases();
		$ri_template->set('link_aliases',  SSUManager::retrieveAliases());
		/*$languages = $db->Execute("SELECT * FROM ".TABLE_LANGUAGES);
		$languages_string = "";
		while(!$languages->EOF){ 
			$languages_string .= "{$languages->fields['languages_id']}:{$languages->fields['name']};";
			$languages->MoveNext();
		}
		$languages_string = trim($languages_string, ';');*/
	break;	
}
		
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<?php if($_GET['action'] == 'link_aliases') { ?>
<link rel="stylesheet" type="text/css" media="screen" href="includes/templates/template_default/css/themes/steel/grid.css" />
<link rel="stylesheet" type="text/css" media="screen" href="includes/templates/template_default/css/themes/jqModal.css" />
<style type="text/css">
input.FormElement[type="text"] {
width:350px;
}
</style>
<script src="includes/templates/template_default/jscript/jquery.js" type="text/javascript"></script>
<script src="includes/templates/template_default/jscript/jquery.jqGrid.js" type="text/javascript"></script>
<script src="includes/templates/template_default/jscript/js/jqModal.js" type="text/javascript"></script>
<script src="includes/templates/template_default/jscript/js/jqDnR.js" type="text/javascript"></script>

<script type="text/javascript">
var $list;
jQuery(document).ready(function(){ 
 
jQuery("#list").jqGrid({ 
	url:'ssu_link_alias.php', 
	datatype: "json", 
	jsonReader : {

  root: "rows",
  page: "page",
  total: "total",
  records: "records",
  repeatitems: true,
  cell: "cell",
  id: "id",
  userdata: "userdata",
  subgrid: {root:"rows", 
    repeatitems: true, 
    cell:"cell"
  }},
  colNames:['id','Url', 'Alias', /*'Language',*/ 'Status', 'Permanent'],
  colModel :[ 
    {name:'id', index:'id', width:50}, 
    {name:'link_url', index:'link_url', editable:true, width:420}, 
    {name:'link_alias', index:'link_alias', editable:true, width:420, align:'left'}, 
    //{name:'languages_id', index:'languages_id', edittype:'select', editoptions:{value:"<?php echo $languages_string;?>"} },
    {name:'status', index:'status', editable:true, edittype:"checkbox", editoptions: {value:'1:0'}, width:80, align:'left'},
    {name:'permanent_link', index:'permanent_link', editable:true, edittype:"checkbox", editoptions: {value:'1:0'}, width:80, align:'left'}
    ],
	rowNum:25,
  rowList:[25,50,100],
	imgpath: 'includes/templates/template_default/css/themes/steel/images', 
	pager: jQuery('#pager'), 
	sortname: 'id', 
	viewrecords: true, 
	multiselect: true,
	sortorder: "desc", 
	caption: 'Your link aliases', 
	editurl: 'ssu_link_alias.php?action=edit', 
	height:450 }).navGrid('#pager', {}, //options 
											{height:280,width:420,reloadAfterSubmit:true}, // edit options 
											{height:280,width:420,reloadAfterSubmit:true}, // add options 
											{reloadAfterSubmit:true}, // del options 
											{sopt: ['eq','cn'] } // search options 
											); 
}); 
</script>
<?php } ?>
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
if (typeof _editor_url == "string") HTMLArea.replaceAll();
 }
 // -->
</script>
</head>
<body onLoad="init()">
<!-- header //-->
<div class="header_area">
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
</div>
<!-- header_eof //-->
<fieldset>
	<legend>Instruction</legend>
	SSU caches your categories/products names and links in order to reduce the number of sql queries and minimize the performance penalty on your server. That has its drawback, however. If you change your categories/products names, you will need to reset the cache to force SSU reload and update the names.
</fieldset>
<fieldset>
	<legend>Cache Functions</legend>
	Check and fix cache(Run this once if you upgrade from any version older than 3.6.5): <a href="<?php echo zen_href_link(FILENAME_SSU,'action=check_and_fix_cache'); ?>">Click here</a><br />
	<!--Reset cache timer(Run this when you add new product ONLY IF you use Auto Alias): <a href="<?php echo zen_href_link(FILENAME_SSU,'action=reset_cache_timer'); ?>">Click here</a><br />-->
	Reset all cache: <a href="<?php echo zen_href_link(FILENAME_SSU,'action=reset_cache&folder=all'); ?>">Click here</a><br />
	Reset alias cache: <a href="<?php echo zen_href_link(FILENAME_SSU,'action=reset_cache&folder=aliases'); ?>">Click here</a><br />
	<?php foreach(SSUConfig::registry('plugins', 'parsers') as $parser) { ?>
	Reset only <?= $parser ?> cache: <a href="<?php echo zen_href_link(FILENAME_SSU,"action=reset_cache&folder=$parser"); ?>">Click here</a><br />
	<?php } ?>
</fieldset>
<fieldset>
	<legend>Alias Functions</legend>
	Manage Aliases: <a href="<?php echo zen_href_link(FILENAME_SSU,'action=link_aliases'); ?>">Click here</a><br />
</fieldset>
<?php $ri_template->render(); ?>

<!-- footer //-->
<div class="footer-area">
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
</div>
<!-- footer_eof //-->
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>