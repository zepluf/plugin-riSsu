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
	
	Reset only <?= $parser ?> cache: <a href="<?php echo zen_href_link(FILENAME_SSU,"action=reset_cache&folder=$parser"); ?>">Click here</a><br />
	
</fieldset>
<fieldset>
	<legend>Alias Functions</legend>
	Manage Aliases: <a href="<?php echo zen_href_link(FILENAME_SSU,'action=link_aliases'); ?>">Click here</a><br />
</fieldset>
