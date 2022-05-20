<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Component\Resource\Action\Permission;

/**
 * Class DeleteAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DeleteAction extends AbstractConfirmAction
{
    protected const NAME = 'admin_delete';

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::DELETE,
            'route'      => [
                'name'     => 'admin_%s_delete',
                'path'     => '/delete',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label' => 'button.remove',
                'theme' => 'danger',
                'icon'  => 'trash',
            ],
            'options'    => [
                'template'      => '@EkynaAdmin/Entity/Crud/delete.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_confirm.html.twig',
                'serialization' => ['groups' => ['Default'], 'admin' => true],
            ],
        ];
    }
}
