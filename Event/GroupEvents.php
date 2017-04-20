<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Event;

/**
 * Class GroupEvents
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class GroupEvents
{
    // Persistence
    public const INSERT      = 'ekyna_admin.group.insert';
    public const UPDATE      = 'ekyna_admin.group.update';
    public const DELETE      = 'ekyna_admin.group.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_admin.group.pre_create';
    public const POST_CREATE = 'ekyna_admin.group.post_create';

    public const PRE_UPDATE  = 'ekyna_admin.group.pre_update';
    public const POST_UPDATE = 'ekyna_admin.group.post_update';

    public const PRE_DELETE  = 'ekyna_admin.group.pre_delete';
    public const POST_DELETE = 'ekyna_admin.group.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
