<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";

echo "TMP DIR: ";
var_dump(sys_get_temp_dir());

$file = tempnam(sys_get_temp_dir(), 'test');

echo "FILE: ";
var_dump($file);

echo "DONE";
