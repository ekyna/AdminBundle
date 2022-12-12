<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action as RA;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Bundle\UiBundle\Form\Type\FormActionsType;
use Ekyna\Bundle\UiBundle\Form\Util\FormUtil;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class FormAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractFormAction extends RA\AbstractAction implements AdminActionInterface
{
    use RA\AuthorizationTrait;
    use RA\FactoryTrait;
    use RA\FormTrait;
    use RA\HelperTrait;
    use RA\ManagerTrait;
    use RA\SerializerTrait;
    use RA\TemplatingTrait;
    use Util\BreadcrumbTrait;
    use Util\ModalTrait;
    use FlashTrait;

    /**
     * Creates the form.
     */
    protected function getForm(array $options = []): FormInterface
    {
        $resource = $this->context->getResource();

        $options = array_replace([
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            'admin_mode'        => true,
            '_redirect_enabled' => true,
        ], $this->getFormOptions(), $options);

        if (!isset($options['action'])) {
            $options['action'] = $this->generateResourcePath($resource, static::class, $this->request->query->all());
        }

        $form = $this->createForm($this->getFormType(), $resource, $options);

        if (!$this->request->isXmlHttpRequest()) {
            $this->createFormFooter($form, [], $this->getCancelPath($options['action']));
        }

        return $form;
    }

    protected function getFormOptions(): array
    {
        return [];
    }

    /**
     * Handles the form submission.
     */
    protected function handleForm(FormInterface $form): ?Response
    {
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($response = $this->onSubmittedAndValid($form)) {
                return $response;
            }
        }

        return null;
    }

    /**
     * Handles the submitted and valid form.
     *
     * @internal
     */
    protected function onSubmittedAndValid(FormInterface $form): ?Response
    {
        if ($response = $this->onPrePersist()) {
            return $response;
        }

        $event = $this->doPersist();

        if (!$isXhr = $this->request->isXmlHttpRequest()) {
            $this->addFlashFromEvent($event);
        }

        if (!$event->hasErrors()) {
            if ($response = $this->onPostPersist()) {
                return $response;
            }

            return $this->redirect($this->getRedirectPath($form));
        }

        if ($isXhr) {
            // TODO all event messages should be bound to XHR response
            FormUtil::addErrorsFromResourceEvent($form, $event);
        }

        return null;
    }

    protected function onPrePersist(): ?Response
    {
        return null;
    }

    protected function doPersist(): ResourceEventInterface
    {
        $resource = $this->context->getResource();

        return $this->getManager()->save($resource);
    }

    protected function onPostPersist(): ?Response
    {
        if ($this->request->isXmlHttpRequest()) {
            $data = $this->buildJsonData();

            $data = $this
                ->getSerializer()
                ->normalize($data, 'json', $this->options['serialization']);

            return new JsonResponse($data);
        }

        return null;
    }

    protected function buildJsonData(): array
    {
        $resource = $this->context->getResource();
        $name = $this->context->getConfig()->getCamelCaseName();

        return [
            $name     => $resource,
            'success' => true,
        ];
    }

    protected function doRespond(FormInterface $form, string $modalType): Response
    {
        if ($this->request->isXmlHttpRequest()) {
            $modal = $this->createModal($modalType);
            $modal
                ->setForm($form->createView())
                ->setVars($this->buildParameters());

            return $this->renderModal($modal);
        }

        $this->breadcrumbFromContext($this->context);

        $parameters = $this->buildParameters([
            'form' => $form->createView(),
        ]);

        return $this
            ->render($this->options['template'], $parameters)
            ->setPrivate();
    }

    /**
     * Returns the configured form type.
     */
    protected function getFormType(): string
    {
        if (isset($this->options['type'])) {
            return $this->options['type'];
        }

        if ($type = $this->context->getConfig()->getData('form')) {
            return $type;
        }

        throw new RuntimeException('No form type configured.');
    }

    /**
     * Create the form's footer.
     */
    protected function createFormFooter(FormInterface $form, array $buttons = [], string $cancelPath = null): void
    {
        if (empty($buttons)) {
            $parent = $this->context->getParent();

            if (null === $cancelPath) {
                $referer = $this->request->headers->get('referer');
                if (!empty($referer) && !str_contains($referer, $form->getConfig()->getAction())) {
                    $cancelPath = $referer;
                } elseif ($parent) {
                    $cancelPath = $this->generateResourcePath($parent->getResource());
                } else {
                    $cancelPath = $this->generateResourcePath($this->context->getResource());
                }
            }

            if (!$parent) {
                $buttons['saveAndList'] = [
                    'type'    => Type\SubmitType::class,
                    'options' => [
                        'button_class' => 'primary',
                        'label'        => t('button.save_and_list', [], 'EkynaUi'),
                        'attr'         => ['icon' => 'list'],
                    ],
                ];
            }
            $buttons['save'] = [
                'type'    => Type\SubmitType::class,
                'options' => [
                    'button_class' => 'primary',
                    'label'        => t('button.save', [], 'EkynaUi'),
                    'attr'         => ['icon' => 'ok'],
                ],
            ];
            $buttons['cancel'] = [
                'type'    => Type\ButtonType::class,
                'options' => [
                    'label'        => t('button.cancel', [], 'EkynaUi'),
                    'button_class' => 'default',
                    'as_link'      => true,
                    'attr'         => [
                        'class' => 'form-cancel-btn',
                        'icon'  => 'remove',
                        'href'  => $cancelPath,
                    ],
                ],
            ];
        }

        $form->add('actions', FormActionsType::class, [
            'buttons' => $buttons,
        ]);
    }

    /**
     * Returns the redirect path, to redirect the user after a valid form submission.
     */
    protected function getRedirectPath(FormInterface $form): string
    {
        $actions = $form->get('actions');

        if ($actions->has('saveAndList') && $actions->get('saveAndList')->isClicked()) {
            return $this->generateResourcePath($this->context->getResource(), ListAction::class);
        }

        if (!empty($path = $form->get('_redirect')->getData())) {
            return $path;
        }

        if ($this->options['redirect_to_parent'] && ($parent = $this->context->getParentResource())) {
            return $this->generateResourcePath($parent);
        }

        return $this->generateResourcePath($this->context->getResource());
    }

    /**
     * Returns the form cancel path, for the form footer "cancel" button.
     */
    protected function getCancelPath(string $action): string
    {
        $referer = $this->request->headers->get('referer'); // TODO store referer in a hidden field.
        if (!empty($referer) && !str_contains($referer, $action)) {
            return $referer;
        }

        if ($this->options['redirect_to_parent'] && ($parent = $this->context->getParent())) {
            return $this->generateResourcePath($parent->getResource());
        }

        if ($this->context->getConfig()->hasAction(ListAction::class)) {
            return $this->generateResourcePath($this->context->getConfig()->getId(), ListAction::class);
        }

        return $this->generateResourcePath($this->context->getResource());
    }

    /**
     * Builds the template parameters.
     */
    protected function buildParameters(array $extra = []): array
    {
        $config = $this->context->getConfig();

        return array_replace([
            'context'                   => $this->context,
            $config->getCamelCaseName() => $this->context->getResource(),
            'form_template'             => $this->options['form_template'],
        ], $extra);
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'type',
                'template',
                'form_template',
                'serialization',
            ])
            ->setDefault('redirect_to_parent', true)
            ->setAllowedTypes('type', 'string')
            ->setAllowedTypes('template', 'string')
            ->setAllowedTypes('form_template', 'string')
            ->setAllowedTypes('serialization', ['string', 'array'])
            ->setAllowedTypes('redirect_to_parent', 'bool')
            ->setAllowedValues('type', function ($value) {
                if (is_null($value)) {
                    return true;
                }

                return is_subclass_of($value, FormTypeInterface::class);
            })
            ->setNormalizer('serialization', function (Options $options, $value) {
                if (is_string($value)) {
                    $value = ['groups' => [$value]];
                } elseif (is_array($value) && !isset($value['groups'])) {
                    $value = ['groups' => $value];
                }

                return array_replace([
                    'groups' => ['Default'],
                    'admin'  => true,
                ], (array)$value);
            });
    }
}
