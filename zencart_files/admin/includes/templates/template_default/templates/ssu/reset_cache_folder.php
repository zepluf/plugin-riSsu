<?php 
/**
* @package Pages
* @copyright Copyright 2003-2006 Zen Cart Development Team
* @copyright Portions Copyright 2003 osCommerce
* @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
* @version $Id: tpl_reset_cache_folder.php 245 2009-09-03 08:38:19Z yellow1912 $
*/
if(!isset($_GET['total'])) $_GET['total'] = 0;
$_GET['total'] += $file_counter;
echo $_GET['total'] ?> file(s) removed.

<?php if($file_counter >= SSU_MAX_CACHE_DELETE) { ?>
<br />(continue clearing cache in a few seconds)...
<script type="text/javascript">
<!--

refresh();
function doLoad()
{
    // the timeout value should be the same as in the "refresh" meta-tag
    setTimeout( "refresh()", 2*1000 );
}

function refresh()
{
    //  This version of the refresh function will cause a new
    //  entry in the visitor's history.  It is provided for
    //  those browsers that only support JavaScript 1.0.
    //
    window.location.href = "<?php echo "ssu.php?".(http_build_query($_GET));?>";
}
//-->
</script>

<?php } ?>