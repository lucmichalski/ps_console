<?php

namespace Adilis\PSConsole\Template\Builder;

class CommandTemplateBuilder extends AbstractTemplateBuilder {
    protected $_command;
    protected $_description;
    protected $_className;
    protected $_domains;
    protected $_domainNamespace;

    public function __construct(string $command, string $description) {
        $this->_command = $command;
        $this->_description = $description;

        if (strpos($this->_command, ':') !== false) {
            $this->_domains = explode(':', $this->_command);
            $this->_domains = array_map('strtolower', $this->_domains);
            $this->_domains = array_map('ucfirst', $this->_domains);
            $this->_className = implode('', $this->_domains);
            array_pop($this->_domains);
            $this->_domainNamespace = '\\' . implode('\\', $this->_domains);
        } else {
            $this->_className = ucfirst($this->_command);
        }
    }

    protected function getFilePath() {
        $domainPath = str_replace('\\', DIRECTORY_SEPARATOR, $this->_domainNamespace);
        $fileName = $this->_className . 'Command.php';
        return __DIR__ . '/../../PSConsole/Command' . $domainPath . DIRECTORY_SEPARATOR . $fileName;
    }

    protected function getTemplateVars() {
        return [
            '{domain_namespace}' => $this->_domainNamespace,
            '{class_name}' => $this->_className,
            '{command}' => $this->_command,
            '{description}' => $this->_description,
        ];
    }

    protected function getTemplate() {
        return
"<?php

namespace Adilis\PSConsole\Command{domain_namespace};

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class {class_name}Command extends Command {

    protected function configure() {
        \$this
            ->setName('{command}')
            ->setDescription('{description}')
        ;
    }

    public function execute(InputInterface \$input, OutputInterface \$output) {

    }
    
}";
    }
}
