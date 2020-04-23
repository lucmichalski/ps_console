<?php

/**
 * 2007-2019 Adilis
 *
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2007-2019 Adilis
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * https://www.adilis.fr
 */

namespace Adilis\PSConsole;

use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Finder\Finder;

class PSConsoleApplication extends BaseApplication
{
    const APP_NAME = 'PSConsole';

    /** @var string php|phar Console run mod */
    protected $_runAs = 'php';

    /** @var string Commands directory */
    protected $_commandsDir = 'src/Adilis/PSConsole/Command';

    /**
     * Set RunAs Mode
     * @param string $mode
     */
    public function setRunAs($mode)
    {
        $this->_runAs = $mode;
    }

    /**
     * Get RunAs
     * @return string
     */
    public function getRunAs()
    {
        return $this->_runAs;
    }

    /**
     * Automatically Detect Registered commands
     */
    public function getDeclaredCommands()
    {
        if ($this->getRunAs() == 'phar') {
            $dir = $this->_getPharPath();
        } else {
            $dir = getcwd() . DIRECTORY_SEPARATOR . $this->_commandsDir;
        }

        $finder = new Finder();
        $commands = $finder->files()->name('*Command.php')->in($dir);
        $customCommands = array();
        if (sizeof($commands)) {
            foreach ($commands as $command) {
                $classPath = 'Adilis\\PSConsole\\Command\\' . str_replace(
                    '/',
                    "\\",
                    $command->getRelativePathname()
                );
                $commandName = basename($classPath, '.php');
                $customCommands[] = new $commandName();
            }

            $this->addCommands($customCommands);
        }
    }

    /**
     * Get Phar path
     * @return string
     */
    protected function _getPharPath()
    {
        $paths = explode(DIRECTORY_SEPARATOR, __DIR__);
        $paths = array_reverse($paths);
        $pharName = $paths[3]; //3 First items : PrestashopConsole/Hhennes/src/

        return 'phar://' . getcwd() . DIRECTORY_SEPARATOR . $pharName . DIRECTORY_SEPARATOR . $this->_commandsDir;
    }
}
