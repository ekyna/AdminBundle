<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\SecurityUtil;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Console\Command\Command;
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
    protected static $defaultName = 'ekyna:admin:generate-api-token';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'The user id')
            ->addOption('email', null, InputOption::VALUE_REQUIRED, 'The user email')
            ->setDescription("Generates user(s) api token(s).");
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (0 < $id = $input->getOption('id')) {
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
            return;
        }

        foreach ($users as $user) {
            $name = sprintf('[%d] %s', $user->getId(), $user->getEmail());

            $output->write(sprintf('<comment>%s</comment> %s ',
                $name,
                str_pad('.', 64 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            SecurityUtil::generateApiToken($user);
            $event = $this->userOperator->update($user);

            if ($event->hasErrors()) {
                $output->writeln('<error>error</error>');
                break;
            }

            $output->writeln('<info>done</info>');
        }
    }
}
