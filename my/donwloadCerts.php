<?php

global $CFG;

use SplFileInfo;
use ZipArchive;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

// Get real path for our folder
$rootPath = realpath(__DIR__ . '/../../mod/customcert/files');

// Initialize archive object
$zip = new ZipArchive();
$zip->open('file.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

var_dump($rootPath);
exit;

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($rootPath),
	RecursiveIteratorIterator::LEAVES_ONLY
);

var_dump($files);
exit;

foreach ($files as $name => $file)
{
	// Skip directories (they would be added automatically)
	if (!$file->isDir())
	{
		// Get real and relative path for current file
		$filePath = $file->getRealPath();
		$relativePath = substr($filePath, strlen($rootPath) + 1);

		// Add current file to archive
		$zip->addFile($filePath, $relativePath);
	}
}

// Zip archive will be created only after closing object
$zip->close();