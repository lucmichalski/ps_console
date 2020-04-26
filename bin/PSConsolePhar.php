#!/usr/bin/env php
<?php
/**
 * 2007-2020 Adilis
 *
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2007-2020 Adilis
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * https://www.adilis.fr
 */

use Adilis\PSConsole\PSConsoleApplication;

require_once 'src/vendor/autoload.php';
require_once 'settings.php';

$app = new PSConsoleApplication(PSC_CONSOLE_NAME, PSC_CONSOLE_VERSION);
$app->setRunAs('phar');

if (is_file('config/config.inc.php')) {
    include_once 'config/config.inc.php';
    $app->getDeclaredCommands();
} else {
    $configuration['commands'] = [
        'Adilis\PSConsole\Command\Install\InstallCommand',
        'Adilis\PSConsole\Command\Install\InfoCommand'
    ];
    $app->setDefaultCommand('install:info');
}

//Application run
$app->run();
