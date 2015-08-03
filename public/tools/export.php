<?php
$allowed = include 'access.php';
if (!in_array('export.php', $allowed)) die("This tool is disabled.");

chdir("../..");
define("CONSOLE_MODE", true);
define('PUBLIC_FOLDER', 'public');
include "init.php";

header("Content-type: text/plain; charset=utf-8");

session_commit(); // we don't need sessions
@set_time_limit(0); // don't limit execution of cron, if possible

echo "Exporting Feng Office files to 'tmp/export'...\n\n";

$dir = "tmp/export";
if (file_exists($dir)) {
    foreach (new DirectoryIterator($dir) as $file) {
        if (true === $file->isFile()) {
            unlink($file->getPathName());
        }
    }
} else {
	mkdir($dir, 0777, true);
}

if (FileRepository::getBackend() instanceof FileRepository_Backend_FileSystem) {
	$files = ProjectFiles::findAll();
	foreach ($files as $file) {
		$filename = $file->getFilename();
		$id = $file->getLastRevision()->getRepositoryId();
		$path = FileRepository::getBackend()->getFilePath($id);
		$newpath = "$dir/$filename";
		if (file_exists($newpath)) {
			$tmppath = $newpath;
			$ext = strrpos($newpath, ".");
			if ($ext === false) {
				$name = $newpath;
				$ext = "";
			} else {
				$name = substr($newpath, 0, $ext);
				$ext = substr($newpath, $ext + 1);
			}
			for ($i=2; file_exists($tmppath); $i++) {
				$tmppath = "$name-$i.$ext";
			}
			$newpath = $tmppath;
		}
		copy($path, $newpath);
		echo "Exported $filename\n";
	}
} else {
	$files = ProjectFiles::findAll();
	foreach ($files as $file) {
		$filename = $file->getFilename();
		$newpath = "tmp/export/" . $filename;
		if (file_exists($newpath)) {
			$tmppath = $newpath;
			$ext = strrpos($newpath, ".");
			if ($ext === false) {
				$name = $newpath;
				$ext = "";
			} else {
				$name = substr($newpath, 0, $ext);
				$ext = substr($newpath, $ext + 1);
			}
			for ($i=2; file_exists($tmppath); $i++) {
				$tmppath = "$name-$i.$ext";
			}
			$newpath = $tmppath;
		}
		try {
			$content = $file->getFileContent();
			$f = fopen($newpath, "wb");
			fwrite($f, $content);
			fclose($f);
			echo "Exported $filename\n";
		} catch (Error $e) {
			echo "Error exporting $filename:\n\t";
			echo $e->getMessage() . "\n";
		}
	}
}

echo "\nReady. Check 'tmp/export' for Feng Office files.\n";
?>