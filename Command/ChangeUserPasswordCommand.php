<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\SecurityUtil;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Class ChangeUserPasswordCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ChangeUserPasswordCommand extends Command
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ResourceOperatorInterface
     */
    private $userOperator;


    /**
     * Constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param ResourceOperatorInterface $userOperator
     */
    public function __construct(UserRepositoryInterface $userRepository, ResourceOperatorInterface $userOperator)
    {
        $this->userRepository = $userRepository;
        $this->userOperator = $userOperator;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ekyna:admin:change-user-password')
            ->setDescription("Changes a admin user's password.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Helper\QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');


        // Email ---------------------------------------------------------------
        $question = new Question('Email: ');
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('This is not a valid email address.');
            }
            if (null === $this->userRepository->findOneBy(['email' => $answer])) {
                throw new \RuntimeException('No admin user found for this email.');
            }

            return $answer;
        });
        $question->setMaxAttempts(3);
        $email = $helper->ask($input, $output, $question);


        // Password ---------------------------------------------------------------
        $password = null;
        $question = new ConfirmationQuestion('Generate password ?');
        if (!$helper->ask($input, $output, $question)) {
            $question = new Question('Password: ');
            $question->setValidator(function ($answer) {
                if (!(preg_match('#^[a-zA-Z0-9]+$#', $answer) && strlen($answer) > 5)) {
                    throw new \RuntimeException('Password should be composed of at least 6 letters and numbers.');
                }

                return $answer;
            });
            $question->setMaxAttempts(3);
            $password = $helper->ask($input, $output, $question);
        }


        // Create user ---------------------------------------------------------------
        /** @var \Ekyna\Bundle\AdminBundle\Model\UserInterface $user */
        $user = $this->userRepository->findOneByEmail($email);
        if (null === $user) {
            $output->writeln(sprintf(
                '<error>No user found for email "%s".</error>',
                $user->getEmail()
            ));

            return;
        }

        // Set or generate password
        if (empty($password)) {
            $password = SecurityUtil::generatePassword($user);
            $output->writeln(sprintf('<info>Generated password: %s</info>', $password));
        } else {
            $user->setPlainPassword($password);
        }

        // Persist
        $event = $this->userOperator->update($user);
        if ($event->hasErrors()) {
            $output->writeln(sprintf(
                '<error>An error occurred while changing password of admin user "%s".</error>',
                $user->getEmail()
            ));

            return;
        }

        $output->writeln(sprintf(
            '<info>Password has been successfully changed for admin user "%s".</info>',
            $user->getEmail()
        ));
    }
}
