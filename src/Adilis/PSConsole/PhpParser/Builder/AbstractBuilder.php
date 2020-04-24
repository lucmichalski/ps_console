<?php

namespace Adilis\PSConsole\PhpParser\Builder;

use Adilis\PSConsole\PhpParser\Printer\NicePrinter;
use PhpParser\Node;
use PhpParser\BuilderFactory;
use PhpParser\NodeDumper;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

abstract class AbstractBuilder {
    protected $_builder;
    protected $_printer;

    public function __construct() {
        $this->_builder = new BuilderFactory;
        $this->_printer = new NicePrinter();
        return $this;
    }

    abstract public function getFilePath();

    abstract protected function buildNodes();

    public function getContent() {
        $nodes = $this->buildNodes();
        /*$code = '<?php  ?>';
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $ast = $parser->parse($code);

        $dumper = new NodeDumper();
        echo $dumper->dump($ast);
        die;*/

        if (!is_array($nodes)) {
            $nodes = [$nodes];
        }
        $nodes = array_values(array_filter($nodes, function ($node) {
            return $node !== null;
        }));

        return $this->_printer->prettyPrintFile($nodes);
    }
}
