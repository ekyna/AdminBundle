<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Craue\FormFlowBundle\Form\FormFlowInterface;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractCreateFlowAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCreateFlowAction extends AbstractFormAction
{
    protected FormFlowInterface $flow;

    public function __construct(FormFlowInterface $flow)
    {
        $this->flow = $flow;
    }

    public function __invoke(): Response
    {
        $resource = $this->createResource();

        $this->context->setResource($resource);

        $formAction = $this->generateResourcePath($resource, static::class, $this->request->query->all());

        $this->flow->setGenericFormOptions([
            'action'            => $formAction,
            'method'            => 'POST',
            'attr'              => ['class' => 'form-horizontal form-with-tabs'],
            '_redirect_enabled' => true,
        ]);
        $this->flow->bind($resource);

        $form = $this->flow->createForm();
        if ($this->flow->isValid($form)) {
            $this->flow->saveCurrentStepData($form);

            if ($this->flow->nextStep()) {
                $form = $this->flow->createForm();
            } elseif ($response = $this->onSubmittedAndValid($form)) {
                return $response;
            }
        }

        if ($this->request->isXmlHttpRequest()) {
            $modal = $this->createModal(Modal::MODAL_CREATE);
            $modal
                ->setForm($form->createView())
                ->setVars($this->buildParameters());

            if ($response = $this->onRenderModal($modal)) {
                return $response;
            }

            return $this->renderModal($modal);
        }

        $this->breadcrumbFromContext($this->context);

        $parameters = $this->buildParameters([
            'form' => $form->createView(),
        ]);

        return $this->render($this->options['template'], $parameters);
    }

    protected function buildParameters(array $extra = []): array
    {
        $parameters = parent::buildParameters($extra);

        $parameters['flow'] = $this->flow;

        return $parameters;
    }

    protected function getRedirectPath(FormInterface $form): string
    {
        return $this->generateResourcePath($this->context->getResource());
    }

    public static function configureAction(): array
    {
        return array_replace(CreateAction::configureAction(), [
            'name'    => static::class,
            'options' => [
                'template'      => '@EkynaAdmin/Entity/Crud/create.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_flow.html.twig',
            ],
        ]);
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined([
                'template',
                'form_template',
            ])
            ->setAllowedTypes('template', 'string')
            ->setAllowedTypes('form_template', 'string');
    }
}
