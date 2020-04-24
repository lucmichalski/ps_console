<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Db\Pma;

use Adilis\PSConsole\Console\Question\YesNoQuestion;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Validate;

/**
 * Class pma
 * Command sample description
 */
class DbPmaInstallCommand extends Command {
    const PMA_LAST_SOURCE = 'https://files.phpmyadmin.net/phpMyAdmin/4.9.5/phpMyAdmin-4.9.5-all-languages.zip';

    protected $_progressBar = null;
    protected $_ouput = null;

    protected function configure() {
        $this
            ->setName('db:pma:install')
            ->setDescription('Install PhpMyAdmin')
            ->setAliases(['pma:install']);
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $this->_output = $output;
        $filesystem = new Filesystem();

        if ($filesystem->exists(_PS_ROOT_DIR_ . '/pma')) {
            $helper = $this->getHelper('question');
            if (!$helper->ask($input, $output, new YesNoQuestion('<question>PhpMyAdmin directory already exits, do you want to delete it ?</question>'))) {
                return;
            }
            $filesystem->remove(_PS_ROOT_DIR_ . '/pma');
        }

        if (!$filesystem->exists(_PS_ROOT_DIR_ . '/pma.zip')) {
            $output->writeln('<info>Start download PhpMyAdmin</info>');

            $context = stream_context_create([], ['notification' => [$this, 'progress']]);
            $resource = file_get_contents(self::PMA_LAST_SOURCE, false, $context);
            $this->_progressBar->finish();
            $output->writeln('');
            file_put_contents(_PS_ROOT_DIR_ . '/pma.zip', $resource);
        }

        $zip = new \ZipArchive;
        if ($zip->open(_PS_ROOT_DIR_ . '/pma.zip') === true) {
            $findersytem = new Finder();

            $zip->extractTo(_PS_ROOT_DIR_);
            $zip->close();

            $findersytem->directories()->in(_PS_ROOT_DIR_)->name('phpMyAdmin-*-all-languages');
            if (!$findersytem->count() || $findersytem->count() > 1) {
                throw new IOException('None or too many folder');
            }

            foreach ($findersytem as $file) {
                break;
            }
            $directory = $file->getPathName();

            $filesystem->rename($directory, _PS_ROOT_DIR_ . '/pma');
            $filesystem->remove(_PS_ROOT_DIR_ . '/pma.zip');

            try {
                $filesystem->appendToFile(_PS_ROOT_DIR_ . '/pma/config.inc.php', $this->_getConfigContent());
                $filesystem->appendToFile(_PS_ROOT_DIR_ . '/pma/.htaccess', $this->_getHtaccessContent());
                $filesystem->appendToFile(_PS_ROOT_DIR_ . '/pma/.htpasswd', $this->_getHtpasswdContent());
            } catch (IOException $e) {
                throw new \RuntimeException('Unable to create class file');
            }
        }
        $output->writeln('<info>PhpMyAdmin have been successfully installed</info>');
    }

    public function progress($notificationCode, $severity, $message, $messageCode, $bytesTransferred, $bytesMax) {
        if (STREAM_NOTIFY_REDIRECTED === $notificationCode) {
            $this->_progressBar->clear();
            $this->_progressBar = null;
            return;
        }

        if (STREAM_NOTIFY_FILE_SIZE_IS === $notificationCode) {
            if ($this->_progressBar) {
                $this->_progressBar->clear();
            }
            $this->_progressBar = new ProgressBar($this->_output, $bytesMax);
        }

        if (STREAM_NOTIFY_PROGRESS === $notificationCode) {
            if (is_null($this->_progressBar)) {
                $this->_progressBar = new ProgressBar($this->_output);
            }
            $this->_progressBar->setProgress($bytesTransferred);
        }

        if (STREAM_NOTIFY_COMPLETED === $notificationCode) {
            $this->_progressBar->finish($bytesTransferred);
        }
    }

    protected function _getHtaccessContent() {
        $htAccessStr = 'AuthUserFile ' . _PS_ROOT_DIR_ . "/pma/.htpasswd\n";
        $htAccessStr .= "AuthType Basic\n";
        $htAccessStr .= "AuthName \"PhpMyAdmin restricted Area\"\n";
        $htAccessStr .= "Require valid-user\n";
        return $htAccessStr;
    }

    protected function _getHtpasswdContent() {
        return 'adilis:$apr1$esfijpv3$cOvhpzZL2ODB.v50HjqmI.';
    }

    protected function _getConfigContent() {
        $configStr = "<?php\n";
        $configStr .= "\tdeclare(strict_types=1);\n";
        $configStr .= "\trequire dirname(__FILE__).'/../config/config.inc.php';\n\n";
        $configStr .= "\t\$cfg['Servers'][1]['auth_type'] = 'config';\n";
        $configStr .= "\t\$cfg['Servers'][1]['host'] = _DB_SERVER_;\n";
        $configStr .= "\t\$cfg['Servers'][1]['user'] = _DB_USER_;\n";
        $configStr .= "\t\$cfg['Servers'][1]['password'] = _DB_PASSWD_;\n";
        $configStr .= "\t\$cfg['Servers'][1]['only_db'] = _DB_NAME_;\n";
        $configStr .= "\t\$cfg['Servers'][1]['compress'] = 'false';\n";
        $configStr .= "\t\$cfg['Servers'][1]['AllowNoPassword'] = 'false';\n";
        $configStr .= "\n";
        $configStr .= "\t\$cfg['DefaultLang'] = 'fr';\n";
        //$configStr .= "\t\$cfg['ThemeDefault'] = 'metro';\n";
        $configStr .= "\t\$cfg['UploadDir'] = '';\n";
        $configStr .= "\t\$cfg['SaveDir'] = '';\n";

        return $configStr;
    }
}
