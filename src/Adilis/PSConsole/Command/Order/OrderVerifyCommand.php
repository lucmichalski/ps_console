<?php

namespace Adilis\PSConsole\Command\Order;

use Order;
use OrderInvoice;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Validate;

class OrderVerifyCommand extends Command {
    protected $_table;

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

        $orderInvoices = [];
        $totalCartRulesTaxIncl = $totalCartRulesTaxExcl = 0;

        foreach ($order->getCartRules() as $cartRule) {
            if (!array_key_exists($cartRule['id_order_invoice'], $orderInvoices)) {
                $orderInvoices[$cartRule['id_order_invoice']] = [
                    'totalProductsTaxExcl' => 0,
                    'totalProductsTaxIncl' => 0,
                    'totalCartRulesTaxExcl' => 0,
                    'totalCartRulesTaxIncl' => 0,
                ];
            }

            $orderInvoices[$cartRule['id_order_invoice']]['totalCartRulesTaxExcl'] += (float)$cartRule['value_tax_excl'];
            $orderInvoices[$cartRule['id_order_invoice']]['totalCartRulesTaxIncl'] += (float)$cartRule['value'];

            $totalCartRulesTaxIncl += (float)$cartRule['value'];
            $totalCartRulesTaxExcl += (float)$cartRule['value_tax_excl'];
        }

        $totalProductsTaxExcl = $totalProductsTaxIncl = 0;
        foreach ($order->getProducts() as $product) {
            if (!array_key_exists($product['id_order_invoice'], $orderInvoices)) {
                $orderInvoices[$product['id_order_invoice']] = [
                    'totalProductsTaxExcl' => 0,
                    'totalProductsTaxIncl' => 0,
                    'totalCartRulesTaxExcl' => 0,
                    'totalCartRulesTaxIncl' => 0,
                ];
            }
            $orderInvoices[$product['id_order_invoice']]['totalProductsTaxExcl'] += (float)$product['total_price_tax_excl'];
            $orderInvoices[$product['id_order_invoice']]['totalProductsTaxIncl'] += (float)$product['total_price_tax_incl'];

            $totalProductsTaxExcl += (float)$product['total_price_tax_excl'];
            $totalProductsTaxIncl += (float)$product['total_price_tax_incl'];
        }

        $shippingTaxExcl = $order->total_shipping_tax_excl;
        $shippingTaxIncl = $shippingTaxExcl * (1 + $order->carrier_tax_rate / 100);
        $totalPaidTaxExcl = $totalProductsTaxExcl + $shippingTaxExcl - $totalCartRulesTaxExcl;
        $totalPaidTaxIncl = $totalProductsTaxIncl + $shippingTaxIncl - $totalCartRulesTaxIncl;

        $this->_table = new Table($output);
        $this->_table->setHeaders(['Data verified', 'Status', 'Database amount', 'Calculated amount']);

        $this->verify('Product amount tax excluded', $order->total_products, $totalProductsTaxExcl);
        $this->verify('Product amount tax included', $order->total_products_wt, $totalProductsTaxIncl);
        $this->verify('Discount amount tax excluded', $order->total_discounts_tax_excl, $totalCartRulesTaxExcl);
        $this->verify('Discount amount tax included', $order->total_discounts_tax_incl, $totalCartRulesTaxIncl);
        if ($order->total_discounts !== $order->total_discounts_tax_incl) {
            $this->verify('Discount amount #2 tax included', $order->total_discounts, $totalCartRulesTaxIncl);
        }
        $this->verify('Shipping amount tax excluded', $order->total_shipping_tax_excl, $shippingTaxExcl);
        $this->verify('Shipping amount tax included', $order->total_shipping_tax_incl, $shippingTaxIncl);
        if ($order->total_shipping != $order->total_shipping_tax_incl) {
            $this->verify('Shipping amount #2 tax included', $order->total_shipping, $shippingTaxIncl);
        }
        $this->verify('Order amount tax excluded', $order->total_paid_tax_excl, $totalPaidTaxExcl);
        $this->verify('Order amount tax included', $order->total_paid_tax_incl, $totalPaidTaxIncl);
        if ($order->total_paid != $order->total_paid_tax_incl) {
            $this->verify('Order amount #2 tax included', $order->total_paid, $totalPaidTaxIncl);
        }

        foreach ($orderInvoices as $idInvoice => $amounts) {
            $orderInvoice = new OrderInvoice((int)$idInvoice);
            if (Validate::isLoadedObject($orderInvoice)) {
                $this->_table->addRows([
                    new TableSeparator,
                    [new TableCell('<comment>Order invoice ' . $orderInvoice->number . '</comment>', ['colspan' => 4])],
                    new TableSeparator
                ]);

                $shippingTaxExcl = $orderInvoice->total_shipping_tax_excl;
                $shippingTaxIncl = $shippingTaxExcl * (1 + $order->carrier_tax_rate / 100);
                $totalPaidTaxExcl = $amounts['totalProductsTaxExcl'] + $shippingTaxExcl - $amounts['totalCartRulesTaxExcl'];
                $totalPaidTaxIncl = $amounts['totalProductsTaxIncl'] + $shippingTaxIncl - $amounts['totalCartRulesTaxIncl'];

                $this->verify('Product amount tax excluded', $orderInvoice->total_products, $amounts['totalProductsTaxExcl']);
                $this->verify('Product amount tax included', $orderInvoice->total_products_wt, $amounts['totalProductsTaxIncl']);
                $this->verify('Discount amount tax excluded', $orderInvoice->total_discount_tax_excl, $amounts['totalCartRulesTaxExcl']);
                $this->verify('Discount amount tax included', $orderInvoice->total_discount_tax_incl, $amounts['totalCartRulesTaxIncl']);
                $this->verify('Shipping amount tax excluded', $order->total_shipping_tax_excl, $shippingTaxExcl);
                $this->verify('Shipping amount tax included', $order->total_shipping_tax_incl, $shippingTaxIncl);
                if ($order->total_shipping != $order->total_shipping_tax_incl) {
                    $this->verify('Shipping amount #2 tax included', $order->total_shipping, $shippingTaxIncl);
                }
                $this->verify('Invoice amount tax excluded', $orderInvoice->total_paid_tax_excl, $totalPaidTaxExcl);
                $this->verify('Invoice amount tax included', $orderInvoice->total_paid_tax_incl, $totalPaidTaxIncl);
            } else {
                $this->_table->addRows([
                    new TableSeparator,
                    [
                        '<comment>Order invoice ' . $orderInvoice->number . '</comment>',
                        new TableCell('<error>Invoice can not be loaded</error>', ['colspan' => 3])
                    ],
                ]);
            }
        }

        $this->_table->render();
    }

    private function verify($text, $value_in_db, $value_calculated) {
        $this->_table->addRow([
            $text,
            (float)$value_in_db === (float)$value_calculated ? '<info>OK</info>' : '<error>KO</error>',
            (float)$value_in_db,
            (float)$value_calculated
        ]);
    }
}
