<?php

namespace Ekyna\Bundle\AdminBundle\Install;

use Ekyna\Bundle\InstallBundle\Install\AbstractInstaller;
use Ekyna\Bundle\InstallBundle\Install\OrderedInstallerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class AdminInstaller
 * @package Ekyna\Bundle\AdminBundle\Install
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminInstaller extends AbstractInstaller implements OrderedInstallerInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Default groups
     */
    protected $groups = [
        'Super administrateur' => ['ROLE_SUPER_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'],
        'Administrateur'       => ['ROLE_ADMIN'],
        'Modérateur'           => ['ROLE_ADMIN'],
    ];


    /**
     * {@inheritdoc}
     */
    public function install(Command $command, InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>[Admin] Creating users groups:</info>');
        $this->createGroups($output);
        $output->writeln('');
    }

    /**
     * Creates the default groups.
     *
     * @param OutputInterface $output
     */
    private function createGroups(OutputInterface $output)
    {
        $em = $this->container->get('ekyna_admin.group.manager');
        $repository = $this->container->get('ekyna_admin.group.repository');

        foreach ($this->groups as $name => $roles) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            /** @var \Ekyna\Bundle\AdminBundle\Model\GroupInterface $group */
            if (null !== $group = $repository->findOneBy(['name' => $name])) {
                $output->writeln('<comment>exists</comment>');

                continue;
            }

            /** @var \Ekyna\Bundle\AdminBundle\Model\GroupInterface $group */
            $group = $repository->createNew();
            $group
                ->setName($name)
                ->setRoles($roles);

            $em->persist($group);

            $output->writeln('<info>created</info>');
        }

        $em->flush();
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'ekyna_admin';
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return -1024;
    }
}
