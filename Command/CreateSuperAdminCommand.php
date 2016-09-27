<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\UserBundle\Command\UserInputInteract;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\Question;

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
            ->setHelp(<<<EOT
The <info>ekyna:admin:create-super-admin</info> creates a super admin user:

  <info>php app/console ekyna:admin:create-super-admin</info>

You can also optionally specify the user datas (email, password):

  <info>php app/console ekyna:admin:create-super-admin john.doe@example.org password</info>
EOT
            )
        ;
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $userInput = new UserInputInteract($this->getContainer()->get('ekyna_user.user.repository'));

        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');

        $userInput->interact($input, $output, $helper);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $groupRepository = $this->getContainer()->get('ekyna_user.group.repository');

        /** @var \Ekyna\Bundle\UserBundle\Model\GroupInterface $group */
        if (null === $group = $groupRepository->findOneByRole('ROLE_SUPER_ADMIN')) {
            $output->writeln('Super admin group not found, aborting.');
            return;
        }

        $userRepository = $this->getContainer()->get('ekyna_user.user.repository');
        if (null !== $userRepository->findOneBy(['email' => $input->getArgument('email')])) {
            $output->writeln(sprintf('User "%s" already exists.', $input->getArgument('email')));
            return;
        }

        $userManager = $this->getContainer()->get('fos_user.user_manager');

        /** @var \Ekyna\Bundle\UserBundle\Model\UserInterface $user */
        $user = $userManager->createUser();
        $user
            ->setGroup($group)
            ->setPlainPassword($input->getArgument('password'))
            ->setEmail($input->getArgument('email'))
            ->setEnabled(true)
        ;
        $userManager->updateUser($user);

        $output->writeln('Super Admin has been successfully created.');
    }
}
