<?php

require __DIR__ . '/../vendor/autoload.php';

use Kinocomplete\Utils\Utils;
use Alchemy\Zippy\Zippy;

define('ROOT_DIR',   realpath(__DIR__ .'/../../../../../'));
define('DIST_DIR',   realpath(__DIR__ .'/../../../../../dist'));
define('MODULE_DIR', realpath(__DIR__ .'/../'));

$moduleJson = file_get_contents(MODULE_DIR .'/module.json');
$moduleJson = json_decode($moduleJson, true);

$zippy = Zippy::load();

$archiveFilePath = DIST_DIR .'/'. $moduleJson['module_name'] .'.zip';
$pluginFilePath  = DIST_DIR .'/'. $moduleJson['module_name'] .'.xml';
$sourceDirPath   = DIST_DIR .'/engine';

if (file_exists($archiveFilePath))
  unlink($archiveFilePath);

if (!file_exists($pluginFilePath))
  throw new Exception('Plugin file not found.');

if (file_exists($sourceDirPath))
  Utils::removeDir($sourceDirPath);

Utils::copyDir(
  ROOT_DIR .'/upload/engine',
  $sourceDirPath
);

// Remove development files.
Utils::removeDir($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/tests');
Utils::removeDir($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/build');
Utils::removeDir($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/fixtures');
Utils::removeDir($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/web/node_modules');
unlink($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/composer.json');
unlink($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/composer.lock');
unlink($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/phpunit.xml');
unlink($sourceDirPath .'/modules/'. $moduleJson['module_name'] .'/phpunit.xml.dist');

$archiveFiles = [
  $pluginFilePath,
  $sourceDirPath
];

$zippy->create(
  $archiveFilePath,
  $archiveFiles,
  true
);

$bytes = filesize($archiveFilePath);

if ($bytes === false)
  throw new Exception('Unable to read a data from archive file.');

Utils::removeDir($sourceDirPath);

fwrite(STDOUT, "Archive file updated by $bytes bytes.\n");
