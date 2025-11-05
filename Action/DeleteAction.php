<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use function Symfony\Component\Translation\t;

/**
 * Class DeleteAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DeleteAction extends AbstractConfirmAction
{
    protected const NAME = 'admin_delete';

    protected function doPersist(): ResourceEventInterface
    {
        $resource = $this->context->getResource();

        return $this->getManager()->delete($resource);
    }

    protected function getFormButtons(string $cancelPath = null): array
    {
        return [
            'submit' => [
                'type'    => SubmitType::class,
                'options' => [
                    'button_class' => 'danger',
                    'label'        => t('button.confirm', [], 'EkynaUi'),
                    'attr'         => ['icon' => 'remove'],
                ],
            ],
        ];
    }

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
