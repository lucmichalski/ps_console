<?php

namespace Adilis\PSConsole\Command\Override\Controller;

use Adilis\PSConsole\PhpParser\Builder\FrontControllerOverrideBuilder;
use Adilis\PSConsole\PhpParser\Builder\AdminControllerOverrideBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class OverrideControllerCreateCommand extends Command {
    protected function configure() {
        $this->setName('override:controller:create');
        $this->setDescription('Create front or admin controller override')
            ->addArgument('controllerType', InputArgument::OPTIONAL, 'front(default)|admin')
            ->addArgument('controllerName', InputArgument::OPTIONAL, 'controller name');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $controllerType = $input->getArgument('controllerType');
        $controllerName = $input->getArgument('controllerName');
        $helper = $this->getHelper('question');
        $finder = new Finder();
        $controllersAutocompleter = [];

        if ($controllerType === null || !in_array($controllerType, ['front', 'admin'])) {
            $fieldQuestion = new Question('<question>Controller type ? </question>');
            $fieldQuestion->setAutocompleterValues(['front', 'admin']);
            $fieldQuestion->setValidator(function ($answer) {
                if (!in_array($answer, ['front', 'admin'])) {
                    throw new \RuntimeException('Controller type is incorrect');
                }
                return $answer;
            });
            $controllerType = $helper->ask($input, $output, $fieldQuestion);
        }

        $controllerDir = $controllerType == 'front' ? _PS_FRONT_CONTROLLER_DIR_ : _PS_ADMIN_CONTROLLER_DIR_;
        $controllersOnDisk = $finder->files()->in($controllerDir)->name('*.php')->notName('index.php');

        foreach ($controllersOnDisk as $file) {
            $controllersAutocompleter[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        }

        if ($controllerName === null || !in_array($controllerName, $controllersAutocompleter)) {
            $fieldQuestion = new Question('<question>Controller name</question>');
            $fieldQuestion->setAutocompleterValues($controllersAutocompleter);
            $fieldQuestion->setValidator(function ($answer) use ($controllersAutocompleter) {
                if (!in_array($answer, $controllersAutocompleter)) {
                    throw new \RuntimeException('Controller name is incorrect');
                }
                return $answer;
            });
            $controllerName = $helper->ask($input, $output, $fieldQuestion);
        }

        foreach ($controllersOnDisk as $file) {
            if ($controllerName === pathinfo($file->getFilename(), PATHINFO_FILENAME)) {
                $controllerPath = $file->getRelativePathname();
                break;
            }
        }

        $builder = $controllerType == 'front' ?
            new FrontControllerOverrideBuilder($controllerName, $controllerPath) :
            new AdminControllerOverrideBuilder($controllerName, $controllerPath);

        try {
            $filesystem = new Filesystem();
            $filesystem->dumpFile($builder->getFilePath(), $builder->getContent());
        } catch (IOException $e) {
            $output->writeln('<error>Unable to create override ' . $controllerName . ' : ' . $e->getMessage() . '</error>');
            return;
        }

        $output->writeln('<info>Controller override ' . $controllerName . ' created with sucess</info>');
        $this->getApplication()->find('cache:index')->run(new ArrayInput([]), $output);
        $this->getApplication()->find('dev:add-index-files')->run(new ArrayInput(['dir' => 'override/controllers']), $output);

        $output->writeln('it works');
    }
}
