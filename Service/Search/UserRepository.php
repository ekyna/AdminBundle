<?php

namespace Ekyna\Bundle\AdminBundle\Service\Search;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Elastica\Query;
use Elastica\Filter;

/**
 * Class UserRepository
 * @package Ekyna\Bundle\AdminBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UserRepository extends ResourceRepository
{
    /**
     * Search users having the given roles.
     *
     * @param string           $expression
     * @param GroupInterface[] $groups
     * @param int              $limit
     *
     * @return \Ekyna\Bundle\ProductBundle\Model\ProductInterface[]
     */
    public function searchByGroups($expression, array $groups, $limit = 10)
    {
        $matchQuery = new Query\MultiMatch();
        $matchQuery->setQuery($expression)->setFields($this->getDefaultMatchFields());

        $groupsIds = [];
        foreach ($groups as $group) {
            $groupsIds[] = $group->getId();
        }

        $rolesFilter = new Filter\Terms();
        $rolesFilter->setTerms('group', $groupsIds);

        $filtered = new Query\Filtered();
        $filtered->setQuery($matchQuery);
        $filtered->setFilter($rolesFilter);

        return $this->find($filtered, $limit);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields()
    {
        return [
            'email',
            'first_name',
            'last_name',
        ];
    }
}
