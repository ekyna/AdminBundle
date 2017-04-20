<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UpdateAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UpdateAction extends AbstractFormAction
{
    protected const NAME = 'admin_update';


    public function __invoke(): Response
    {
        if (null === $this->context->getResource()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if ($response = $this->onInit()) {
            return $response;
        }

        $form = $this->getForm();

        if ($response = $this->handleForm($form)) {
            return $response;
        }

        return $this->doRespond($form, Modal::MODAL_UPDATE);
    }

    protected function onInit(): ?Response
    {
        return null;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_update',
                'path'     => '/update',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label' => 'button.edit',
                'theme' => 'warning',
                'icon'  => 'pencil',
            ],
            'options'    => [
                'template'      => '@EkynaAdmin/Entity/Crud/update.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_default.html.twig',
                'serialization' => ['groups' => ['Default'], 'admin' => true],
            ],
        ];
    }
}
