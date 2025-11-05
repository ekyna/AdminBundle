<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\UiBundle\Form\Type\ConfirmType;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class AbstractConfirmAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractConfirmAction extends AbstractFormAction
{
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

    protected function getFormData(): ?object
    {
        return null;
    }

    protected function getFormOptions(): array
    {
        return array_replace(parent::getFormOptions(), [
            'attr'    => ['class' => 'form-horizontal'],
            'buttons' => false,
        ]);
    }

    protected function getFormButtons(string $cancelPath = null): array
    {
        return [
            'submit' => [
                'type'    => Type\SubmitType::class,
                'options' => [
                    'button_class' => 'success',
                    'label'        => t('button.confirm', [], 'EkynaUi'),
                    'attr'         => ['icon' => 'ok'],
                ],
            ],
        ];
    }

    protected function getRedirectPath(FormInterface $form): string
    {
        if (!empty($path = $form->get('_redirect')->getData())) {
            return $path;
        }

        if ($parent = $this->context->getParentResource()) {
            return $this->generateResourcePath($parent);
        }

        return $this->generateResourcePath($this->context->getResource(), $this->getRedirectAction());
    }

    protected function getRedirectAction(): string
    {
        return ListAction::class;
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('type', ConfirmType::class);
    }
}
