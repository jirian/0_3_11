<?php
define('QUADODO_IN_SYSTEM', true);
require_once '../includes/header.php';
$qls->Security->check_auth_page('user.php');

$filename = 'export.vsdx';
$filenameFullPath = $_SERVER['DOCUMENT_ROOT'].'/userDownloads/'.$filename;
		
// Open ZIP File
$zip = new ZipArchive();
$zipFilename = $filenameFullPath;
if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE)!==TRUE) {
    die('Cannot open zip file.');
}

// Add database data
$fileStructure = array(
	array('', '[Content_Types].xml'),
	array('visio/', 'document.xml'),
	array('visio/', 'windows.xml'),
	array('visio/_rels/', 'document.xml.rels'),
	array('visio/pages/', 'page1.xml'),
	array('visio/pages/', 'pages.xml'),
	array('visio/pages/_rels/', 'pages.xml.rels'),
	array('visio/docProps/', 'app.xml'),
	array('visio/docProps/', 'core.xml'),
	array('visio/docProps/', 'custom.xml'),
	array('visio/docProps/', 'thumbnail.emf'),
	array('visio/_rels/', '.rels')
);
foreach($fileStructure as $fileElement) {
	$zip->addFile($_SERVER['DOCUMENT_ROOT'].'/includes/visio/'.$fileElement[0].$fileElement[1], $fileElement[0].$fileElement[1]);
}

$zip->close();

header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename='.$filename);
header('Content-Length: '.filesize($filenameFullPath));

readfile($filenameFullPath);

unlink($filenameFullPath);

exit;

?>
