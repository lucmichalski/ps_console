<?php

namespace Adilis\PSConsole\Command\Override\Module;

use Adilis\PSConsole\Template\Builder\ModuleOverrideTemplateBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class OverrideModuleCreateCommand extends Command {
    protected function configure() {
        $this
            ->setName('override:module:create')
            ->setDescription('Create module override')
            ->addArgument('moduleName', InputArgument::OPTIONAL, 'module name');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $moduleName = $input->getArgument('moduleName');
        $helper = $this->getHelper('question');
        $modulesAutocompleter = [];

        foreach (\Module::getModulesOnDisk() as $module) {
            $modulesAutocompleter[] = $module->name;
        }

        if ($moduleName === null || !in_array($moduleName, $modulesAutocompleter)) {
            $fieldQuestion = new Question('<question>Module name:</question>');
            $fieldQuestion->setAutocompleterValues($modulesAutocompleter);
            $fieldQuestion->setValidator(function ($answer) use ($modulesAutocompleter) {
                if ($answer === null || !in_array($answer, $modulesAutocompleter)) {
                    throw new \RuntimeException('Module name is incorrect');
                }
                return $answer;
            });
            $moduleName = $helper->ask($input, $output, $fieldQuestion);
        }

        try {
            $builder = new ModuleOverrideTemplateBuilder($moduleName);
            $builder->writeFile();
        } catch (\Exception $e) {
            $output->writeln('<error>Unable to write file: ' . $e->getMessage() . '</error>');
            return;
        }

        $this->getApplication()->find('cache:index')->run(new ArrayInput([]), $output);
        $this->getApplication()->find('dev:add-index-files')->run(new ArrayInput(['dir' => 'override/modules']), $output);
    }
}
