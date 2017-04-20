<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Search;

use Ekyna\Bundle\AdminBundle\Repository\GroupRepository;
use Ekyna\Bundle\UserBundle\Model\UserInterface;
use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepository;
use Ekyna\Component\Resource\Search\Request;
use Ekyna\Component\Resource\Search\Result;
use Elastica\Query;

/**
 * Class UserRepository
 * @package Ekyna\Bundle\AdminBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UserRepository extends SearchRepository
{
    /**
     * @var GroupRepository
     */
    private GroupRepository $groupRepository;


    /**
     * Sets the group repository.
     *
     * @param GroupRepository $repository
     */
    public function setGroupRepository(GroupRepository $repository): void
    {
        $this->groupRepository = $repository;
    }

    /**
     * @inheritDoc
     */
    protected function createQuery(Request $request): Query\AbstractQuery
    {
        $query = parent::createQuery($request);

        if (empty($roles = (array)$request->getParameter('roles', []))) {
            return $query;
        }

        // TODO Use scalar result to ids directly
        if (empty($groups = $this->groupRepository->findByRoles($roles))) {
            return $query;
        }

        $groupsIds = [];
        foreach ($groups as $group) {
            $groupsIds[] = $group->getId();
        }

        $bool = new Query\BoolQuery();
        $bool
            ->addMust($query)
            ->addMust(new Query\Terms('group', $groupsIds));

        return $query;
    }

    /**
     * @inheritDoc
     */
    protected function createResult($source, Request $request): ?Result
    {
        if (null === $result = parent::createResult($source, $request)) {
            return null;
        }

        $id = $source instanceof UserInterface ? $source->getId() : $source['id'];

        return $result
            ->setIcon('fa fa-user')
            ->setRoute('admin_ekyna_admin_user_read') // TODO Use resource/action
            ->setParameters(['userId' => $id]);
    }

    /**
     * @inheritDoc
     */
    protected function getDefaultFields(): array
    {
        return [
            'first_name',
            'first_name.analyzed',
            'last_name',
            'last_name.analyzed',
            'email',
            'email.analyzed',
        ];
    }
}
