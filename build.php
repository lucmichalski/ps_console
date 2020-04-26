<?php

/**
 * Script to release a new version
 * 1. build phar version
 * 2. generate COMMANDS.md
 */

$binDir = dirname(__FILE__) . '/bin/';
$versionFile = $binDir . 'current.version';

shell_exec('php ' . dirname(__FILE__) . '/bin/' . 'box.phar build');
$shaFile = sha1_file(dirname(__FILE__) . '/bin/' . 'psc.phar');
file_put_contents($versionFile, $shaFile);

exec('php ' . dirname(__FILE__) . '/psc.php --format=md', $output);
file_put_contents(dirname(__FILE__) . '/COMMANDS.md', implode("\n", $output));
