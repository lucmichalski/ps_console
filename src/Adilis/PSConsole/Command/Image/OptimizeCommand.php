<?php

/**
 * @author    Adilis <contact@adilis.fr>
 * @copyright 2020 Adilis
 */

namespace Adilis\PSConsole\Command\Image;

use CURLFile;
use PrestaShop\PrestaShop\Core\Foundation\Filesystem\FileSystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class Optimize
 * Command sample description
 */
class OptimizeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('image:optimize')
            ->setDescription('Optimize images with https://resmush.it/');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->_helper = $this->getHelper('question');
        $this->_finder = new Finder();
        $this->_filesystem = new FileSystem();

        $images = $this->_finder->files()->in(_PS_IMG_DIR_ . 'p')->name('*.jpg')->name('*.png');
        foreach ($images as $image) {
            $absoluteFilePath = $image->getRealPath();
            $this->optimize($absoluteFilePath);
            dump($absoluteFilePath);
            die;
            // ...
        }
    }

    private function optimize($file)
    {
        $mime = mime_content_type($file);
        $info = pathinfo($file);
        $name = $info['basename'];
        $output = new CURLFile($file, $mime, $name);
        $data = array(
            "files" => $output,
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://api.resmush.it/?qlty=75');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = curl_error($ch);
        }
        curl_close($ch);
        $result = json_decode($result, true);
        var_dump($result);
    }
}
