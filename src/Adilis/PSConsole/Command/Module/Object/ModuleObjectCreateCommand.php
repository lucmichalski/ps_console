<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module\Object;

use Adilis\PSConsole\PhpParser\Builder\ModuleObjectBuilder;
use Adilis\PSConsole\PhpParser\Builder\ModuleObjectInstallBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Validate;

/**
 * Class object
 * Command sample description
 */
class ModuleObjectcreateCommand extends Command {
    /** @var string Module Name */
    protected $_moduleName;

    const FIELD_TYPES = ['int', 'bool', 'string', 'float', 'date', 'html'];

    protected function configure() {
        $this
            ->setName('module:object:create')
            ->setDescription('Generate module model object')
            ->addArgument('moduleName', InputArgument::REQUIRED, 'module name');
    }

    /**
     * @inheritDoc
     */
    public function execute(InputInterface $input, OutputInterface $output) {
        $moduleName = $input->getArgument('moduleName');
        $helper = $this->getHelper('question');

        $objectQuestion = new Question('<question>Model Class :</question>');
        $objectQuestion->setValidator(function ($answer) {
            if (!Validate::isFileName($answer)) {
                throw new \RuntimeException('The className is not valid');
            }
            return $answer;
        });

        $objectClass = $helper->ask($input, $output, $objectQuestion);
        $tableName = $helper->ask($input, $output, new Question('<question>Table name :</question>', 'sample'));
        $primary = $helper->ask($input, $output, new Question('<question>Primary key :</question>', 'id_sample'));
        $multishop = $helper->ask($input, $output, new ConfirmationQuestion('<question>Create multishop association (y/n) default y :</question>', true, '/^(y|j)/i'));

        $output->writeln('');

        $fields = [];
        do {
            //Liste des champs
            //Nom du champ
            if (isset($newField)) {
                $name = $newField;
            } else {
                $name = $helper->ask($input, $output, new Question('<question>Field name :</question>', 'name'));
            }

            //Field Type
            $fieldQuestion = new Question('<question>Field type :</question>', 'string');
            $fieldQuestion->setAutocompleterValues(self::FIELD_TYPES);
            $fieldQuestion->setValidator(function ($answer) {
                if ($answer !== null && !in_array($answer, self::FIELD_TYPES)) {
                    throw new \RuntimeException('The field type must be part of the suggested');
                }
                return $answer;
            });
            $type = $helper->ask($input, $output, $fieldQuestion);

            $required = false;
            if ($type != 'bool' && $type != 'date') {
                $required = $helper->ask($input, $output, new ConfirmationQuestion('<question>Required field (y/n) default n :</question>', false, '/^(y|j)/i'));
            }

            $lang = false;
            if ($type == 'string' || $type == 'html') {
                $lang = $helper->ask($input, $output, new ConfirmationQuestion('<question>Lang field (y/n) default n:</question>', false, '/^(y|j)/i'));
            }

            if ($multishop && !$lang) {
                $shop = $helper->ask($input, $output, new ConfirmationQuestion('<question>Shop field (y/n) default n:</question>', false, '/^(y|j)/i'));
            } else {
                $shop = false;
            }

            //Field Validate rule
            $validationFunctions = $this->_getValidationFunctions();
            switch ($type) {
                case 'int':
                    $validationDefaultValue = 'isInt';
                    break;
                case 'bool':
                    $validationDefaultValue = 'isBool';
                    break;
                case 'string':
                    $validationDefaultValue = 'isString';
                    break;
                case 'float':
                    $validationDefaultValue = 'isFloat';
                    break;
                case 'date':
                    $validationDefaultValue = 'isDate';
                    break;
                case 'html':
                    $validationDefaultValue = 'isCleanHtml';
                    break;
                default:
                    $validationDefaultValue = 'isAnything';
            }

            $validationQuestion = new Question('<question>Field validation (default is ' . $validationDefaultValue . ') :</question>', $validationDefaultValue);
            $validationQuestion->setAutocompleterValues($validationFunctions);
            $validationQuestion->setValidator(function ($answer) use ($validationFunctions) {
                if ($answer !== null && !in_array($answer, $validationFunctions)) {
                    throw new \RuntimeException('The validate function must be part of the suggested');
                }
                return $answer;
            });
            $validation = $helper->ask($input, $output, $validationQuestion);

            switch ($type) {
                case 'int':
                    $lengthDefaultValue = 11;
                    break;
                case 'string':
                    $lengthDefaultValue = 255;
                    break;
                case 'float':
                    $lengthDefaultValue = 20;
                    break;
            }
            if (in_array($type, ['int', 'string', 'float'])) {
                $length = $helper->ask($input, $output, new Question('<question>Field length (default is ' . $lengthDefaultValue . ') :</question>', $lengthDefaultValue));
            } else {
                $length = null;
            }
            $length_after = null;
            if ($type == 'float') {
                $length_after = $helper->ask($input, $output, new Question('<question>Field length after point (default is 6) :</question>'));
            }

            $fields[] = [
                'name' => $name,
                'required' => $required,
                'lang' => $lang,
                'type' => $type,
                'validate' => $validation,
                'length' => $length,
                'length_after' => $length_after,
                'shop' => $shop,
            ];

            $output->writeln('');

            //Ask for create a new field
            $newField = $helper->ask(
                $input,
                $output,
                new Question('<question>Field name (leave empty to stop) : </question>', false)
            );
        } while ($newField !== false);

        //Ask if sql generation is needed
        $generateInstallQuery = $helper->ask(
            $input,
            $output,
            new ConfirmationQuestion('<question>Generate sql (y/n) default y ?</question>', true, '/^(y|j)/i')
        );

        /* QUESTION ARE DONE */
        $filesystem = new Filesystem;
        $builder = new ModuleObjectBuilder($moduleName, $objectClass, $tableName, $primary, $multishop, $fields);
        try {
            $filesystem->dumpFile($builder->getFilePath(), $builder->getContent());
        } catch (IOException $e) {
            $output->writeln('<error>Unable to create object ' . $objectClass . ' : ' . $e->getMessage() . '</error>');
            return;
        }

        if ($generateInstallQuery) {
            if ($filesystem->exists(_PS_MODULE_DIR_ . $this->_moduleName . '/sql/install.php')) {
            } else {
                $builder = new ModuleObjectInstallBuilder($moduleName, $tableName, $primary, $multishop, $fields);
                try {
                    $filesystem->dumpFile($builder->getFilePath(), $builder->getContent());
                } catch (IOException $e) {
                    $output->writeln('<error>Unable to create install.php for object ' . $objectClass . ' : ' . $e->getMessage() . '</error>');
                    return;
                }
            }
        }

        $output->writeln('<info>Model file generated</info>');
    }

    protected function _getValidationFunctions($fieldType = null) {
        $functions = [];
        try {
            $validation = new \ReflectionClass(Validate::class);
            foreach ($validation->getMethods() as $method) {
                $functions[] = $method->name;
            }
        } catch (\ReflectionException $e) {
        }
        return $functions;
    }
}
