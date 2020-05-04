<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module;

use Adilis\PSConsole\PhpParser\Builder\ModuleUpgradeBuilder;
use Adilis\PSConsole\Template\Builder\ModuleUpgradeTemplateBuilder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class upgrade
 * Command sample description
 */
class ModuleUpgradeCommand extends ModuleAbstract {
    protected function configure() {
        $this
            ->setName('module:create:upgrade')
            ->setDescription('Generate module upgrade file')
            ->addModuleNameArgument()
            ->addArgument('moduleVersion', InputArgument::OPTIONAL, 'module version');
        ;
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $moduleVersion = $input->getArgument('moduleVersion');
        if ($moduleVersion === null || !self::isValidModuleVersion($moduleVersion)) {
            $fieldQuestion = new Question('<question>Module version:</question>');
            $fieldQuestion->setValidator(function ($answer) {
                if (!self::isValidModuleVersion($answer)) {
                    throw new \RuntimeException('Module version is incorrect');
                }
                return $answer;
            });
            $moduleVersion = $this->_helper->ask($input, $output, $fieldQuestion);
        }

        if (!$this->_filesystem->exists($this->_modulePath)) {
            $output->writeln('<error>Module not exists</error>');
            return;
        }

        try {
            $builder = new ModuleUpgradeTemplateBuilder($this->_moduleName, $moduleVersion);
            $builder->writeFile();
            $output->writeln('<info>Update file generated</info>');
            $this->getApplication()->find('dev:add-index-files')->run(new ArrayInput(['dir' => $this->_moduleRelativePath . 'upgrade']), $output);
        } catch (\Exception $e) {
            $output->writeln('<error>Unable to write file: ' . $e->getMessage() . '</error>');
            return;
        }
    }

    private static function isValidModuleVersion($moduleVersion) {
        return preg_match('#^[0-9]{1}\.[0-9]+\.?[0-9]*$#', $moduleVersion);
    }
}
