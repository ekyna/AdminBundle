<?php

namespace Ekyna\Bundle\AdminBundle\Install;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\InstallBundle\Install\AbstractInstaller;
use Ekyna\Bundle\InstallBundle\Install\OrderedInstallerInterface;
use Ekyna\Bundle\UserBundle\Command\UserInputInteract;
use Ekyna\Bundle\UserBundle\Model\GroupInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminInstaller
 * @package Ekyna\Bundle\AdminBundle\Install
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminInstaller extends AbstractInstaller implements OrderedInstallerInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var EntityRepository
     */
    private $userRepository;

    /**
     * @var GroupInterface
     */
    private $superAdminGroup;

    /**
     * @var InputInterface
     */
    private $userDataInput;

    /**
     * Sets the container.
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Default groups :
     * [name => [[roles], permission, default]]
     */
    protected $defaultGroups = [
        'Super administrateur' => [['ROLE_SUPER_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'], 'MASTER', false],
        'Administrateur'       => [['ROLE_ADMIN'], 'OPERATOR', false],
        'Modérateur'           => [['ROLE_ADMIN'], 'EDIT', false],
        'Utilisateur'          => [[], 'VIEW', true],
    ];

    /**
     * {@inheritdoc}
     */
    public function initialize(Command $command, InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('no-interaction')) {
            return;
        }

        $groupRepository = $this->container->get('ekyna_user.group.repository');
        /** @var \Ekyna\Bundle\UserBundle\Model\GroupInterface $group */
        if (null === $this->superAdminGroup = $groupRepository->findOneBy(['name' => array_keys($this->defaultGroups)[0]])) {
            $output->writeln('Super admin group not found, aborting.');
            return;
        }

        $this->userRepository = $this->container->get('ekyna_user.user.repository');
        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface[] $superAdmins */
        $superAdmins = $this->userRepository->findBy(['group' => $this->superAdminGroup]);
        if (0 < count($superAdmins)) {
            $emails = [];
            foreach ($superAdmins as $superAdmin) {
                $emails[] = $superAdmin->getEmail();
            }
            $output->writeln(sprintf('Super admin already exists (<comment>%s</comment>).', implode(', ', $emails)));
            return;
        }

        $definition = new InputDefinition(array(
            new InputArgument('email'),
            new InputArgument('password'),
            new InputArgument('firstName'),
            new InputArgument('lastName'),
        ));

        $this->userDataInput = new ArrayInput([], $definition);
    }

    /**
     * {@inheritdoc}
     */
    public function interact(Command $command, InputInterface $input, OutputInterface $output)
    {
        if (null !== $this->userDataInput) {
            $output->writeln('<question>Please provide super admin initial information ...</question>');

            /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
            $helper = $command->getHelperSet()->get('question');

            $userInput = new UserInputInteract($this->userRepository);
            $userInput->interact($this->userDataInput, $output, $helper);

            $output->writeln('');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function install(Command $command, InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>[Admin] Creating users groups:</info>');
        $this->createGroups($output);
        $output->writeln('');

        $output->writeln('<info>[Admin] Generating Acl rules:</info>');
        $this->createAclRules($output);
        $output->writeln('');

        if (null !== $this->userDataInput) {
            $output->writeln('<info>[Admin] Creating Super Admin:</info>');
            $this->createSuperAdmin($command, $this->userDataInput, $output);
            $output->writeln('');
        }
    }

    /**
     * Creates default groups entities
     *
     * @param OutputInterface $output
     */
    private function createGroups(OutputInterface $output)
    {
        $em = $this->container->get('ekyna_user.group.manager');
        $repository = $this->container->get('ekyna_user.group.repository');

        foreach ($this->defaultGroups as $name => $options) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));
            if (null !== $group = $repository->findOneBy(['name' => $name])) {
                $output->writeln('already exists.');
                continue;
            }
            /** @var \Ekyna\Bundle\UserBundle\Model\GroupInterface $group */
            $group = $repository->createNew();
            $group
                ->setDefault($options[2])
                ->setName($name)
                ->setRoles($options[0])
            ;
            $em->persist($group);
            $output->writeln('created.');
        }
        $em->flush();
    }

    /**
     * Creates default groups permissions for each admin pool configurations (if available)
     *
     * @param OutputInterface $output
     */
    private function createAclRules(OutputInterface $output)
    {
        $registry = $this->container->get('ekyna_resource.configuration_registry');
        $aclOperator = $this->container->get('ekyna_admin.acl_operator');
        $groups = $this->container->get('ekyna_user.group.repository')->findAll();

        // TODO disallow on ekyna_user.group for non super admin.

        /** @var \Ekyna\Bundle\UserBundle\Entity\Group $group */
        foreach ($groups as $group) {
            if (isset($this->defaultGroups[$group->getName()])) {
                $permission = $this->defaultGroups[$group->getName()][1];
            } else {
                continue;
            }
            $data = [];
            foreach ($registry->getConfigurations() as $id => $config) {
                $data[$id] = [$permission => true];
            }
            $aclOperator->updateGroup($group, $data);
        }

        $output->writeln('Acl rules have been successfully generated.');
    }

    /**
     * Creates the super admin user.
     *
     * @param Command $command
     * @param OutputInterface $output
     */
    private function createSuperAdmin(Command $command, InputInterface $input, OutputInterface $output)
    {
        $userManager = $this->container->get('fos_user.user_manager');

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $userManager->createUser();
        $user
            ->setGroup($this->superAdminGroup)
            //->setGender('mr')
            //->setFirstName($input->getArgument('firstName'))
            //->setLastName($input->getArgument('lastName'))
            ->setPlainPassword($input->getArgument('password'))
            ->setEmail($input->getArgument('email'))
            ->setEnabled(true);

        $userManager->updateUser($user);

        $output->writeln('Super Admin has been successfully created.');
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return -1024;
    }
}
