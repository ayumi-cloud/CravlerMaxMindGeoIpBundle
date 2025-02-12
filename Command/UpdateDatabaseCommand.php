<?php

namespace Cravler\MaxMindGeoIpBundle\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Cravler\MaxMindGeoIpBundle\DependencyInjection\CravlerMaxMindGeoIpExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class UpdateDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('cravler:maxmind:geoip-update')
            ->setDescription('Downloads and updates the MaxMind GeoIp2 database')
            ->addOption('no-md5-check', null, InputOption::VALUE_NONE, 'Disable MD5 check')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->getParameter(CravlerMaxMindGeoIpExtension::CONFIG_KEY);

        foreach ($config['source'] as $key => $source) {
            if (!$source) {
                continue;
            }

            $output->writeln('');
            $output->write(sprintf('Downloading %s... ', $source));

            $tmpFile = $this->downloadFile($source);
            if (false === $tmpFile) {
                $output->writeln('FAILED');
                $output->writeln(sprintf('<error>Error during file download occurred on %s</error>', $source));
                continue;
            }

            $output->writeln('<info>Done</info>');
            $output->write('Unzipping the downloaded data... ');
            $tmpFileUnzipped = dirname($tmpFile).DIRECTORY_SEPARATOR.$config['db'][$key];

            $success = $this->decompressFile($tmpFile, $tmpFileUnzipped);

            if (!$input->getOption('no-md5-check')) {
                if (strpos($tmpFile, '.tar.gz') !== false) {
                    $calculatedMD5 = md5_file($tmpFile);
                } else {
                    $calculatedMD5 = md5_file($tmpFileUnzipped);
                }
            }

            unlink($tmpFile);

            if ($success) {
                $output->writeln('<info>Done</info>');
            } else {
                $output->writeln(sprintf('<error>An error occured when decompressing %s</error>', basename($tmpFile)));
                continue;
            }

            # MD5 check
            if (!$input->getOption('no-md5-check')) {
                $output->write('Checking file hash... ');
                if ($config['md5_check'][$key]) {
                    $expectedMD5 = file_get_contents($config['md5_check'][$key]);

                    if (!$expectedMD5 || strlen($expectedMD5) !== 32) {
                        unlink($tmpFileUnzipped);
                        $output->writeln(sprintf('<error>Unable to check MD5 for %s</error>', $source));
                        continue;
                    } elseif ($expectedMD5 !== $calculatedMD5) {
                        unlink($tmpFileUnzipped);
                        $output->writeln(sprintf('<error>MD5 for %s does not match</error>', $source));
                        continue;
                    } else {
                        $output->writeln('<info>File hash OK.</info>');
                    }
                } else {
                    $output->writeln('<comment>Skipped</comment>');
                }
            }

            if (!file_exists($config['path'])) {
                mkdir($config['path'], 0777, true);
            }

            $outputFilePath = $config['path'].DIRECTORY_SEPARATOR.$config['db'][$key];
            chmod(dirname($outputFilePath), 0777);
            $success = @rename($tmpFileUnzipped, $outputFilePath);

            if ($success) {
                $output->writeln(sprintf('<info>Update completed for %s.</info>', $key));
            } else {
                $output->writeln(sprintf('<error>Unable to update %s</error>', $key));
            }
        }
        $output->writeln('');
    }

    /**
     * @param string $source
     *
     * @return bool|string
     */
    private function downloadFile($source)
    {
        $tmpFile = tempnam(sys_get_temp_dir(), 'maxmind_geoip2_');
        if (strpos($source, 'tar.gz') !== false) {
            @rename($tmpFile, $tmpFile.'.tar.gz');
            $tmpFile .= '.tar.gz';
        }

        if (!@copy($source, $tmpFile)) {
            return false;
        }

        return $tmpFile;
    }

    /**
     * @param $fileName
     * @param $outputFilePath
     *
     * @return bool
     */
    private function decompressFile($fileName, $outputFilePath)
    {
        if (strpos($fileName, '.tar.gz') !== false) {
            $tmpDir = tempnam(sys_get_temp_dir(), 'MaxMind_');
            unlink($tmpDir);
            mkdir($tmpDir);

            $p = new \PharData($fileName);
            $p->decompress();

            $tarFileName = str_replace('.gz', '', $fileName);
            $phar = new \PharData($tarFileName);
            $phar->extractTo($tmpDir);
            unlink($tarFileName);

            $files = glob($tmpDir.DIRECTORY_SEPARATOR.'*'.DIRECTORY_SEPARATOR.'*.mmdb');
            if (count($files)) {
                @rename($files[0], $outputFilePath);
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($tmpDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                $todo($fileinfo->getRealPath());
            }
            rmdir($tmpDir);
        } else {
            $gz = gzopen($fileName, 'rb');
            $outputFile = fopen($outputFilePath, 'wb');
            while (!gzeof($gz)) {
                fwrite($outputFile, gzread($gz, 4096));
            }
            fclose($outputFile);
            gzclose($gz);
        }

        return is_readable($outputFilePath);
    }
}
