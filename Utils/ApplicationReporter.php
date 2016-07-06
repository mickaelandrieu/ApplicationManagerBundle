<?php

namespace Am\ApplicationManagerBundle\Utils;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Displays useful information about Symfony applications
 *
 * Highly inspired from https://github.com/symfony/symfony/pull/19278
 */
class ApplicationReporter
{
    /**
     * @var string $baseDir
     */
    private $baseDir;

    /**
     * @var KernelInterface $kernel
     */
    private $kernel;

    /**
     * ApplicationReporter constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->baseDir = realpath($kernel->getRootDir().DIRECTORY_SEPARATOR.'..');
    }

    public function report()
    {
        return array(
            'class' => $this->getClass(),
            'name' => $this->getName(),
            'version' => $this->getVersion(),
            'eom' => $this->getEndOfMaintenance(),
            'eom_expired' => $this->isExpired($this->getEndOfMaintenance()),
            'eol' => $this->getEndOfLife(),
            'eol_expired' => $this->isExpired($this->getEndOfMaintenance()),
            'env' => $this->getEnvironment(),
            'debug' => $this->isDebug(),
            'charset' => $this->getCharset(),
            'timezone' => $this->getTimezone(),
            'root_dir' => $this->getRootDirectory(),
            'cache_dir' => $this->getCacheDirectory(),
            'log_dir' => $this->getLogDirectory(),
            'bundles' => $this->getBundles(),
        );
    }

    public function getClass()
    {
        return get_class($this->kernel);
    }

    public function getName()
    {
        return $this->kernel->getName();
    }

    public function getVersion()
    {
        return $this->kernel::VERSION;
    }

    public function getEndOfMaintenance()
    {
        return $this->kernel::END_OF_MAINTENANCE;
    }

    public function getEndOfLife()
    {
        return $this->kernel::END_OF_LIFE;
    }

    public function getEnvironment()
    {
        return $this->kernel->getEnvironment();
    }

    public function isDebug()
    {
        return $this->kernel->isDebug();
    }

    public function getCharset()
    {
        return $this->kernel->getCharset();
    }

    public function getTimezone()
    {
        return date_default_timezone_get();
    }

    public function getRootDirectory()
    {
        return $this->formatPath($this->kernel->getRootDir(), $this->baseDir);
    }

    public function getCacheDirectory()
    {
        return $this->formatPath($this->kernel->getCacheDir(), $this->baseDir);
    }

    public function getLogDirectory()
    {
        return $this->formatPath($this->kernel->getLogDir(), $this->baseDir);
    }

    public function getBundles()
    {
        $bundlesWithPaths = array();
        $bundles = $this->kernel->getBundles();
        sort($bundles);

        foreach($bundles as $bundle) {
            $bundlesWithPaths[] = array(
                'name' => $bundle->getName(),
                'path' => $this->formatPath($bundle->getPath(), $this->baseDir),
            );
        }

        return $bundlesWithPaths;
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
