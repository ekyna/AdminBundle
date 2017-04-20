<?php

declare(strict_types=1);

use Ekyna\Bundle\AdminBundle\Model;

return [
    Model\UserInterface::class => [
        'superadmin'    => [
            '__factory'     => [
                '@ekyna_admin.factory.user::create' => [],
            ],
            'email'         => 'superadmin@example.org',
            'plainPassword' => 'superadmin',
            'group'         => "<adminGroup('Super administrateur')>", // TODO By code
            'enabled'       => true,
        ],
        'administrator' => [
            '__factory'     => [
                '@ekyna_admin.factory.user::create' => [],
            ],
            'email'         => 'administrator@example.org',
            'plainPassword' => 'administrator',
            'group'         => "<adminGroup('Administrateur')>",
            'enabled'       => true,
        ],
        'moderator'     => [
            '__factory'     => [
                '@ekyna_admin.factory.user::create' => [],
            ],
            'email'         => 'moderator@example.org',
            'plainPassword' => 'moderator',
            'group'         => "<adminGroup('Modérateur')>",
            'enabled'       => true,
        ],
    ],
];
