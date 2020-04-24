<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Theme;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Validate;

/**
 * Class Get
 * Command sample description
 */
class ThemeGetCommand extends Command {
    protected $_progressBar = null;
    protected $_filesystem = null;
    protected $_finderSystem = null;
    protected $_ouput = null;

    protected function configure() {
        $this
            ->setName('theme:get')
            ->setDescription('Download theme from url')
            ->addArgument('url', InputArgument::OPTIONAL, 'Theme URL');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $this->_output = $output;
        $this->_filesystem = new Filesystem();
        $this->_finder = new Finder();
        $this->_helper = $this->getHelper('question');

        $url = $input->getArgument('url');
        if (!Validate::isUrl($url)) {
            $urlQuestion = new Question('<question>Please set theme URL for download ?</question>');
            $urlQuestion->setValidator(function ($answer) {
                if (!Validate::isUrl($answer)) {
                    throw new RuntimeException('Given url is invalid');
                }
                return $answer;
            });
            $url = $this->_helper->ask($input, $output, $urlQuestion);
        }
        $fileName = basename($url);
        $themeName = pathinfo($fileName, PATHINFO_FILENAME);
        $filePath = _PS_ROOT_DIR_ . '/themes/' . $fileName;

        if (!$this->_filesystem->exists($filePath)) {
            $output->writeln('<info>Start download theme</info>');
            $context = stream_context_create([], ['notification' => [$this, 'progress']]);
            $resource = file_get_contents($url, false, $context);
            $this->_progressBar->finish();
            $output->writeln('');
            file_put_contents(_PS_ROOT_DIR_ . '/themes/' . basename($url), $resource);
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) === true) {
            $zip->extractTo(_PS_ROOT_DIR_ . '/themes/' . $themeName);
            $zip->close();

            $this->_filesystem->remove($filePath);
        }
        $output->writeln('<info>Theme have successfully been copied in /themes/' . $themeName);
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
}
