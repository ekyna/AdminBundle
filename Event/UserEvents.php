<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Event;

/**
 * Class UserEvents
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class UserEvents
{
    // Persistence
    public const INSERT                 = 'ekyna_admin.user.insert';
    public const UPDATE                 = 'ekyna_admin.user.update';
    public const DELETE                 = 'ekyna_admin.user.delete';

    // Domain
    public const PRE_CREATE             = 'ekyna_admin.user.pre_create';
    public const POST_CREATE            = 'ekyna_admin.user.post_create';

    public const PRE_UPDATE             = 'ekyna_admin.user.pre_update';
    public const POST_UPDATE            = 'ekyna_admin.user.post_update';

    public const PRE_DELETE             = 'ekyna_admin.user.pre_delete';
    public const POST_DELETE            = 'ekyna_admin.user.post_delete';

    public const PRE_GENERATE_PASSWORD  = 'ekyna_admin.user.pre_generate_password';
    public const POST_GENERATE_PASSWORD = 'ekyna_admin.user.post_generate_password';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
