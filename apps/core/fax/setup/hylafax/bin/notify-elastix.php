#!/usr/bin/php
<?php
$args = $argv;
$args[0] = '--send';
pcntl_exec('bin/elastix-faxevent', $args, $_ENV);
?>
