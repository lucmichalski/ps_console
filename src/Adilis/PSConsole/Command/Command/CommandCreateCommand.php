<?php

namespace Adilis\PSConsole\Command\Command;

use Adilis\PSConsole\PhpParser\Builder\CommandBuilder;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class CreateCommand
 * Command sample description
 */
class CommandCreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('command:create')
            ->setDescription('Create a new command skeleton')
            ->setAliases(['cmd:create']);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $commandName = $helper->ask($input, $output, $this->_getCommandNameQuestion());
        $commandDescription = $helper->ask($input, $output, $this->_getCommandDescriptionQuestion());

        $builder = new CommandBuilder($commandName, $commandDescription);
        try {
            $filesystem = new Filesystem();
            $filesystem->dumpFile($builder->getFilePath(), $builder->getContent());
        } catch (RuntimeException $e) {
            $output->writeln('<error>Unable to generate the command :' . $e->getMessage() . '</error>');
            return 1;
        }

        $output->writeln('<info>Command Created with success</info>');
        return 0;
    }

    /**
     * Get command name question
     * @return Question
     */
    protected function _getCommandNameQuestion()
    {
        $question = new Question('<question>Command Name (ex domain:action or domain:subdomain:action )</question>');
        $question->setNormalizer(function ($anwser) {
            return $anwser ? trim($anwser) : null;
        });
        $question->setValidator(function ($answer) {
            if ($answer === null || !preg_match('#^[a-z]+:[a-z]+(?::[a-z]+)?$#', $answer)) {
                throw new RuntimeException('The command name is not valid, it must use a format like domain:action or domain:subdomain:action');
            }
            return $answer;
        });

        return $question;
    }

    /**
     * Get command description question
     * @return Question
     */
    protected function _getCommandDescriptionQuestion()
    {
        $question = new Question('<question>Command description</question>');
        $question->setValidator(function ($answer) {
            if ($answer === null) {
                throw new RuntimeException('Please give a command description');
            }
            return $answer;
        });

        return $question;
    }
}
