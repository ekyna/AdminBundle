<?php

namespace Ekyna\Bundle\AdminBundle\Search;

/**
 * SearchRepositoryInterface.
 *
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
