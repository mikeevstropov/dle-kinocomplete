<?php

define('DIST_DIR',   realpath(__DIR__ .'/../../../../../dist'));
define('MODULE_DIR', realpath(__DIR__ .'/../'));

$moduleJson = file_get_contents(MODULE_DIR .'/module.json');
$moduleJson = json_decode($moduleJson, true);

$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><dleplugin></dleplugin>');

$xml->addChild('name',           $moduleJson['module_label']);
$xml->addChild('description',    $moduleJson['module_description']);
$xml->addChild('icon',           'engine/skins/images/'. $moduleJson['module_icon']);
$xml->addChild('version',        $moduleJson['module_version']);
$xml->addChild('dleversion',     $moduleJson['system_version_min']);
$xml->addChild('versioncompare', 'greater');
$xml->addChild('mysqlinstall',   "<![CDATA[]]>");
$xml->addChild('mysqlupgrade',   "<![CDATA[]]>");
$xml->addChild('mysqlenable',    "<![CDATA[]]>");
$xml->addChild('mysqldisable',   "<![CDATA[]]>");
$xml->addChild('mysqldelete',    "<![CDATA[]]>");

$addNewsFile = $xml->addChild('file');
$addNewsFile->addAttribute('name', 'engine/inc/addnews.php');
$addNewsFileOperation = $addNewsFile->addChild('operation');
$addNewsFileOperation->addAttribute('action', 'after');
$addNewsFileSearchString = '$js_array[] = "engine/classes/uploads/html5/fileuploader.js";';
$addNewsFileReplaceString = 'require ENGINE_DIR ."/modules/kinocomplete/src/addNewsInjection.php";';
$addNewsFileSearch = $addNewsFileOperation->addChild('searchcode', "<![CDATA[$addNewsFileSearchString]]>");
$addNewsFileReplace = $addNewsFileOperation->addChild('replacecode', "<![CDATA[$addNewsFileReplaceString]]>");

$editNewsFile = $xml->addChild('file');
$editNewsFile->addAttribute('name', 'engine/inc/editnews.php');
$editNewsFileOperation = $editNewsFile->addChild('operation');
$editNewsFileOperation->addAttribute('action', 'after');
$editNewsFileSearchString = '$js_array[] = "engine/classes/uploads/html5/fileuploader.js";';
$editNewsFileReplaceString = 'require ENGINE_DIR ."/modules/kinocomplete/src/editNewsInjection.php";';
$editNewsFileSearch = $editNewsFileOperation->addChild('searchcode', "<![CDATA[$editNewsFileSearchString]]>");
$editNewsFileReplace = $editNewsFileOperation->addChild('replacecode', "<![CDATA[$editNewsFileReplaceString]]>");

$showFullFile = $xml->addChild('file');
$showFullFile->addAttribute('name', 'engine/modules/show.full.php');
$showFullFileOperation = $showFullFile->addChild('operation');
$showFullFileOperation->addAttribute('action', 'after');
$showFullFileSearchString = 'else $tpl->load_template( \'fullstory.tpl\' );';
$showFullFileReplaceString = 'require ENGINE_DIR ."/modules/kinocomplete/src/showFullInjection.php";';
$showFullFileSearch = $showFullFileOperation->addChild('searchcode', "<![CDATA[$showFullFileSearchString]]>");
$showFullFileReplace = $showFullFileOperation->addChild('replacecode', "<![CDATA[$showFullFileReplaceString]]>");

$dom = dom_import_simplexml($xml)->ownerDocument;
$dom->formatOutput = true;

$xmlString = $dom->saveXML();
$xmlString = str_replace('&lt;![CDATA[', '<![CDATA[', $xmlString);
$xmlString = str_replace(']]&gt;', ']]>', $xmlString);
$xmlString = str_replace('-&gt;', '->', $xmlString);

$fileName = $moduleJson['module_name'] .'.xml';
$filePath = DIST_DIR .'/'. $fileName;

if (!touch($filePath))
  throw new Exception('Unable to get an access to plugin file.');

$bytes = file_put_contents($filePath, $xmlString);

if ($bytes === false)
  throw new Exception('Unable to write a data into plugin file.');

fwrite(STDOUT, "Plugin file updated by $bytes bytes.\n");
