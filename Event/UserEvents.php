<?php

namespace Ekyna\Bundle\AdminBundle\Event;

/**
 * Class UserEvents
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class UserEvents
{
    // Persistence
    const INSERT      = 'ekyna_admin.user.insert';
    const UPDATE      = 'ekyna_admin.user.update';
    const DELETE      = 'ekyna_admin.user.delete';

    // Domain
    const INITIALIZE  = 'ekyna_admin.user.initialize';

    const PRE_CREATE  = 'ekyna_admin.user.pre_create';
    const POST_CREATE = 'ekyna_admin.user.post_create';

    const PRE_UPDATE  = 'ekyna_admin.user.pre_update';
    const POST_UPDATE = 'ekyna_admin.user.post_update';

    const PRE_DELETE  = 'ekyna_admin.user.pre_delete';
    const POST_DELETE = 'ekyna_admin.user.post_delete';

    const PRE_GENERATE_PASSWORD  = 'ekyna_admin.user.pre_generate_password';
    const POST_GENERATE_PASSWORD = 'ekyna_admin.user.post_generate_password';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
