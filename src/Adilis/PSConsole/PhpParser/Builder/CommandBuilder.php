<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use PhpParser\BuilderFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Node;

class CommandBuilder {
    protected $_name;
    protected $_description;
    protected $_class;
    protected $_domains;
    protected $_domainNamespace;

    public function __construct($name, $description) {
        $this->_name = $name;
        $this->_description = $description;
        $this->_builder = new BuilderFactory;
        $this->_printer = new Standard();

        if (strpos($this->_name, ':') !== false) {
            $this->_domains = explode(':', $this->_name);
            $this->_domains = array_map('strtolower', $this->_domains);
            $this->_domains = array_map('ucfirst', $this->_domains);
            $this->_class = implode('', $this->_domains);
            array_pop($this->_domains);
            $this->_domainNamespace = '\\' . implode('\\', $this->_domains);
        } else {
            $this->_class = ucfirst($this->_name);
        }
    }

    public function getFilePath() {
        $domainPath = str_replace('\\', DIRECTORY_SEPARATOR, $this->_domainNamespace);
        $fileName = $this->_class . 'Command.php';
        return _PS_MODULE_DIR_ . 'ps_console/src/Adilis/PSConsole/Command' . $domainPath . DIRECTORY_SEPARATOR . $fileName;
    }

    public function getContent() {
        return $this->_printer->prettyPrintFile([$this->buildNodes()]);
    }

    private function buildNodes() {
        return
            $this->_builder->namespace('Adilis\PSConsole\Command' . $this->_domainNamespace)
            ->addStmt($this->_builder->use('Symfony\Component\Console\Command\Command'))
            ->addStmt($this->_builder->use('Symfony\Component\Console\Input\InputInterface'))
            ->addStmt($this->_builder->use('Symfony\Component\Console\Output\OutputInterface'))
            ->addStmt(
                $this->_builder->class($this->_class . 'Command')
                    ->extend('Command')
                    ->addStmt(
                        $this->_builder->method('configure')
                            ->makeProtected()
                            ->addStmts([
                                new Node\Expr\MethodCall(
                                    new Node\Expr\Variable('this'),
                                    'setName',
                                    [new Node\Scalar\String_($this->_name)]
                                ),
                                new Node\Expr\MethodCall(
                                    new Node\Expr\Variable('this'),
                                    'setDescription',
                                    [new Node\Scalar\String_($this->_description)]
                                )
                            ])
                    )
                    ->addStmt(
                        $this->_builder->method('execute')
                            ->makePublic()
                            ->addParam($this->_builder->param('input')->setTypeHint('InputInterface'))
                            ->addParam($this->_builder->param('output')->setTypeHint('OutputInterface'))
                            ->addStmts([
                                new Node\Expr\MethodCall(
                                    new Node\Expr\Variable('output'),
                                    'writeln',
                                    [new Node\Scalar\String_('it works')]
                                )
                            ])
                    )
            )
            ->getNode();
    }
}
