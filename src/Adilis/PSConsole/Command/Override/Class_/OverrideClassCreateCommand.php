<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Override\Class_;

use Adilis\PSConsole\Template\Builder\ClassOverrideTemplateBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Finder\Finder;

/**
 * Class class
 * Command sample description
 */
class OverrideClassCreateCommand extends Command {
    protected function configure() {
        $this
            ->setName('override:class:create')
            ->setDescription('Create core class override')
            ->addArgument('className', InputArgument::OPTIONAL, 'class name');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $className = $input->getArgument('className');
        $helper = $this->getHelper('question');
        $finder = new Finder();
        $classes_autocompleter = [];

        $classesOnDisk = $finder->files()->in(_PS_CLASS_DIR_)->name('*.php')->notName('index.php');
        foreach ($classesOnDisk as $file) {
            $classes_autocompleter[] = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        }

        if ($className === null || !in_array($className, $classes_autocompleter)) {
            $fieldQuestion = new Question('<question>Class name</question>');
            $fieldQuestion->setAutocompleterValues($classes_autocompleter);
            $fieldQuestion->setValidator(function ($answer) use ($classes_autocompleter) {
                if (!in_array($answer, $classes_autocompleter)) {
                    throw new \RuntimeException('Class name is incorrect');
                }
                return $answer;
            });
            $className = $helper->ask($input, $output, $fieldQuestion);
        }

        foreach ($classesOnDisk as $file) {
            if ($className === pathinfo($file->getFilename(), PATHINFO_FILENAME)) {
                $classPath = $file->getRelativePathname();
                break;
            }
        }

        try {
            $builder = new ClassOverrideTemplateBuilder($className, $classPath);
            $builder->writeFile();
        } catch (\Exception $e) {
            $output->writeln('<error>Unable to write file: ' . $e->getMessage() . '</error>');
            return;
        }

        $output->writeln('<info>Class override ' . $className . ' created with sucess</info>');
        $this->getApplication()->find('cache:index')->run(new ArrayInput([]), $output);
        $this->getApplication()->find('dev:add-index-files')->run(new ArrayInput(['dir' => 'override/classes']), $output);
    }
}
