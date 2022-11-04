<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use RuntimeException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

use function filter_var;
use function preg_match;
use function sprintf;

/**
 * Class ChangeUserPasswordCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ChangeUserPasswordCommand extends AbstractUserCommand
{
    protected static $defaultName        = 'ekyna:admin:change-user-password';
    protected static $defaultDescription = 'Changes a admin user\'s password.';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelperSet()->get('question');


        // Email ---------------------------------------------------------------
        $question = new Question('Email: ');
        $question->setValidator(function ($answer) {
            if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('This is not a valid email address.');
            }
            if (null === $this->userRepository->findOneByEmail($answer, false)) {
                throw new RuntimeException('No admin user found for this email.');
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
                if (!preg_match(self::PASSWORD_REGEX, $answer)) {
                    throw new RuntimeException('Password should be composed of at least 6 digits excluding white-space characters.');
                }

                return $answer;
            });
            $question->setMaxAttempts(3);
            $question->setHidden(true);
            $password = $helper->ask($input, $output, $question);
        }


        // Fetch user ---------------------------------------------------------------
        /** @var UserInterface $user */
        $user = $this->userRepository->findOneByEmail($email, false);
        if (null === $user) {
            $output->writeln(sprintf('<error>No user found for email "%s".</error>', $email));

            return 1;
        }

        // Set or generate password
        if (empty($password)) {
            $password = $this->securityUtil->generatePassword();
            $output->writeln(sprintf('<info>Generated password: %s</info>', $password));
        }

        $user
            ->setPassword('TriggerPersistence')
            ->setPlainPassword($password);

        $event = $this->userManager->update($user);

        if ($event->hasErrors()) {
            $output->writeln(sprintf(
                '<error>An error occurred while changing password of admin user "%s".</error>',
                $user->getEmail()
            ));

            return 1;
        }

        $output->writeln(sprintf(
            '<info>Password has been successfully changed for admin user "%s".</info>',
            $user->getEmail()
        ));

        return 0;
    }
}
