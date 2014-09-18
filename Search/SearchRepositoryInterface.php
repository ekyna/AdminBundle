<?php

namespace Ekyna\Bundle\AdminBundle\Search;

/**
 * Interface SearchRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Search
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface SearchRepositoryInterface
{
    /**
     * Default text search.
     * 
     * @param string $text
     * 
     * @return array
     */
    public function defaultSearch($text);
}
