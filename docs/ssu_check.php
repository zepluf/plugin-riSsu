<?php
	require('includes/application_top.php');
	
	checkConfig();
	checkCache();
	if ($messageStack->size('ssu') > 0) echo $messageStack->output('ssu');
	
	
	function checkCache(){
		global $messageStack;
		$messageStack->add('ssu', 'Checking cache write permission','warning');
		// Lets check the chmod of cache folders first.
		checkChmod(SSUConfig::registry('paths','cache'));
	}
	
	function checkConfig(){
		global $messageStack;
		$messageStack->add('ssu', 'Checking includes/configure.php','warning');
		
		$http_parts = parse_url(HTTP_SERVER);
		$http_catalog = DIR_WS_CATALOG;
		if(!empty($http_parts['path'])){
			$http_catalog = $http_parts['path'].DIR_WS_CATALOG;
			$messageStack->add('ssu', "Your HTTP_SERVER contains path, this will cause SSU to malfunction!!! <br/>
										Suggestion:<br />
										1. Change HTTP_SERVER to:{$http_parts['scheme']}://{$http_parts['host']} <br/>
										2. Change DIR_WS_CATALOG to: $http_catalog
										", 'error');
		}
			
		$https_parts = parse_url(HTTPS_SERVER);
		$https_catalog = DIR_WS_HTTPS_CATALOG;
		if(!empty($https_parts['path'])){
			$https_catalog = $https_parts['path'].DIR_WS_HTTPS_CATALOG;
			$messageStack->add('ssu', "Your HTTPS_SERVER contains path, this will cause SSU to malfunction!!! <br/>
										Suggestion:<br />
										1. Change HTTPS_SERVER to:{$https_parts['scheme']}://{$https_parts['host']} <br/>
										2. Change DIR_WS_HTTPS_CATALOG to: $https_catalog
										", 'error');
		}
		
		$messageStack->add('ssu', 'Suggested .htaccess content','warning');
		if($https_catalog == $http_catalog){
			$messageStack->add(	'ssu', "<textarea cols=50 rows=20 style='overflow:hidden;'>#### BOF SSU \nOptions +FollowSymLinks -MultiViews \n\nRewriteEngine On \n\nRewriteBase $https_catalog \n\n# Deny access from .htaccess \nRewriteRule ^\.htaccess$ - [F] \n\nRewriteCond %{SCRIPT_FILENAME} !-f \nRewriteCond %{SCRIPT_FILENAME} !-d \nRewriteRule ^(.*) index.php?/$1 [E=VAR1:$1,QSA,L] \n\n#### EOF SSU</textarea>",'success');
		}
		else{
			$sub_folder = trim(str_replace($http_catalog, '', $https_catalog), '/');
			$messageStack->add(	'ssu', "<textarea style='overflow:hidden;'>#### BOF SSU \nOptions +FollowSymLinks -MultiViews \nRewriteEngine On \nRewriteBase $https_catalog \n\n# Deny access from .htaccess \nRewriteRule ^\.htaccess$ - [F] \n \nRewriteCond %{SCRIPT_FILENAME} !-f \nRewriteCond %{SCRIPT_FILENAME} !-d \nRewriteRule ^(.*) index.php?/$1 [E=VAR1:$1,QSA] \n \n# STRIP THE REWRITEBASE RULE FROM NON-SSL CONNECTIONS. \nRewriteCond %{SERVER_PORT} 80 \nRewriteCond %{REQUEST_URI} ^/$sub_folder/ \nRewriteRule ^(.*) {$http_catalog}index.php?/$1 [E=VAR1:$1,QSA,L] \n#### EOF SSU</textarea>",'success');
		}
	}
	
	function checkChmod($dir) {
		global $messageStack;
	    if(!$dh = @opendir($dir)){
	    	$messageStack->add("Could not open dir $dir", 'warning');
	    	return false;
	    }
	    
	    if(!is__writable($dir)){
	    	$messageStack->add("$dir is not writable", 'warning');
	    	return false;
	    }
	    
	    while (false !== ($obj = readdir($dh))) {
	        if($obj=='.' || $obj=='..') continue;
	        if (is_dir("$dir$obj/") && !is__writable("$dir$obj/")) $messageStack->add('ssu', "$dir$obj is not writable", 'warning');
	    }
	
	    closedir($dh);	    
	}
	
	function is__writable($path) {
		//will work in despite of Windows ACLs bug
		//NOTE: use a trailing slash for folders!!!
		//see http://bugs.php.net/bug.php?id=27609
		//see http://bugs.php.net/bug.php?id=30931
		
		    if ($path{strlen($path)-1}=='/') // recursively return a temporary file path
		        return is__writable($path.uniqid(mt_rand()).'.tmp');
		    else if (is_dir($path))
		        return is__writable($path.'/'.uniqid(mt_rand()).'.tmp');
		    // check tmp file for read/write capabilities
		    $rm = file_exists($path);
		    $f = @fopen($path, 'a');
		    if ($f===false)
		        return false;
		    fclose($f);
		    if (!$rm)
		        unlink($path);
		    return true;
	}