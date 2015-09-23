<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Class CreateSuperAdminCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CreateSuperAdminCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:admin:create-super-admin')
            ->setDescription('Creates a super admin user.')
            ->addArgument('email', InputArgument::OPTIONAL, 'The email address.')
            ->addArgument('password', InputArgument::OPTIONAL, 'The password.')
            ->addArgument('firstName', InputArgument::OPTIONAL, 'The first name.')
            ->addArgument('lastName', InputArgument::OPTIONAL, 'The last name.')
            ->setHelp(<<<EOT
The <info>ekyna:admin:create-super-admin</info> creates a super admin user:

  <info>php app/console ekyna:admin:create-super-admin</info>

You can also optionally specify the user datas (email, password, first name and last name):

  <info>php app/console ekyna:admin:create-super-admin john.doe@example.org password John Doe</info>
EOT
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupRepository = $this->getContainer()->get('ekyna_user.group.repository');
        $userRepository = $this->getContainer()->get('ekyna_user.user.repository');

        /** @var \Ekyna\Bundle\UserBundle\Model\GroupInterface $group */
        if (null === $group = $groupRepository->findOneByRole('ROLE_SUPER_ADMIN')) {
            $output->writeln('Super admin group not found, aborting.');
            return;
        }

        /** @var \Symfony\Component\Console\Helper\DialogHelper $dialog */
        $dialog = $this->getHelperSet()->get('dialog');

        // Email
        $email = $input->getArgument('email');
        if (empty($email)) {
            $email = $dialog->askAndValidate($output, 'Email: ', function ($answer) use ($userRepository) {
                if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('This is not a valid email address.');
                }
                if (null !== $userRepository->findOneBy(['email' => $answer])) {
                    throw new \RuntimeException('This email address is already used.');
                }
                return $answer;
            }, 3);
        }

        // Password
        $password = $input->getArgument('password');
        if (empty($password)) {
            $password = $dialog->askAndValidate($output, 'Password: ', function ($answer) {
                if (!(preg_match('#^[a-zA-Z0-9]+$#', $answer) && strlen($answer) > 5)) {
                    throw new \RuntimeException('Password should be composed of at least 6 letters and numbers.');
                }
                return $answer;
            }, 3);
        }

        $notBlankValidator = function ($answer) {
            if (0 === strlen($answer)) {
                throw new \RuntimeException('This cannot be blank.');
            }
            return $answer;
        };

        // First name
        $firstName = $input->getArgument('firstName');
        if (empty($firstName)) {
            $firstName = $dialog->askAndValidate($output, 'First name: ', $notBlankValidator, 3);
        }

        // Last name
        $lastName = $input->getArgument('lastName');
        if (empty($lastName)) {
            $lastName = $dialog->askAndValidate($output, 'Last name: ', $notBlankValidator, 3);
        }

        $userManager = $this->getContainer()->get('fos_user.user_manager');
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
}
