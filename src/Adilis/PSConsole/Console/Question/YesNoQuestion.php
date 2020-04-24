<?php

namespace Adilis\PSConsole\Console\Question;

use Symfony\Component\Console\Question\ConfirmationQuestion;

class YesNoQuestion extends ConfirmationQuestion {
    public function __construct($question, $default = true, $trueAnswerRegex = '/^y/i') {
        $question .= '[y/n] (default is ' . ($default ? 'y' : 'n') . ')';
        parent::__construct($question, $default, $trueAnswerRegex);
    }
}
