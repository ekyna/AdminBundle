<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Action\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CreateAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreateAction extends AbstractFormAction
{
    protected const NAME = 'admin_create';

    public function __invoke(): Response
    {
        $resource = $this->createResource();

        $this->context->setResource($resource);

        if ($response = $this->onInit()) {
            return $response;
        }

        $form = $this->getForm();

        if ($response = $this->handleForm($form)) {
            return $response;
        }

        return $this->doRespond($form, Modal::MODAL_CREATE);
    }

    protected function onInit(): ?Response
    {
        return null;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::CREATE,
            'route'      => [
                'name'    => 'admin_%s_create',
                'path'    => '/create',
                'methods' => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.new',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'success',
                'icon'         => 'plus',
            ],
            'options'    => [
                'template'      => '@EkynaAdmin/Entity/Crud/create.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_default.html.twig',
                'serialization' => ['groups' => ['Default'], 'admin' => true],
            ],
        ];
    }
}
