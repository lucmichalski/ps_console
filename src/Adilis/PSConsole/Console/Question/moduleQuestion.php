<?php

namespace Adilis\PSConsole\Console\Question;

use Symfony\Component\Console\Question\Question;
use Validate;

class hookQuestion extends Question
{
    public function __construct($question, $default = null)
    {
        parent::__construct($question, $default);
        $this
            ->setValidator(function ($answer) {
                if ($answer !== null && !Validate::isModuleName($answer)) {
                    throw new \RuntimeException('Module name is incorrect');
                }
                return $answer;
            });
    }
}
