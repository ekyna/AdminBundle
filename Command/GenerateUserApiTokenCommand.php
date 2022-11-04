<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Class GenerateUserApiTokenCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GenerateUserApiTokenCommand extends AbstractUserCommand
{
    protected static $defaultName        = 'ekyna:admin:generate-api-token';
    protected static $defaultDescription = 'Generates user(s) api token(s).';

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The user id')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'The user email');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (0 < $id = (int)$input->getOption('id')) {
            $user = $this->userRepository->find($id);
            if (!$user) {
                throw new InvalidOptionException("Option 'id' does not match any user.");
            }
            $users = [$user];
        } elseif ($email = $input->getOption('email')) {
            $user = $this->userRepository->findOneByEmail($email);
            if (!$user) {
                throw new InvalidOptionException("Option 'email' does not match any user.");
            }
            $users = [$user];
        } else {
            $users = $this->userRepository->findAll();
        }

        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(sprintf('Generate API token(s) for %s user(s) ?', count($users)), false);
        if (!$helper->ask($input, $output, $question)) {
            return 0;
        }

        /** @var UserInterface $user */
        foreach ($users as $user) {
            $name = sprintf('[%d] %s', $user->getId(), $user->getEmail());

            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 64 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            $token = $this->securityUtil->generateToken();

            $user->setApiToken($token);

            $event = $this->userManager->update($user);

            if ($event->hasErrors()) {
                $output->writeln('<error>error</error>');
                break;
            }

            $output->writeln('<info>done</info>');
        }

        return 0;
    }
}
