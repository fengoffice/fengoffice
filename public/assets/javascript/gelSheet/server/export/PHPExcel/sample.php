<?php

$zip = new ZipArchive;
$res = $zip->open('test.zip');
if ($res === TRUE) {
    echo 'ok';
    $zip->extractTo('test');
    $zip->close();
} else {
    echo 'failed, code:' . $res;
}




?>