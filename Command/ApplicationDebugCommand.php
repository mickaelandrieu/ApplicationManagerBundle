<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\FrameworkBundle\Command;

use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * A console command to display kernel information.
 *
 * @author Roland Franssen <franssen.roland@gmail.com>
 *
 * @todo: reuse application reporter service instead.
 */
class ApplicationDebugCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('debug:app')
            ->setDescription('Displays application information')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /** @var $kernel KernelInterface */
        $kernel = $this->getContainer()->get('kernel');
        $baseDir = realpath($kernel->getRootDir().DIRECTORY_SEPARATOR.'..');
        $bundles = array_map(function ($bundle) use ($baseDir) {
            return '* '.$bundle->getName().' (<comment>'.$this->formatPath($bundle->getPath(), $baseDir).'</comment>)';
        }, $kernel->getBundles());
        sort($bundles);

        $io->title('Welcome to Symfony '.Kernel::VERSION);
        $io->success(array(
            'Your application is now ready. You can start working on it at:',
            $baseDir,
        ));
        $io->table(array(), array(
            array('Kernel', get_class($kernel)),
            array('Name', $kernel->getName()),
            array('Version', Kernel::VERSION),
            array('Version ID', Kernel::VERSION_ID),
            array('End of maintenance', Kernel::END_OF_MAINTENANCE.($this->isExpired(Kernel::END_OF_MAINTENANCE) ? ' <error>Expired</error>' : '')),
            array('End of life', Kernel::END_OF_LIFE.($this->isExpired(Kernel::END_OF_LIFE) ? ' <error>Expired</error>' : '')),
            new TableSeparator(),
            array('Environment', $kernel->getEnvironment()),
            array('Debug', $kernel->isDebug() ? 'true' : 'false'),
            array('Charset', $kernel->getCharset()),
            array('Timezone', date_default_timezone_get().' (<comment>'.(new \DateTime())->format(\DateTime::W3C).'</comment>)'),
            new TableSeparator(),
            array('Root directory', $this->formatPath($kernel->getRootDir(), $baseDir)),
            array('Cache directory', $this->formatPath($kernel->getCacheDir(), $baseDir).' (<comment>'.$this->formatFilesize($kernel->getCacheDir()).'</comment>)'),
            array('Log directory', $this->formatPath($kernel->getLogDir(), $baseDir).' (<comment>'.$this->formatFilesize($kernel->getLogDir()).'</comment>)'),
            new TableSeparator(),
            array('Bundles (<comment>'.count($bundles).'</comment>)', implode(PHP_EOL, $bundles)),
        ));
    }

    private function formatPath($path, $baseDir = null)
    {
        return null !== $baseDir ? preg_replace('~^'.preg_quote($baseDir, '~').'~', '.', $path) : $path;
    }

    private function formatFilesize($path)
    {
        if (is_file($path)) {
            $size = filesize($path) ?: 0;
        } else {
            $size = 0;
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS | \RecursiveDirectoryIterator::FOLLOW_SYMLINKS)) as $file) {
                $size += $file->getSize();
            }
        }

        static $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i = 0; $size >= 1024 && isset($units[$i]); $size /= 1024, ++$i);

        return number_format($size, 2).$units[$i];
    }

    private function isExpired($date)
    {
        $date = \DateTime::createFromFormat('m/Y', $date);

        return false !== $date && new \DateTime() > $date->modify('last day of this month 23:59:59');
    }
}
