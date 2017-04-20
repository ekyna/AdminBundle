<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DeleteAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DeleteAction extends AbstractFormAction
{
    protected const NAME = 'admin_delete';

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

        return $this
            ->doRespond($form, Modal::MODAL_DELETE)
            ->setPrivate();
    }

    protected function onInit(): ?Response
    {
        return null;
    }

    protected function doPersist(): ResourceEventInterface
    {
        $resource = $this->context->getResource();

        return $this->getManager()->delete($resource);
    }

    protected function buildJsonData(): array
    {
        return [
            'success' => true,
        ];
    }

    protected function createModal(string $action, string $title = null, ResourceInterface $resource = null): Modal
    {
        $modal = parent::createModal($action, $title, $resource);

        return $modal->setSize(Modal::SIZE_NORMAL);
    }

    protected function getForm(array $options = []): FormInterface
    {
        $resource = $this->context->getResource();

        if (!isset($options['action'])) {
            $options['action'] = $this->generateResourcePath($resource, static::class, $this->request->query->all());
        }

        if (!isset($options['cancel_path'])) {
            $options['cancel_path'] = $this->getCancelPath($options['action']);
        }

        return $this->createForm(ConfirmType::class, null, array_replace([
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal'],
            'admin_mode'        => true,
            'buttons'           => !$this->request->isXmlHttpRequest(),
            '_redirect_enabled' => true,
        ], $options));
    }

    protected function getRedirectPath(FormInterface $form): string
    {
        if (!empty($path = $form->get('_redirect')->getData())) {
            return $path;
        }

        if ($parent = $this->context->getParentResource()) {
            return $this->generateResourcePath($parent);
        }

        return $this->generateResourcePath($this->context->getResource(), ListAction::class);
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
