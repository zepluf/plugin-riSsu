The Zencart Simple Seo Url (SSU) is a product of RubikIntegration.com

This branch is meant to be used with our Zencart Plugin Framework (ZePLUF)

**Requirements**
- PHP 5.3 or newer
- ZePLUF: https://github.com/yellow1912/ZenCartPluginFramework

**Installation**
- Open your current includes/functions/html_output.php
- find the function zen_href_link
Look for:
  global $request_type, $session_started, $http_domain, $https_domain;

Insert below:
  if(($link = plugins\riPlugin\Plugin::get('riSsu.Link')->link($page, $parameters, $connection, $add_session_id, $search_engine_safe, $static, $use_dir_ws_catalog)) !== false)
    return $link;