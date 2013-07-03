<?php

ini_set('include_path', '@PEACH_DIR@:@PEAR_DIR@');

define("DOCUMENT_ROOT", dirname(__FILE__));
define("PEACH_CORE_TEMPLATE_DIR", "@INSTALL_DIR@/templates/");
define("PEACH_CORE_TEMPLATE_COMPILE_DIR", "@INSTALL_DIR@/compiled/");
define("PEACH_CORE_CONFIG_DIR", "@INSTALL_DIR@/config");
define("PEACH_CORE_SESSION_DIR", "@INSTALL_DIR@/sessions/");
define("PEACH_DATA_DIR", "@INSTALL_DIR@/Data/");
define("PEACH_APP_DIR", "@PEACH_DIR@/PEACH/App");
define("PEACH_APP_TEMPLATE_COMPILE_DIR", "@INSTALL_DIR@/compiled");
define('PEACH_SITEMAP_DIR', "@INSTALL_DIR@/sitemaps");
define('PEACH_MODULE_KEY', "Appname");
define('PEACH_METHOD_KEY', "Method");
define('PEACH_WEBROOT',  "@INSTALL_DIR@");
define('PEACH_METHOD_PREFIX', "public_");
define('PEACH_ARGS_KEY', "Args");
define('PEACH_REWRITE', true);
define('PEACH_DIR', '@PEACH_INSTALL_DIR');
ini_set('session.save_path', PEACH_CORE_SESSION_DIR);

require_once 'PEACH/Site.php';

$site = PEACH_Site::instance();
$site->load();
echo $site->execute();

?>
