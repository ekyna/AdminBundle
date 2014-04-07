<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Ekyna\Bundle\UserBundle\Entity\Group;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Ekyna\Bundle\UserBundle\Entity\User;

/**
 * InstallCommand
 */
class InstallCommand extends ContainerAwareCommand
{
    /**
     * Default groups :
     * [name => [[roles], permission, default]]
     */
    protected $defaultGroups = array(
        'Super administrateur' => array(array('ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'), 'MASTER', false),
        'Administrateur'       => array(array('ROLE_ADMIN'), 'OPERATOR', false),
        'Modérateur'           => array(array('ROLE_ADMIN'), 'OPERATOR', false),
        'Utilisateur'          => array(array(), 'VIEW', true),
    );

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:admin:init')
            ->setDescription('Initialize administration.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if(0 !== $this->initAcl($input, $output)) {
            return 1;
        }
        if(0 !== $this->createGroups($input, $output)) {
            return 1;
        }
        if(0 !== $this->createAclRules($input, $output)) {
            return 1;
        }
        if(0 !== $this->createSuperAdmin($input, $output)) {
            return 1;
        }
        return 0;
    }

    private function createSuperAdmin(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Creating Super Admin.</info>');
        
        $dialog = $this->getHelperSet()->get('dialog');

        $username = $dialog->askAndValidate(
            $output,
            'Username: ',
            function ($answer) {
                if (!(preg_match('#[a-zA-Z]+#', $answer) && strlen($answer) > 4)) {
                    throw new \RunTimeException('Username should be composed of at least 5 letters.');
                }
                return $answer;
            },
            3
        );

        $email = $dialog->askAndValidate(
            $output,
            'Email address: ',
            function ($answer) {
                if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                    throw new \RunTimeException('This is not a valid email address.');
                }
                return $answer;
            },
            3
        );

        $password = $dialog->askAndValidate(
            $output,
            'Password: ',
            function ($answer) {
                if (!(preg_match('#[a-zA-Z0-9]+#', $answer) && strlen($answer) > 5)) {
                    throw new \RunTimeException('Password should be composed of at least 6 letters and numbers.');
                }
                return $answer;
            },
            3
        );

        $em = $this->getContainer()->get('ekyna_user.group.manager');
        $group = $this->getContainer()
            ->get('ekyna_user.group.repository')
            ->findOneBy(array('name' => array_keys($this->defaultGroups)[0]));

        $user = new User();
        $user
            ->setUsername($username)
            ->setPlainPassword($password)
            ->setEmail($email)
            ->setGroup($group)
            ->setEnabled(true)
        ;

        $em->persist($user);
        $em->flush();

        $output->writeln('Super Admin have been successfully created.');
    }
    
    /**
     * Creates default groups entities
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return number
     */
    private function createGroups(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Creating groups.</info>');

        $em = $this->getContainer()->get('ekyna_user.group.manager');
        $repository = $this->getContainer()->get('ekyna_user.group.repository');

        foreach($this->defaultGroups as $name => $options) {
            if(null !== $group = $repository->findOneBy(array('name' => $name))) {
                continue;
            }
            $group = new Group($name);
            $group
                ->setRoles($options[0])
                ->setDefault($options[2])
            ;
            $em->persist($group);
        }
        $em->flush();

        $output->writeln('Groups have been successfully created.');
        return 0;
    }

    /**
     * Creates default groups permissions for each admin pool configurations (if available)
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return number
     */
    private function createAclRules(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Creating Acl rules.</info>');

        $registry = $this->getContainer()->get('ekyna_admin.pool_registry');
        $aclManipulator = $this->getContainer()->get('ekyna_admin.acl_manipulator');
        $groups = $this->getContainer()->get('ekyna_user.group.repository')->findAll();

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
            $aclManipulator->updateGroup($group, $datas);
        }

        $output->writeln('Acl rules have been successfully created.');
        return 0;
    }

    /**
     * Calls "init:acl" command if available
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return number
     */
    private function initAcl(InputInterface $input, OutputInterface $output)
    {
        $commandName = 'init:acl';
        try {
            $command = $this->getApplication()->find($commandName);
        }catch(\InvalidArgumentException $e) {
            $output->writeln(sprintf('"%s" command not found. Aborting.', $commandName));
            return 2;
        }

        $output->writeln(sprintf('<info>Running "%s" command</info>', $commandName));
        $arguments = array(
            'command' => $commandName,
        );
        $cmdInput = new ArrayInput($arguments);
        $command->run($cmdInput, $output);
        return 0;
    }
}
