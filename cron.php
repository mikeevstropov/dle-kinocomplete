<?php

@ini_set ('display_errors', true);
@ini_set ('html_errors', false);

@error_reporting(E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);
@ini_set('error_reporting', E_ALL ^ E_WARNING ^ E_DEPRECATED ^ E_NOTICE);

define('DATALIFEENGINE', true);
define('AUTOMODE', true);
define('LOGGED_IN', true);
define('ROOT_DIR', __DIR__);

class db {}

require_once ROOT_DIR .'/engine/data/dbconfig.php';
require_once ROOT_DIR .'/engine/data/config.php';

date_default_timezone_set($config['date_adjust']);

$_REQUEST['cron'] = true;

require_once ROOT_DIR .'/engine/inc/kinocomplete.php';