--TEST--
UTF-8 smoketest
--FILE--
<?php
require_once '../library/HTMLPurifier.auto.php';
$purifier = new HTMLPurifier();
echo $purifier->purify('太極拳, ЊЎЖ, لمنس');
--EXPECT--
太極拳, ЊЎЖ, لمنس