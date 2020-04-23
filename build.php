<?php

/**
 * Script to release a new version
 */
$binDir = dirname(__FILE__) . '/bin/';
$versionFile = $binDir . 'current.version';

shell_exec('php ' . $binDir . 'box.phar build');
$shaFile = sha1_file($binDir . 'ps.phar');
unlink($versionFile);
file_put_contents($versionFile, $shaFile);
