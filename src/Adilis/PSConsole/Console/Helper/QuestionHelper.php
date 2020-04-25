<?php

namespace Adilis\PSConsole\Console\Helper;

use Adilis\PSConsole\Console\Question\YesNoQuestion;
use Symfony\Component\Console\Helper\QuestionHelper as HelperQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

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
                $answer['required'] = $this->ask($input, $output, new YesNoQuestion('<question>Required field :</question>', false, '/^(y|j)/i'));
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
}
