<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Theme;

use Adilis\PSConsole\Console\Question\YesNoQuestion;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Validate;

/**
 * Class Child
 * Command sample description
 */
class ChildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('theme:child')
            ->setDescription('Create child theme')
            ->addArgument('parent', InputArgument::OPTIONAL, 'Original theme')
            ->addArgument('name', InputArgument::OPTIONAL, 'Child theme name')
            ->addArgument('displayName', InputArgument::OPTIONAL, 'Child theme display name');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $output->writeln("<error>This command is only available for Prestashop > 1.7.0.0 </error>");
            return;
        }

        $this->_helper = $this->getHelper('question');
        $this->_filesystem = new Filesystem;

        $parent = $input->getArgument('parent');
        $name = $input->getArgument('name');
        $displayName = $input->getArgument('displayName');
        $themes = $this->getThemesOnDisk();

        if (!Validate::isThemeName($parent)) {
            $themeQuestion = new Question('<question>Which theme will be the basis ?</question>');
            $themeQuestion->setAutocompleterValues($themes);
            $themeQuestion->setValidator(function ($answer) use ($themes) {
                if (!in_array($answer, $themes)) {
                    throw new RuntimeException('Given theme name is invalid');
                }
                return $answer;
            });
            $parent = $this->_helper->ask($input, $output, $themeQuestion);
        }

        if (!Validate::isThemeName($name)) {
            $themeQuestion = new Question('<question>Set the name of the child theme</question>');
            $themeQuestion->setValidator(function ($answer) {
                if (!Validate::isThemeName($answer)) {
                    throw new RuntimeException('Given theme name is invalid');
                }
                return $answer;
            });
            $name = $this->_helper->ask($input, $output, $themeQuestion);
        }

        if ($displayName === null || !Validate::isName($displayName)) {
            $themeQuestion = new Question('<question>Set the display name of the child theme</question>');
            $themeQuestion->setValidator(function ($answer) {
                if ($answer === null || !Validate::isName($answer)) {
                    throw new RuntimeException('Given theme name is invalid');
                }
                return $answer;
            });
            $displayName = $this->_helper->ask($input, $output, $themeQuestion);
        }

        if ($this->_filesystem->exists(_PS_ALL_THEMES_DIR_ . $name)) {
            if (!$this->_helper->ask($input, $output, new YesNoQuestion('<question>Child directory already exits, do you want to delete it ?</question>'))) {
                return;
            }
            $this->_filesystem->remove(_PS_ALL_THEMES_DIR_ . $name);
        }

        $this->_filesystem->mkdir(_PS_ALL_THEMES_DIR_ . $name, 0755);

        $configArray = [
            'parent' => $parent,
            'name' => $name,
            'display_name' => $displayName,
            'version' => '1.0.0',
            'author' => [
                'name' => 'Adilis',
                'email' => 'email'
            ]
        ];
        $configArray = Yaml::dump($configArray, 3);
        $this->_filesystem->dumpFile(_PS_ALL_THEMES_DIR_ . $name . '/config/theme.yml', $configArray);

        if ($this->_filesystem->exists(_PS_ALL_THEMES_DIR_  . $parent . '/preview.png')) {
            $this->_filesystem->copy(
                _PS_ALL_THEMES_DIR_ . $parent . '/preview.png',
                _PS_ALL_THEMES_DIR_ . $name . '/preview.png'
            );
        }

        if ($this->_filesystem->exists(_PS_ALL_THEMES_DIR_  . $parent . '/_dev/')) {
            $this->_filesystem->mirror(
                _PS_ALL_THEMES_DIR_ . $parent . '/_dev/',
                _PS_ALL_THEMES_DIR_ . $name . '/_dev/'
            );
        }

        if ($this->_filesystem->exists(_PS_ALL_THEMES_DIR_ . '/' . $parent . '/assets/')) {
            $this->_filesystem->mirror(
                _PS_ALL_THEMES_DIR_ . '/' . $parent . '/assets/',
                _PS_ALL_THEMES_DIR_ . '/' . $name . '/assets/'
            );
        }

        $output->writeln("<info>Child theme have been successfully created</info>");
    }

    private function getThemesOnDisk()
    {
        $suffix = 'config/theme.yml';
        $themeDirectories = glob(_PS_ALL_THEMES_DIR_ . '*/' . $suffix, GLOB_NOSORT);

        $themes = array();
        foreach ($themeDirectories as $directory) {
            $name = basename(substr($directory, 0, -strlen($suffix)));
            $themes[] = $name;
        }

        return $themes;
    }
}
