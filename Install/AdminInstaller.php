<?php

namespace Ekyna\Bundle\AdminBundle\Install;

use Ekyna\Bundle\InstallBundle\Install\OrderedInstallerInterface;
use Ekyna\Bundle\UserBundle\Entity\Group;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AdminInstaller
 * @package Ekyna\Bundle\AdminBundle\Install
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminInstaller implements OrderedInstallerInterface
{
    /**
     * Default groups :
     * [name => [[roles], permission, default]]
     */
    protected $defaultGroups = array(
        'Super administrateur' => array(array('ROLE_SUPER_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'), 'MASTER', false),
        'Administrateur'       => array(array('ROLE_ADMIN'), 'OPERATOR', false),
        'Modérateur'           => array(array('ROLE_ADMIN'), 'EDIT', false),
        'Utilisateur'          => array(array(), 'VIEW', true),
    );

    /**
     * {@inheritdoc}
     */
    public function install(ContainerInterface $container, Command $command, InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>[Admin] Creating users groups:</info>');
        $this->createGroups($container, $output);
        $output->writeln('');

        $output->writeln('<info>[Admin] Generating Acl rules:</info>');
        $this->createAclRules($container, $output);
        $output->writeln('');

        if (!$input->getOption('no-interaction')) {
            $output->writeln('<info>[Admin] Creating Super Admin:</info>');
            $this->createSuperAdmin($container, $command, $output);
            $output->writeln('');
        }
    }

    /**
     * Creates default groups entities
     *
     * @param ContainerInterface $container
     * @param OutputInterface    $output
     */
    private function createGroups(ContainerInterface $container, OutputInterface $output)
    {
        $em = $container->get('ekyna_user.group.manager');
        $repository = $container->get('ekyna_user.group.repository');

        foreach ($this->defaultGroups as $name => $options) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));
            if (null !== $group = $repository->findOneBy(array('name' => $name))) {
                $output->writeln('already exists.');
                continue;
            }
            $group = new Group($name);
            $group
                ->setDefault($options[2])
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
     * @param ContainerInterface $container
     * @param OutputInterface    $output
     *
     * @return number
     */
    private function createAclRules(ContainerInterface $container, OutputInterface $output)
    {
        $registry = $container->get('ekyna_admin.pool_registry');
        $aclOperator = $container->get('ekyna_admin.acl_operator');
        $groups = $container->get('ekyna_user.group.repository')->findAll();

        // TODO disallow on ekyna_user.group for non super admin.

        /** @var \Ekyna\Bundle\UserBundle\Entity\Group $group */
        foreach($groups as $group) {
            if(isset($this->defaultGroups[$group->getName()])) {
                $permission = $this->defaultGroups[$group->getName()][1];
            }else{
                continue;
            }
            $datas = array();
            foreach($registry->getConfigurations() as $id => $config) {
                $datas[$id] = array($permission => true);
            }
            $aclOperator->updateGroup($group, $datas);
        }

        $output->writeln('Acl rules have been successfully generated.');
    }

    /**
     * Creates the super admin user.
     *
     * @param ContainerInterface $container
     * @param Command            $command
     * @param OutputInterface    $output
     */
    private function createSuperAdmin(ContainerInterface $container, Command $command, OutputInterface $output)
    {
        $groupRepository = $container->get('ekyna_user.group.repository');
        $userRepository = $container->get('ekyna_user.user.repository');

        /** @var \Ekyna\Bundle\UserBundle\Model\GroupInterface $group */
        if (null === $group = $groupRepository->findOneBy(array('name' => array_keys($this->defaultGroups)[0]))) {
            $output->writeln('Super admin group not found, aborting.');
            return;
        }

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $superAdmin */
        if (null !== $superAdmin = $userRepository->findOneBy(array('group' => $group))) {
            $output->writeln(sprintf('Super admin already exists (<comment>%s</comment>).', $superAdmin->getEmail()));
            return;
        }

        $output->writeln('<question>Please provide initial informations ...</question>');

        /** @var \Symfony\Component\Console\Helper\DialogHelper $dialog */
        $dialog = $command->getHelperSet()->get('dialog');

        $email = $dialog->askAndValidate($output, 'Email: ', function ($answer) use ($userRepository) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('This is not a valid email address.');
            }
            if (null !== $userRepository->findOneBy(array('email' => $answer))) {
                throw new \RuntimeException('This email address is already used.');
            }
            return $answer;
        }, 3);

        $password = $dialog->askAndValidate($output, 'Password: ', function ($answer) {
            if (!(preg_match('#^[a-zA-Z0-9]+$#', $answer) && strlen($answer) > 5)) {
                throw new \RuntimeException('Password should be composed of at least 6 letters and numbers.');
            }
            return $answer;
        }, 3);

        $notBlankValidator = function ($answer) {
            if (0 === strlen($answer)) {
                throw new \RuntimeException('This cannot be blank.');
            }
            return $answer;
        };

        $firstName = $dialog->askAndValidate($output, 'First name: ', $notBlankValidator, 3);
        $lastName = $dialog->askAndValidate($output, 'Last name: ', $notBlankValidator, 3);

        $userManager = $container->get('fos_user.user_manager');
        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $userManager->createUser();
        $user
            ->setGroup($group)
            ->setGender('mr')
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setPlainPassword($password)
            ->setEmail($email)
            ->setEnabled(true)
        ;
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