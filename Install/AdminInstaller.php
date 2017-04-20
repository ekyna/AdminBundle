<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Install;

use Ekyna\Bundle\AdminBundle\Factory\GroupFactoryInterface;
use Ekyna\Bundle\AdminBundle\Manager\GroupManagerInterface;
use Ekyna\Bundle\AdminBundle\Repository\GroupRepositoryInterface;
use Ekyna\Bundle\InstallBundle\Install\AbstractInstaller;
use Ekyna\Bundle\ResourceBundle\Service\Security\AclManagerInterface;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;
use function str_pad;

use const STR_PAD_LEFT;

/**
 * Class AdminInstaller
 * @package Ekyna\Bundle\AdminBundle\Install
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminInstaller extends AbstractInstaller
{
    private GroupRepositoryInterface  $groupRepository;
    private GroupManagerInterface     $groupManager;
    private GroupFactoryInterface     $groupFactory;
    private ResourceRegistryInterface $resourceRegistry;
    private AclManagerInterface       $aclManager;

    /**
     * Default groups
     */
    protected array $groups = [
        'Super administrateur' => [
            'roles'       => [
                'ROLE_SUPER_ADMIN',
                'ROLE_ALLOWED_TO_SWITCH',
            ],
            'permissions' => [],
        ],
        'Administrateur'       => [
            'roles'       => [
                'ROLE_ADMIN',
            ],
            'permissions' => [
                Permission::LIST,
                Permission::READ,
                Permission::CREATE,
                Permission::UPDATE,
                Permission::DELETE,
                Permission::SEARCH,
            ],
        ],
        'Modérateur'           => [
            'roles'       => [
                'ROLE_ADMIN',
            ],
            'permissions' => [
                Permission::LIST,
                Permission::READ,
            ],
        ],
    ];

    /**
     * Constructor.
     *
     * @param GroupRepositoryInterface  $groupRepository
     * @param GroupManagerInterface     $groupManager
     * @param GroupFactoryInterface     $groupFactory
     * @param ResourceRegistryInterface $resourceRegistry
     * @param AclManagerInterface       $aclManager
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        GroupManagerInterface $groupManager,
        GroupFactoryInterface $groupFactory,
        ResourceRegistryInterface $resourceRegistry,
        AclManagerInterface $aclManager
    ) {
        $this->groupRepository = $groupRepository;
        $this->groupManager = $groupManager;
        $this->groupFactory = $groupFactory;
        $this->resourceRegistry = $resourceRegistry;
        $this->aclManager = $aclManager;
    }

    /**
     * @inheritDoc
     */
    public function install(Command $command, InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('<info>[Admin] Creating users groups:</info>');
        $this->createGroups($output);
        $output->writeln('');

        $output->writeln('<info>[Admin] Configuring group\'s ACL:</info>');
        $this->configureAcl($output);
        $output->writeln('');
    }

    /**
     * Creates the default groups.
     *
     * @param OutputInterface $output
     */
    private function createGroups(OutputInterface $output): void
    {
        foreach ($this->groups as $name => $config) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            if ($this->groupRepository->findOneByName($name)) {
                $output->writeln('<comment>exists</comment>');

                continue;
            }

            $group = $this->groupFactory->create();
            $group
                ->setName($name)
                ->setRoles($config['roles']);

            $this->groupManager->persist($group);

            $output->writeln('<info>created</info>');
        }

        $this->groupManager->flush();
    }

    /**
     * Configures the group's ACL.
     *
     * @param OutputInterface $output
     */
    private function configureAcl(OutputInterface $output): void
    {
        foreach ($this->groups as $name => $config) {
            $output->write(sprintf(
                '- <comment>%s</comment> %s ',
                $name,
                str_pad('.', 44 - mb_strlen($name), '.', STR_PAD_LEFT)
            ));

            $group = $this->groupRepository->findOneByName($name);

            foreach ($this->resourceRegistry->all() as $resource) {
                foreach ($config['permissions'] as $permission) {
                    if (!in_array($permission, $resource->getPermissions(), true)) {
                        continue;
                    }

                    $this
                        ->aclManager
                        ->setPermission($group, $resource->getNamespace(), $resource->getName(), $permission, true);
                }
            }

            $output->writeln('<info>done</info>');
        }

        $this->aclManager->flush();
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'ekyna_admin';
    }
}
