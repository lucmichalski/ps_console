<?php

namespace Adilis\PSConsole\Console\Helper;

use Symfony\Component\Console\Helper\QuestionHelper as HelperQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class QuestionHelper extends HelperQuestionHelper
{

    public function askMany(InputInterface $input, OutputInterface $output, Question $question)
    {
        $answer_array = [];
        do {
            $answer = $this->ask($input, $output, $question);
            if ($answer !== $question->getDefault()) {
                $answer_array[] = $answer;
            }
        } while ($answer !== $question->getDefault());
        return $answer_array;
    }
}
