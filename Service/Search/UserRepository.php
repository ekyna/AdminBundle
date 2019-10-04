<?php

namespace Ekyna\Bundle\AdminBundle\Service\Search;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Component\Resource\Search\Elastica\ResourceRepository;
use Elastica\Query;

/**
 * Class UserRepository
 * @package Ekyna\Bundle\AdminBundle\Search
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class UserRepository extends ResourceRepository
{
    /**
     * Creates the search query.
     *
     * @param string $expression
     * @param GroupInterface[] $groups
     *
     * @return Query
     */
    public function createSearchQuery(string $expression, array $groups = []): Query
    {
        $match = new Query\MultiMatch();
        $match
            ->setQuery($expression)
            ->setType(Query\MultiMatch::TYPE_CROSS_FIELDS)
            ->setFields($this->getDefaultMatchFields());

        if (empty($groups)) {
            return Query::create($match);
        }

        $groupsIds = [];
        foreach ($groups as $group) {
            $groupsIds[] = $group->getId();
        }

        $bool = new Query\BoolQuery();
        $bool
            ->addMust($match)
            ->addMust(new Query\Terms('group', $groupsIds));

        return Query::create($bool);
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultMatchFields(): array
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
