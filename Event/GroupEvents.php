<?php

namespace Ekyna\Bundle\AdminBundle\Event;

/**
 * Class GroupEvents
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class GroupEvents
{
    // Persistence
    const INSERT      = 'ekyna_admin.group.insert';
    const UPDATE      = 'ekyna_admin.group.update';
    const DELETE      = 'ekyna_admin.group.delete';

    // Domain
    const INITIALIZE  = 'ekyna_admin.group.initialize';

    const PRE_CREATE  = 'ekyna_admin.group.pre_create';
    const POST_CREATE = 'ekyna_admin.group.post_create';

    const PRE_UPDATE  = 'ekyna_admin.group.pre_update';
    const POST_UPDATE = 'ekyna_admin.group.post_update';

    const PRE_DELETE  = 'ekyna_admin.group.pre_delete';
    const POST_DELETE = 'ekyna_admin.group.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
