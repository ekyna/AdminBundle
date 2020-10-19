<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Repository\GroupRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\SecurityUtil;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use RuntimeException;
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

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;


    /**
     * Constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param ResourceOperatorInterface $userOperator
     * @param GroupRepositoryInterface  $groupRepository
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ResourceOperatorInterface $userOperator,
        GroupRepositoryInterface $groupRepository
    ) {
        parent::__construct($userRepository, $userOperator);

        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->addArgument('group', InputArgument::OPTIONAL, 'The user group id')
            ->addArgument('email', InputArgument::OPTIONAL, 'The user email')
            ->addArgument('password', InputArgument::OPTIONAL, 'The user password')
            ->setDescription("Creates a new admin user.");
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');

        // Group  ---------------------------------------------------------------
        $group = null;
        if ($id = $input->getArgument('group')) {
            $group = $this->groupRepository->find($id);
        }
        if (is_null($group)) {
            $groups = [];
            foreach ($this->groupRepository->findAll() as $group) {
                $groups[$group->getName()] = $group;
            }
            if (empty($groups)) {
                $output->writeln('<error>Please create groups first.</error>');

                return;
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
        } catch (Throwable $e) {
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
        } catch (Throwable $e) {
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

            return;
        }

        // Create user ---------------------------------------------------------------
        /** @var \Ekyna\Bundle\AdminBundle\Model\UserInterface $user */
        $user = $this->userRepository->createNew();
        $user
            ->setGroup($group)
            ->setEmail($email)
            ->setActive(true);

        // Set or generate password
        if (empty($password)) {
            $password = SecurityUtil::generatePassword($user);
            $output->writeln(sprintf('<info>Generated password: %s.</info>', $password));
        } else {
            $user->setPlainPassword($password);
        }

        $event = $this->userOperator->create($user);

        if ($event->hasErrors()) {
            $output->writeln(sprintf(
                '<error>An error occurred while creating admin user "%s".</error>',
                $user->getEmail()
            ));

            return;
        }

        $output->writeln(sprintf(
            '<info>Admin user "%s" has been successfully created.</info>',
            $user->getEmail()
        ));
    }
}
