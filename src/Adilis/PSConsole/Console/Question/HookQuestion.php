<?php

namespace Adilis\PSConsole\Console\Question;

use Hook;
use Symfony\Component\Console\Question\Question;
use Validate;

class HookQuestion extends Question {
    public function __construct($question, $default = null) {
        parent::__construct($question, $default);
        $this
            ->setAutocompleterValues(self::getHookList())
            ->setValidator(function ($answer) {
                if ($answer !== null && !Validate::isHookName($answer)) {
                    throw new \RuntimeException('Module version is incorrect');
                }
                return $answer;
            });
    }

    private function getHookList() {
        $hooks = array_map(function ($row) {
            return $row['name'];
        }, Hook::getHooks());

        return $hooks;
    }
}
