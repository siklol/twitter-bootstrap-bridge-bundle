<?php
namespace SikIndustries\TwitterBootstrapBridgeBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BootstrapInstallerCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('sik-industries:bootstrap:install')
            ->setDescription('Installs the latest compile twitter bootstrap version')
            ->addOption(
                'bootstrap-source-url',
                null,
                InputOption::VALUE_OPTIONAL,
                'The compiled zip file for twitter bootstrap',
                'http://twitter.github.com/bootstrap/assets/bootstrap.zip'
            )
            ->addOption(
                'download-dir',
                null,
                InputOption::VALUE_OPTIONAL,
                'The download folder where everything will be extracted to',
                dirname(dirname(__FILE__)).'/Resources/public/'
            )
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('CURL is not enabled! Aborting');
        }
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $input->getOption('bootstrap-source-url'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data = curl_exec($ch);
        curl_close($ch);

        if (!is_dir($input->getOption('download-dir'))) {
            mkdir($input->getOption('download-dir'), 0777, true);
        }

        $zipFile = $input->getOption('download-dir').'/bootstrap.zip';
        @file_put_contents($zipFile, $data);

        if (!is_file($zipFile)) {
            throw new \Exception(sprintf('Could not write %s to disk!', $zipFile));
        }
        $zip = new \ZipArchive;
        $zip->open($zipFile);
        $extracted = $zip->extractTo($input->getOption('download-dir'));
        $zip->close();

        $message = 'Done. Twitter Bootstrap can now be used!';
        if (!$extracted) {
            $message = "<error>Bootstrap could not be extracted!</error>";
        }

        $output->writeln($message);
    }
}