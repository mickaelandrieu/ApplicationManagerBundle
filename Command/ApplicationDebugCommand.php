<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Am\ApplicationManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Helper\Helper as ConsoleHelper;

use Am\ApplicationManagerBundle\Utils\ApplicationReporter;

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

        /** @var $applicationReporter ApplicationReporter */
        $applicationReporter = $this->getContainer()->get('am_application_reporter');
        $report = $applicationReporter->report();

        $io->title('Welcome to Symfony Application reporter');
        $io->success(array(
            'Application available at: ' . $applicationReporter->getBaseDir(),
        ));

        $io->table(array(), array(
            array('Kernel', $report['class']),
            array('Name', $report['name']),
            array('Version', $report['version']),
            array('End of maintenance', $report['eom'] .($report['eom_expired'] ? ' <error>Expired</error>' : '')),
            array('End of life', $report['eol'] .($report['eol_expired'] ? ' <error>Expired</error>' : '')),
            new TableSeparator(),
            array('Environment', $report['env']),
            array('Debug', $report['debug'] ? 'true' : 'false'),
            array('Charset', $report['charset']),
            array('Timezone', $report['timezone'].' (<comment>'.(new \DateTime())->format(\DateTime::W3C).'</comment>)'),
            new TableSeparator(),
            array('Root directory', $report['root_dir']),
            array('Cache directory', $report['cache_dir'].' (<comment>'.ConsoleHelper::formatMemory($report['cache_dir']).'</comment>)'),
            array('Log directory', $report['log_dir'].' (<comment>'.ConsoleHelper::formatMemory($report['log_dir']).'</comment>)'),
            new TableSeparator(),
            array('Bundles (<comment>'.count($report['bundles']).'</comment>)', $this->getBundlesOutput($report['bundles'])),
        ));
    }

    private function getBundlesOutput($bundles)
    {
        $output = '';

        foreach ($bundles as $bundle) {
            $output .= $bundle['name'] . ' (<comment>' . $bundle['path'] . '</comment>)' . PHP_EOL;
        }

        return $output;
    }
}
