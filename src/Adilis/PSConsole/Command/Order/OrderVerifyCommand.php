<?php

namespace Adilis\PSConsole\Command\Order;

use Order;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Validate;

class OrderVerifyCommand extends Command {
    protected function configure() {
        $this
            ->setName('order:verify')
            ->addArgument('idOrder', InputArgument::REQUIRED, 'Id order')
            ->setDescription('Verify amounts in order');
    }

    public function execute(InputInterface $input, OutputInterface $output) {
        $idOrder = (int)$input->getArgument('idOrder');
        $order = new Order($idOrder);

        if (!Validate::isLoadedObject($order)) {
            $output->writeLn('<error>Order does not exists ');
            return;
        }
    }
}
