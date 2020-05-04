<?php

namespace Adilis\PSConsole\Console\Helper;

use Adilis\PSConsole\Console\Question\YesNoQuestion;
use Symfony\Component\Console\Helper\QuestionHelper as HelperQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Validate;

class QuestionHelper extends HelperQuestionHelper {
    public function askMany(InputInterface $input, OutputInterface $output, Question $question) {
        $answer_array = [];
        do {
            $answer = $this->ask($input, $output, $question);
            if ($answer !== $question->getDefault()) {
                $answer_array[] = $answer;
            }
        } while ($answer !== $question->getDefault());
        return $answer_array;
    }

    public function askManyDatabaseFields(InputInterface $input, OutputInterface $output) {
        $answer_array = [];
        $newField = null;
        $available_field_type = ['int', 'bool', 'string', 'float', 'date', 'html'];

        do {
            $answer = [];
            if ($newField !== null) {
                $answer['name'] = $newField;
            } else {
                $answer['name'] = $this->ask($input, $output, new Question('<question>Field name :</question>', 'name'));
            }

            //Field Type
            $fieldQuestion = new Question('<question>Field type :</question>', 'string');
            $fieldQuestion->setAutocompleterValues($available_field_type);
            $fieldQuestion->setValidator(function ($answer) use ($available_field_type) {
                if ($answer !== null && !in_array($answer, $available_field_type)) {
                    throw new \RuntimeException('The field type must be part of the suggested');
                }
                return $answer;
            });
            $answer['type'] = $this->ask($input, $output, $fieldQuestion);

            $answer['required'] = false;
            if ($answer['type'] != 'bool' && $answer['type'] != 'date') {
                $answer['required'] = $this->ask($input, $output, new YesNoQuestion('<question>Required field :</question>', false));
            }

            switch ($answer['type']) {
                case 'int': $lengthDefaultValue = 11; break;
                case 'string': $lengthDefaultValue = 255; break;
                case 'float': $lengthDefaultValue = 20; break;
            }
            $answer['length'] = null;
            if (in_array($answer['type'], ['int', 'string', 'float'])) {
                $answer['length'] = $this->ask($input, $output, new Question('<question>Field length (default is ' . $lengthDefaultValue . ') :</question>', $lengthDefaultValue));
            }

            $answer['length_after'] = null;
            if ($answer['length_after'] == 'float') {
                $answer['length_after'] = $this->ask($input, $output, new Question('<question>Field length after point (default is 6) :</question>'));
            }

            $answer_array[] = $answer;
            $output->writeln('');

            $newField = $this->ask(
                $input,
                $output,
                new Question('<question>Field name (leave empty to stop) : </question>', null)
            );
        } while ($newField !== null);
        return $answer_array;
    }

    public function askManyObjectFields(InputInterface $input, OutputInterface $output) {
        $answer_array = [];
        $newField = null;
        $available_field_type = ['int', 'bool', 'string', 'float', 'date', 'html'];

        do {
            $answer = [];
            if ($newField !== null) {
                $answer['name'] = $newField;
            } else {
                $answer['name'] = $this->ask($input, $output, new Question('<question>Field name :</question>', 'name'));
            }

            //Field Type
            $fieldQuestion = new Question('<question>Field type :</question>', 'string');
            $fieldQuestion->setAutocompleterValues($available_field_type);
            $fieldQuestion->setValidator(function ($answer) use ($available_field_type) {
                if ($answer !== null && !in_array($answer, $available_field_type)) {
                    throw new \RuntimeException('The field type must be part of the suggested');
                }
                return $answer;
            });
            $answer['type'] = $this->ask($input, $output, $fieldQuestion);

            $answer['required'] = false;
            if ($answer['type'] != 'bool' && $answer['type'] != 'date') {
                $answer['required'] = $this->ask($input, $output, new YesNoQuestion('<question>Required field :</question>', false));
            }

            $answer['lang'] = false;
            if ($answer['type'] == 'string' || $answer['type'] == 'html') {
                $answer['lang'] = $this->ask($input, $output, new YesNoQuestion('<question>Lang field (y/n) default n:</question>', false));
            }

            $answer['shop'] = false;
            if (!$answer['lang']) {
                $answer['shop'] = $this->ask($input, $output, new YesNoQuestion('<question>Shop field (y/n) default n:</question>', false));
            }

            //Field Validate rule
            $validationFunctions = $this->_getValidationFunctions();
            switch ($answer['type']) {
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
            $answer['validation'] = $this->ask($input, $output, $validationQuestion);

            switch ($answer['type']) {
                case 'int': $lengthDefaultValue = 11; break;
                case 'string': $lengthDefaultValue = 255; break;
                case 'float': $lengthDefaultValue = 20; break;
            }
            $answer['length'] = null;
            if (in_array($answer['type'], ['int', 'string', 'float'])) {
                $answer['length'] = $this->ask($input, $output, new Question('<question>Field length (default is ' . $lengthDefaultValue . ') :</question>', $lengthDefaultValue));
            }

            $answer['length_after'] = null;
            if ($answer['length_after'] == 'float') {
                $answer['length_after'] = $this->ask($input, $output, new Question('<question>Field length after point (default is 6) :</question>'));
            }

            $answer_array[] = $answer;
            $output->writeln('');

            $newField = $this->ask(
                $input,
                $output,
                new Question('<question>Field name (leave empty to stop) : </question>', null)
            );
        } while ($newField !== null);
        return $answer_array;
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
