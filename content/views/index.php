<?php $riview->get('loader')->load(array(
	'jquery.lib', 
	'jquery.ui.lib', 
	'riSsu::jqGrid/css/ui.jqgrid.css',
    'riSsu::jqGrid/js/i18n/grid.locale-en.js',
	'riSsu::jqGrid/jquery.jqGrid.js',
))?>

<?php $riview->get('loader')->startInline('js');?>
<script type="text/javascript">
var $list;
jQuery(document).ready(function(){ 
 
jQuery("#list").jqGrid({ 
	url:'<?php echo $router->generate('ssu_alias_list');?>', 
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
    {name:'status', index:'status', editable:true, edittype:"checkbox", editoptions: {value:'1:0', defaultValue: '1'}, width:80, align:'left'},
    {name:'permanent_link', index:'permanent_link', editable:true, edittype:"checkbox", editoptions: {value:'1:Yes;0:No'}, width:80, align:'left'}
    ],
	rowNum:25,
  	rowList:[25,50,100],
    //imgpath: '../plugins/riSsu/content/resources/jqGrid/css/themes/steel/images', 
	pager: jQuery('#pager'), 
	sortname: 'id', 
	viewrecords: true, 
	multiselect: true,
	sortorder: "desc", 
	caption: 'Your link aliases', 
	editurl: '<?php echo $router->generate('ssu_alias_edit');?>', 
	height:450 }).navGrid('#pager', {}, //options 
	{height:280,width:420,reloadAfterSubmit:true}, // edit options 
	{height:280,width:420,reloadAfterSubmit:true}, // add options 
	{reloadAfterSubmit:true}, // del options 
	{sopt: ['eq','cn'] } // search options 
	); 
}); 
</script>
<?php $riview->get('loader')->endInline();?>

<fieldset>
	<legend>Instruction</legend>
	SSU caches your categories/products names and links in order to reduce the number of sql queries and minimize the performance penalty on your server. That has its drawback, however. If you change your categories/products names, you will need to reset the cache to force SSU reload and update the names.
</fieldset>
<fieldset>
	<legend>Cache Functions</legend>
	Reset all cache: <a href="<?php echo $router->generate('ssu_reset', array('folder' => 'all')); ?>">Click here</a><br />
	Reset alias cache: <a href="<?php echo $router->generate('ssu_reset', array('folder' => 'aliases')); ?>">Click here</a><br />		
	Reset product/category cache: <a href="<?php echo $router->generate('ssu_reset', array('folder' => 'pc')); ?>">Click here</a><br />
</fieldset>

<br /><br />

<table id="list" class="scroll"></table> 
<div id="pager" class="scroll" style="text-align:center;"></div> 