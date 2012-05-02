<?php 
$base_href = getBaseHref(true);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>RI Admin</title>
<link rel="stylesheet" type="text/css" href="<?php echo $base_href;?>includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/themes/base/jquery-ui.css">
<link rel="stylesheet" type="text/css" href="<?php echo $base_href;?>includes/cssjsmenuhover.css" media="all" id="hoverJS">
<link rel="stylesheet" type="text/css" href="<?php echo $base_href;?>../plugins/riResultList/content/css/pagination.css" media="all">
<script language="javascript" src="<?php echo $base_href;?>includes/menu.js"></script>
<script language="javascript" src="<?php echo $base_href;?>includes/general.js"></script>
<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js'></script>
<script type='text/javascript' src='//ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js'></script>
<script type='text/javascript' src='<?php echo $base_href;?>../plugins/riCard/content/resources/js/jsrender.js'></script>
<script type='text/javascript' src='<?php echo $base_href;?>../plugins/riCard/content/resources/js/jquery.observable.js'></script>
<script type='text/javascript' src='<?php echo $base_href;?>../plugins/riCard/content/resources/js/jquery.views.js'></script>
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
  }
  // -->
</script>
  <script type="text/javascript">
	jQuery(document).ready(function() {	
		jQuery(".datepicker").datepicker({ dateFormat: 'yy/mm/dd' });	

		
		jQuery('.checkall').click(function () {
			jQuery(jQuery(this).data('target')).attr('checked', this.checked);
		});
					
    });	
  </script>
 
<style type="text/css">
.column{background:#ccc;margin-top:20px}
.column.odd{background:none}
.clearfix{clear:both}
input.small{width:40px}
input.medium{width:70px}
tr.current td{background:yellow}
</style>
<base href="<?php echo $base_href;?>">
</head>

<body onload="init()">
<!-- header //-->

<!-- header_eof //-->
<!-- body //-->

<?php echo $view['holder']->get('main')?>



</body>
</html>