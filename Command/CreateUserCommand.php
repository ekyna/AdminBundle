<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Factory\UserFactoryInterface;
use Ekyna\Bundle\AdminBundle\Repository\GroupRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\User\Service\Security\SecurityUtil;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Throwable;

/**
 * Class CreateUserCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateUserCommand extends AbstractUserCommand
{
    protected static $defaultName = 'ekyna:admin:create-user';

    protected GroupRepositoryInterface $groupRepository;
    protected UserFactoryInterface     $userFactory;

    public function __construct(
        UserRepositoryInterface $userRepository,
        ResourceManagerInterface $userManager,
        SecurityUtil $securityUtil,
        UserFactoryInterface $userFactory,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($userRepository, $userManager, $securityUtil);

        $this->userFactory = $userFactory;
        $this->groupRepository = $groupRepository;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('group', InputArgument::OPTIONAL, 'The user group id')
            ->addArgument('email', InputArgument::OPTIONAL, 'The user email')
            ->addArgument('password', InputArgument::OPTIONAL, 'The user password')
            ->setDescription('Creates a new admin user.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');

        // Group  ---------------------------------------------------------------
        $group = null;
        if ($id = (int)$input->getArgument('group')) {
            $group = $this->groupRepository->find($id);
        }
        if (is_null($group)) {
            $groups = [];
            foreach ($this->groupRepository->findAll() as $group) {
                $groups[$group->getName()] = $group;
            }
            if (empty($groups)) {
                $output->writeln('<error>Please create groups first.</error>');

                return 1;
            }

            $question = new ChoiceQuestion('Please select the user group', array_keys($groups), 0);
            $question->setErrorMessage('Group %s is invalid.');
            $groupName = $helper->ask($input, $output, $question);
            $group = $groups[$groupName];
        }


        // Email ---------------------------------------------------------------
        $emailValidator = function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('This is not a valid email address.');
            }
            if (null !== $this->userRepository->findOneByEmail($answer, false)) {
                throw new RuntimeException('This email address is already used.');
            }

            return $answer;
        };

        try {
            $email = $emailValidator($input->getArgument('email'));
        } catch (Throwable $exception) {
            $email = null;
        }
        if (is_null($email)) {
            $question = new Question('Email: ');
            $question->setValidator($emailValidator);
            $question->setMaxAttempts(3);
            $email = $helper->ask($input, $output, $question);
        }


        // Password ---------------------------------------------------------------
        $passwordValidator = function ($answer) {
            if (!preg_match(self::PASSWORD_REGEX, $answer)) {
                throw new RuntimeException(
                    'Password should be composed of at least 6 digits excluding white-space characters.'
                );
            }

            return $answer;
        };

        try {
            $password = $passwordValidator($input->getArgument('password'));
        } catch (Throwable $exception) {
            $password = null;
        }
        if (is_null($password)) {
            $question = new ConfirmationQuestion('Generate password ?', false);
            if (!$helper->ask($input, $output, $question)) {
                $question = new Question('Password: ');
                $question->setValidator($passwordValidator);
                $question->setMaxAttempts(3);
                $password = $helper->ask($input, $output, $question);
            }
        }

        if (!($group && $email)) {
            $output->writeln('<info>Abort.</info>');

            return 0;
        }

        // Create user ---------------------------------------------------------------
        $user = $this->userFactory->create();
        $user
            ->setGroup($group)
            ->setEmail($email)
            ->setEnabled(true);

        // Set or generate password
        if (empty($password)) {
            $password = $this->securityUtil->generatePassword();
            $output->writeln(sprintf('<info>Generated password: %s.</info>', $password));
        }

        $user->setPlainPassword($password);

        $event = $this->userManager->create($user);

        if ($event->hasErrors()) {
            $output->writeln(sprintf(
                '<error>An error occurred while creating admin user "%s".</error>',
                $user->getEmail()
            ));

            return 1;
        }

        $output->writeln(sprintf(
            '<info>Admin user "%s" has been successfully created.</info>',
            $user->getEmail()
        ));

        return 0;
    }
}
