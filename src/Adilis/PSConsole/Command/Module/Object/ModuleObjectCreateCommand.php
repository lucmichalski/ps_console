<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module\Object;

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

    /** @var Filesystem */
    protected $_filesystem;

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

        $params = [];

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

        $params['table_name'] = $tableName;
        $params['primary'] = $primary;
        $params['multishop'] = $multishop;
        $params['multilang'] = false;

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
                    throw new RuntimeException('The field type must be part of the suggested');
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
                if ($lang && $params['multilang'] == false) {
                    $params['multilang'] = true;
                }
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

        $params['fields'] = $fields;

        //Ask if sql generation is needed
        $sql = $helper->ask(
            $input,
            $output,
            new ConfirmationQuestion('<question>Generate sql (y/n) default y ?</question>', true, '/^(y|j)/i')
        );

        $this->_filesystem = new Filesystem();
        $this->_moduleName = $moduleName;

        if ($sql) {
            $sqlQueries = $this->_generateSql($params);
        } else {
            $sqlQueries = [];
        }

        $defaultContent =
            str_replace(
                [
                    '{object}',
                    '{object_properties}',
                    '{object_definition}',
                    '{todo_list}'
                ],
                [
                    $objectClass,
                    $this->_getObjectProperties($fields),
                    $this->_getObjectDefinition($params),
                    '',
                ],
                $this->_getDefaultContent()
            );

        try {
            if (!$this->_filesystem->exists(_PS_MODULE_DIR_ . $this->_moduleName . '/classes')) {
                $this->_filesystem->mkdir(_PS_MODULE_DIR_ . $this->_moduleName . '/classes', 0775);
            }
            $this->addIndexFiles(_PS_MODULE_DIR_ . $this->_moduleName . '/classes');

            $this->_filesystem->dumpFile(
                _PS_MODULE_DIR_ . $moduleName . '/classes/' . $objectClass . '.php',
                $defaultContent
            );
        } catch (IOException $e) {
            $output->writeln('<error>Unable to create model file</error>');
            return false;
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

    protected function _getObjectProperties(array $fields) {
        $fieldStr = '';
        foreach ($fields as $field) {
            $fieldStr .= "\t\tpublic \$" . $field['name'];
            switch ($field['type']) {
                case 'float':
                case 'int':
                    if ($field['required']) {
                        $fieldStr .= ' = 0';
                    }
                    break;
                case 'bool':
                    $fieldStr .= ' = 0';
                    break;
                case 'string':
                    if (!$field['lang'] && $field['required']) {
                        $fieldStr .= " = ''";
                    }
                    break;
            }
            $fieldStr .= ";\n";
        }
        return $fieldStr;
    }

    protected function _getObjectDefinition(array $params) {
        $defStr = "\t\tpublic static \$definition = array(\n";
        $defStr .= "\t\t\t'table' => '" . $params['table_name'] . "',\n";
        $defStr .= "\t\t\t'primary' => '" . $params['primary'] . "',\n";
        $defStr .= "\t\t\t'multishop' => '" . ($params['multishop'] ? 'true' : 'false') . "',\n";
        $defStr .= "\t\t\t'multilang' => '" . ($params['multilang'] ? 'true' : 'false') . "',\n";
        $defStr .= "\t\t\t'multilang_shop' => '" . ($params['multilang'] && $params['multishop'] ? 'true' : 'false') . "',\n";
        $defStr .= "\t\t\t'fields' => array(\n";

        foreach ($params['fields'] as $field) {
            $type = 'TYPE_' . strtoupper($field['type']);
            $defStr .= "\t\t\t\t'" . $field['name'] . "' => array('type' => self::" . $type;
            if ($field['validate']) {
                $defStr .= ", 'validate' => '" . $field['validate'] . "'";
            }
            if ($field['length']) {
                $defStr .= ", 'length' => " . (int) $field['length'];
            }
            if ($field['lang'] == true) {
                $defStr .= ", 'lang' => true";
            }
            if ($field['shop'] == true) {
                $defStr .= ", 'shop' => true";
            }
            $defStr .= "),\n";
        }
        $defStr = rtrim($defStr, ",\n");
        $defStr .= "\n\t\t\t)\n\t\t);";

        return $defStr;
    }

    protected function _getDefaultContent() {
        return
            '<?php
    if (!defined(\'_PS_VERSION_\')) {
        exit;
    }
{todo_list}
    class {object} extends ObjectModel
    {
        public $id;
{object_properties}
{object_definition}
    }
';
    }

    protected function _generateSql(array $params) {
        $sql = [];
        if ($this->_filesystem->exists(_PS_MODULE_DIR_ . $this->_moduleName . '/sql/install.php')) {
            try {
                @require_once _PS_MODULE_DIR_ . $this->_moduleName . '/sql/install.php';
            } catch (\Exception $e) {
            }
        }

        $sqlQueryString = "'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . '" . $params['table_name'] . "` (\n";
        $sqlQueryString .= "\t\t`" . $params['primary'] . "` int(11) unsigned NOT NULL AUTO_INCREMENT,\n";

        $sqlLangQueryString = "'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . '" . $params['table_name'] . "_lang` (\n";
        $sqlLangQueryString .= "\t\t`" . $params['primary'] . "` int(11) unsigned NOT NULL,\n";
        $sqlLangQueryString .= "\t\t`id_lang` int(11) unsigned NOT NULL,\n";
        if (isset($params['multishop'])) {
            $sqlLangQueryString .= "\t\t`id_shop` int(11) unsigned NOT NULL DEFAULT \"1\",\n";
        }

        $sqlShopQueryString = "'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . '" . $params['table_name'] . "_shop` (\n";
        $sqlShopQueryString .= "\t\t`" . $params['primary'] . "` int(11) unsigned NOT NULL,\n";
        $sqlShopQueryString .= "\t\t`id_shop` int(11) unsigned NOT NULL DEFAULT \"1\",\n";

        foreach ($params['fields'] as $field) {
            $required = ($field['required'] !== false) ? 'NOT NULL' : 'DEFAULT NULL';
            $fieldString = "\t\t`" . $field['name'] . '`';

            switch ($field['type']) {
                case 'int':
                    $fieldLength = $field['length'] ? $field['length'] : 11;
                    $fieldString .= ' INT(' . (int) $fieldLength . ') unsigned' . $required;
                    if ($field['required'] === false) {
                        $fieldString .= ' DEFAULT "0"';
                    }
                    break;
                case 'bool':
                    $fieldString .= ' TINYINT(1) NOT NULL unsigned DEFAULT "0"';
                    break;
                case 'string':
                    $fieldLength = $field['length'] ? $field['length'] : 255;
                    $fieldString .= ' VARCHAR (' . (int) $fieldLength . ') ' . $required . '';
                    break;
                case 'float':
                    $fieldLength = (int) ($field['length'] ? $field['length'] : 20);
                    $fieldLengthAfter = (int) (isset($field['length_after']) && $field['length_after'] ? $field['length_after'] : 6);
                    $fieldString .= ' DECIMAL(' . (int) $fieldLength . ',' . (int) $fieldLengthAfter . ') ' . $required;
                    if ($field['required'] === false) {
                        $fieldString .= ' DEFAULT "0.' . str_pad('', $fieldLengthAfter, '0') . '"';
                    }
                    break;
                case 'date':
                    $fieldString .= ' datetime NOT NULL';
                    break;
                case 'html':
                    $fieldString .= ' text';
                    break;
            }

            if ($field['lang'] !== false) {
                $sqlLangQueryString .= $fieldString . ",\n";
            } else {
                $sqlQueryString .= $fieldString . ",\n";
            }

            if ($field['shop'] !== false) {
                $sqlShopQueryString .= $fieldString . ",\n";
            }
        }

        $sqlQueryString .= "\t\tPRIMARY KEY (`" . $params['primary'] . "`)\n";
        $sqlQueryString .= "\t) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';\n";

        if ($params['multishop']) {
            $sqlLangQueryString .= "\t\tPRIMARY KEY (`" . $params['primary'] . "`, `id_shop`, `id_lang`)\n";
        } else {
            $sqlLangQueryString .= "\t\tPRIMARY KEY (`" . $params['primary'] . "`, `id_lang`)\n";
        }
        $sqlLangQueryString .= "\t) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';\n";

        $sqlShopQueryString .= "\t\tPRIMARY KEY (`" . $params['primary'] . "`, `id_shop`)\n";
        $sqlShopQueryString .= "\t) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';\n";

        $sql[$params['table_name']] = $sqlQueryString;
        if ($params['multilang']) {
            $sql[$params['table_name'] . '_lang'] = $sqlLangQueryString;
        }
        if ($params['multishop']) {
            $sql[$params['table_name'] . '_shop'] = $sqlShopQueryString;
        }

        $queries = '';
        foreach ($sql as $tableName => $query) {
            $queries .= "\t\$sql['" . $params['table_name'] . "'] = " . $query . "\n";
        }
        $queries = rtrim($queries);
        $defaultContent = str_replace('{queries}', $queries, $this->_getSQLInstallDefaultContent());

        try {
            if (!$this->_filesystem->exists(_PS_MODULE_DIR_ . $this->_moduleName . '/sql')) {
                $this->_filesystem->mkdir(_PS_MODULE_DIR_ . $this->_moduleName . '/sql', 0775);
            }

            $this->_filesystem->dumpFile(
                _PS_MODULE_DIR_ . $this->_moduleName . '/sql/install.php',
                $defaultContent
            );
        } catch (IOException $e) {
            $output->writeln('<error>Unable to create model file</error>');
            return false;
        }
    }

    protected function _getSQLInstallDefaultContent() {
        return
            '<?php

    $sql = array();
    
{queries}

    foreach ($sql as $query) {
        if (Db::getInstance()->execute($query) == false) {
            return false;
        }
    }
';
    }

    private function addIndexFiles($dir) {
        try {
            if (!is_dir($dir)) {
                throw new \Exception('directory doesn\'t exists');
            }

            $finder = new Finder();
            $indexFile = $finder->files()->in((string) $dir)->depth('==0')->name('index.php');
            if (!sizeof($indexFile)) {
                copy(_PS_IMG_DIR_ . 'index.php', $dir . DIRECTORY_SEPARATOR . 'index.php');
            }

            $directories = $finder->directories()->in($dir);

            $i = 0;
            foreach ($directories as $directory) {
                ${$i} = new Finder();
                $indexFile = ${$i}->files()->in((string) $directory)->depth('==0')->name('index.php');
                if (!sizeof($indexFile)) {
                    copy(_PS_IMG_DIR_ . 'index.php', $directory . DIRECTORY_SEPARATOR . 'index.php');
                }
                $i++;
            }
        } catch (\Exception $e) {
            $output->writeln('<info>ERROR:' . $e->getMessage() . '</info>');
        }
    }
}
