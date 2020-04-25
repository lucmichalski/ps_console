<?php

namespace Adilis\PSConsole\Command\Db\Field;

use Adilis\PSConsole\Console\Helper\QuestionHelper;
use Db;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class DbFieldAddCommand extends Command {
    protected function configure() {
        $this->setName('db:field:add');
        $this->setDescription('Create field in database');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $helper = new QuestionHelper;

        $tablesInDatabase = self::_getTablesInDatase();
        $question = new Question('<question>Select table :</question>');
        $question->setAutocompleterValues($tablesInDatabase);
        $question->setValidator(function ($answer) use ($tablesInDatabase) {
            if ($answer === null || !in_array($answer, $tablesInDatabase)) {
                throw new \RuntimeException('Selected table is invalid!');
            }
            return $answer;
        });
        $table = $helper->ask($input, $output, $question);
        $fields = $helper->askManyDatabaseFields($input, $output);

        $fieldsInTable = self::_getFieldsInTable($table);
        foreach ($fields as $field) {
            if (!in_array($field['name'], $fieldsInTable)) {
                $query = 'ALTER TABLE ' . $table . ' ADD ' . self::_getFieldQueryPart($field);
                if (Db::getInstance()->execute($query)) {
                    $fieldsInTable[] = $field['name'];
                    $output->writeln('<info>Field ' . $field['name'] . ' have successfully created</info>');
                } else {
                    $output->writeln('<error>Unable to create field ' . $field['name'] . ', bad query : ' . $query . '</error>');
                }
            } else {
                $output->writeln('<error>Unable to create field ' . $field['name'] . ', this field already exists in table</error>');
            }
        }
    }

    private static function _getTablesInDatase() {
        $tables = [];
        $tablesInfos = Db::getInstance()->executeS('SHOW TABLE STATUS');
        foreach ($tablesInfos as $tableInfos) {
            $tables[] = $tableInfos['Name'];
        }
        return $tables;
    }

    private static function _getFieldsInTable($table) {
        $fields = [];
        $fieldsInfos = Db::getInstance()->executeS('DESCRIBE ' . $table);
        foreach ($fieldsInfos as $fieldInfos) {
            $fields[] = strtolower($fieldInfos['Field']);
        }
        return $fields;
    }

    private static function _getFieldQueryPart($field) {
        $required = ($field['required'] !== false) ? 'NOT NULL' : 'DEFAULT NULL';
        $sqlQueryString = '`' . $field['name'] . '`';

        switch ($field['type']) {
            case 'int':
                $fieldLength = $field['length'] ? $field['length'] : 11;
                $sqlQueryString .= ' INT(' . (int) $fieldLength . ') unsigned ' . $required;
                if ($field['required'] === false) {
                    $sqlQueryString .= ' DEFAULT "0"';
                }
                break;
            case 'bool':
                $sqlQueryString .= ' TINYINT(1) NOT NULL unsigned DEFAULT "0"';
                break;
            case 'string':
                $fieldLength = $field['length'] ? $field['length'] : 255;
                $sqlQueryString .= ' VARCHAR (' . (int) $fieldLength . ') ' . $required . '';
                break;
            case 'float':
                $fieldLength = (int) ($field['length'] ? $field['length'] : 20);
                $fieldLengthAfter = (int) (isset($field['length_after']) && $field['length_after'] ? $field['length_after'] : 6);
                $sqlQueryString .= ' DECIMAL(' . (int) $fieldLength . ',' . (int) $fieldLengthAfter . ') ' . $required;
                if ($field['required'] === false) {
                    $sqlQueryString .= ' DEFAULT "0.' . str_pad('', $fieldLengthAfter, '0') . '"';
                }
                break;
            case 'date':
                $sqlQueryString .= ' datetime NOT NULL';
                break;
            case 'html':
                $sqlQueryString .= ' text';
                break;
        }

        return $sqlQueryString;
    }
}
