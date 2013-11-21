<?php

namespace M6Web\GithubEnterpriseArchiveBundle\Command;

use M6Web\StatHatBundle\StatHat;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ArchiveCommand
 *
 * @author Adrien Samson <asamson.externe@m6.fr>
 */
class ArchiveCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('archive')
            ->setDescription('Archive GithubEnterprise timeline');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $downloaded = $this->getContainer()->get('m6_web_github_enterprise_archive.dowloader')->download();

        $output->writeln(sprintf('Downloaded %d items', $downloaded));
    }
}
