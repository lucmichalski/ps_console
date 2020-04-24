<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module;

use Adilis\PSConsole\Command\Module\ModuleAbstract;
use Adilis\PSConsole\Console\Helper\QuestionHelper;
use Adilis\PSConsole\PhpParser\Builder\ModuleBuilder;
use Adilis\PSConsole\Console\Question\HookQuestion;
use Adilis\PSConsole\Console\Question\YesNoQuestion;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Class create
 * Command sample description
 */
class ModuleCreateCommand extends ModuleAbstract {
    protected function configure() {
        $this
            ->setName('module:create')
            ->setDescription('Generate module skeleton')
            ->addModuleNameArgument()
            ->addHookListArgument();
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $author = $this->_helper->ask($input, $output, new Question('<question>Module author :</question>', Configuration::get('PSC_AUTHOR_DEFAULT')));
        $displayName = $this->_helper->ask($input, $output, new Question('<question>Module Display name :</question>', $this->_moduleName));
        $description = $this->_helper->ask($input, $output, new Question('<question>Module description :</question>', $this->_moduleName));
        $widgetAnswer = (version_compare(_PS_VERSION_, '1.7', '>=')) ?
            $this->_helper->ask($input, $output, new YesNoQuestion('Implement widget Interface'))
            : null;
        $templateAnswer = (count($this->_hookList)) ?
            $this->_helper->ask($input, $output, new YesNoQuestion('Generate templates for content hooks ?'))
            : null;

        if (is_dir($this->_modulePath)) {
            $output->writeln('<error>Module already exists</error>');
        }

        $builder = new ModuleBuilder($this->_moduleName, $author, $displayName, $description, $this->_hookList, $widgetAnswer, $templateAnswer);
        $this->_filesystem->dumpFile($builder->getFilePath(), $builder->getContent());
        if (Configuration::get('PSC_LOGO_DEFAULT_PATH') && $this->_filesystem->exists(Configuration::get('PSC_LOGO_DEFAULT_PATH'))) {
            $this->_filesystem->copy(Configuration::get('PSC_LOGO_DEFAULT_PATH'), $this->_modulePath . '/logo.png');
        }

        if ($templateAnswer) {
            foreach ($this->_hookList as $hook) {
                if (preg_match('#^display#', $hook)) {
                    $defaultContent = '<p>Content of hook ' . $hook . ' auto-generated</p>';
                    $fileName = $this->_modulePath . '/views/templates/hook/' . strtolower($hook) . '.tpl';
                    $this->_filesystem->dumpFile($fileName, $defaultContent);
                }
            }
        }

        $output->writeln('<info>Module generated with success</info>');
        $this->getApplication()->find('dev:add-index-files')->run(new ArrayInput(['dir' => $this->_moduleRelativePath]), $output);
    }
}
