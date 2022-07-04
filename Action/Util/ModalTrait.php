<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Util;

use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Bundle\UiBundle\Service\Modal\ModalRenderer;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\HttpFoundation\Response;

use function in_array;
use function sprintf;

/**
 * Trait ModalTrait
 * @package Ekyna\Bundle\AdminBundle\Action\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Context $context
 */
trait ModalTrait
{
    private ModalRenderer $renderer;


    /**
     * @required
     */
    public function setRenderer(ModalRenderer $renderer): void
    {
        $this->renderer = $renderer;
    }

    /**
     * Creates a modal instance.
     */
    protected function createModal(string $action, string $title = null, ResourceInterface $resource = null): Modal
    {
        if (empty($title) && $this->context) {
            $prefix = $this->context->getConfig()->getTransPrefix();
            $title = sprintf('%s.header.%s', $prefix, $action);
        }

        if ($resource && in_array($action, [Modal::MODAL_UPDATE, Modal::MODAL_DELETE])) {
            $title = $this->renderer->getTranslator()->trans($title, ['%name%' => (string)$resource]);
        }

        $modal = new Modal($title);

        $buttons = [];

        if (in_array($action, [
            Modal::MODAL_CREATE,
            Modal::MODAL_UPDATE,
            Modal::MODAL_DELETE,
            Modal::MODAL_CONFIRM,
        ])) {
            $submitButton = Modal::BTN_SUBMIT;

            if ($action === Modal::MODAL_UPDATE) {
                $submitButton['cssClass'] = 'btn-warning';
            } elseif ($action === Modal::MODAL_DELETE) {
                $submitButton['label'] = 'button.remove';
                $submitButton['icon'] = 'glyphicon glyphicon-trash';
                $submitButton['cssClass'] = 'btn-danger';
            } elseif ($action === Modal::MODAL_CONFIRM) {
                $submitButton['label'] = 'button.confirm';
                $submitButton['cssClass'] = 'btn-danger';
            }

            $buttons[] = $submitButton;
        }

        $buttons[] = Modal::BTN_CLOSE;

        $modal->setButtons($buttons);

        return $modal;
    }

    /**
     * Renders the modal.
     */
    protected function renderModal(Modal $modal): Response
    {
        if ($response = $this->onRenderModal($modal)) {
            return $response;
        }

        return $this->renderer->render($modal);
    }

    protected function onRenderModal(Modal $modal): ?Response
    {
        return null;
    }
}
