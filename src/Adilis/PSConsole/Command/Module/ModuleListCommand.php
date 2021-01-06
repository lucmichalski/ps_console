<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Module;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use Module;

/**
 * Commande qui permet de récupérer la liste des modules installé
 *
 */
class ModuleListCommand extends Command {
    protected function configure() {
        $this
            ->setName('module:list')
            ->setDescription('Get modules list')
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NONE,
                'JSON format'
            )
            ->addOption(
                'active',
                null,
                InputOption::VALUE_NONE,
                'List only active modules'
            )
            ->addOption(
                'no-active',
                null,
                InputOption::VALUE_NONE,
                'List only not active modules'
            )
            ->addOption(
                'installed',
                null,
                InputOption::VALUE_NONE,
                'List only installed modules'
            )
            ->addOption(
                'no-installed',
                null,
                InputOption::VALUE_NONE,
                'List only not installed modules'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $modules = Module::getModulesOnDisk();
        //module stdClass definition
        /*
            [id] => 36
            [warning] =>
            [name] => gridhtml
            [displayName] => Module display name
            [version] => 1.3.0
            [description] => Module description
            [author] => PrestaShop
            [tab] => administration
            [is_configurable] => 0
            [need_instance] => 0
            [limited_countries] =>
            [author_uri] =>
            [active] => 1
            [onclick_option] =>
            [trusted] => 1
            [installed] => 1
            [database_version] => 1.3.0
            [interest] =>
            [enable_device] => 7
         */

        //sort by module name
        usort($modules, [$this, 'cmp']);
        // apply filters
        if ($input->getOption('active')) {
            $modules = array_filter($modules, function ($module) {
                return (bool) ($module->active);
            });
        }
        if ($input->getOption('no-active')) {
            $modules = array_filter($modules, function ($module) {
                return !($module->active);
            });
        }
        if ($input->getOption('installed')) {
            $modules = array_filter($modules, function ($module) {
                return (bool) ($module->installed);
            });
        }
        if ($input->getOption('no-installed')) {
            $modules = array_filter($modules, function ($module) {
                return !($module->installed);
            });
        }

        if $input->getOption('json') {

            $jsonObj = array();
            // $table = new Table($output);        
            // $table->setHeaders(['Name', 'Version', 'Installed', 'Active']);
            foreach ($modules as $module) {
                $modObj = new stdClass();
                $modObj->name = $module->name;
                $modObj->version = $module->version;
                $modObj->installed = ((bool) ($module->installed) ? 'true' : 'false');
                $modObj->active = ((bool) ($module->active) ? 'true' : 'false');
                $jsonObj[] = $modObj;
            }

            $jsonContent = $serializer->serialize($jsonObj, 'json');
            $output->writeln($jsonContent); // or return it in a Response

        } else {
            $output->writeln('<info>Currently module on disk:</info>');
            $nr = 0;
            $table = new Table($output);        
            $table->setHeaders(['Name', 'Version', 'Installed', 'Active']);
            foreach ($modules as $module) {
                $table->addRow([
                    $module->name,
                    $module->version,
                    ((bool) ($module->installed) ? 'true' : 'false'),
                    ((bool) ($module->active) ? 'true' : 'false')
                ]);
                $nr++;
            }
            $table->render();
            $output->writeln("<info>Total modules on disk: $nr</info>");
        }

    }

    private function cmp($a, $b) {
        return strcmp($a->name, $b->name);
    }
}
