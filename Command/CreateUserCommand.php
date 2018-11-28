<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Repository\GroupRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class CreateUserCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CreateUserCommand extends Command
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var GroupRepositoryInterface
     */
    private $groupRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $userOperator;


    /**
     * Constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param GroupRepositoryInterface  $groupRepository
     * @param ResourceOperatorInterface $userOperator
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        GroupRepositoryInterface $groupRepository,
        ResourceOperatorInterface $userOperator
    ) {
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
        $this->userOperator = $userOperator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:admin:create-user')
            ->setDescription('Creates a new admin user.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');

        // Group  ---------------------------------------------------------------
        $groups = [];
        foreach ($this->groupRepository->findAll() as $group) {
            $groups[$group->getName()] = $group;
        }
        if (empty($groups)) {
            $output->writeln('<error>Please create groups first.</error>');

            return;
        }

        $question = new ChoiceQuestion('Please select the user group', array_keys($groups));
        $question->setErrorMessage('Group %s is invalid.');
        $groupName = $helper->ask($input, $output, $question);
        $group = $groups[$groupName];


        // Email ---------------------------------------------------------------
        $question = new Question('Email: ');
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('This is not a valid email address.');
            }
            if (null !== $this->userRepository->findOneBy(['email' => $answer])) {
                throw new \RuntimeException('This email address is already used.');
            }

            return $answer;
        });
        $question->setMaxAttempts(3);
        $email = $helper->ask($input, $output, $question);


        // Password ---------------------------------------------------------------
        $question = new Question('Password: ');
        $question->setValidator(function ($answer) {
            if (!(preg_match('#^[a-zA-Z0-9]+$#', $answer) && strlen($answer) > 5)) {
                throw new \RuntimeException('Password should be composed of at least 6 letters and numbers.');
            }

            return $answer;
        });
        $question->setMaxAttempts(3);
        $password = $helper->ask($input, $output, $question);


        // Create user ---------------------------------------------------------------
        /** @var \Ekyna\Bundle\AdminBundle\Model\UserInterface $user */
        $user = $this->userRepository->createNew();
        $user
            ->setGroup($group)
            ->setPlainPassword($password)
            ->setEmail($email)
            ->setActive(true);

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
