<?php

global $CFG;

use SplFileInfo;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

// Get real path for our folder
$rootPath = realpath(__DIR__ . '/../mod/customcert/files');

$idCurso = $_GET['idCurso'];

// Initialize archive object
$zip = new ZipArchive();
$tmpFile = 'myZip.zip';
$zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($rootPath),
		RecursiveIteratorIterator::LEAVES_ONLY
);

$cont = 0;

foreach ($files as $name => $file) {
	// Skip directories (they would be added automatically)
	$idCursoStr = substr($name, strpos($name, "|||") -1,1);

	if($idCursoStr == '.') {
		continue;
	}

	if($idCursoStr != $idCurso) {
		$cont++;
		continue;
	}

	if (!$file->isDir())
	{
		// Get real and relative path for current file
		$filePath = $file->getRealPath();
		$relativePath = substr($filePath, strlen($rootPath) + 1);

		// Add current file to archive
		$zip->addFile($filePath, $relativePath);
	}
}

if(count($files) == $cont) {
	echo 'Este curso no tiene certificados';
	echo '<br><a href="/moodle/my">Regresar al inicio</a>';
	exit;
}

echo 'Archivo creado!';
ob_clean();
ob_end_flush();
header('Content-disposition: attachment; filename=Certificados.zip');
header('Content-type: application/zip');
readfile($tmpFile);

// Zip archive will be created only after closing object
$zip->close();