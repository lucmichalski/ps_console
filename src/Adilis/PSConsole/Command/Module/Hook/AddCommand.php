<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module\Hook;

use Adilis\PSConsole\Command\Module\ModuleAbstract;
use Adilis\PSConsole\PhpParser\Node\Visitor\ModuleAddHookNodeVisitor;
use Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Tools;

/**
 * Class Hook
 * Command sample description
 */
class AddCommand extends ModuleAbstract
{
    protected function configure()
    {
        $this
            ->setName('module:hook:add')
            ->setDescription('Add hook to module')
            ->addModuleNameArgument()
            ->addHookListArgument();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_helper = $this->getHelper('question');
        $this->_finder = new Finder();
        $this->_filesystem = new Filesystem();

        $module_content = Tools::file_get_contents($this->_moduleFilePath);
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $stmts = $parser->parse($module_content);
        $traverser = new NodeTraverser();

        foreach ($this->_hookList as $hook) {
            try {
                $method_visitor = new ModuleAddHookNodeVisitor($this->_moduleName, $hook);
                $traverser->addVisitor($method_visitor);
                $stmts = $traverser->traverse($stmts);
            } catch (Error $e) {
                echo 'Parse Error: ', $e->getMessage();
            }

            $prettyPrinter = new Standard();
            $this->_filesystem->dumpFile($this->_moduleFilePath, $prettyPrinter->prettyPrintFile($stmts));
        }
        $this->getApplication()->find('module:hook:register')->run(
            new ArrayInput(['moduleName'  => $this->_moduleName, 'hooksList'  => $this->_hookList]),
            $output
        );
    }
}
