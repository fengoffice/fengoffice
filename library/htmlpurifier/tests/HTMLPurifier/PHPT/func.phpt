--TEST--
HTMLPurifier.func.php test
--FILE--
<?php
require_once '../library/HTMLPurifier.auto.php';
require 'HTMLPurifier.func.php';
echo HTMLPurifier('<b>Salsa!');
--EXPECT--
<b>Salsa!</b>