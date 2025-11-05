<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action\CopierTrait;
use Ekyna\Bundle\UiBundle\Model\Modal;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use LogicException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function array_replace;
use function Symfony\Component\Translation\t;

/**
 * Class DuplicateAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DuplicateAction extends AbstractFormAction
{
    use CopierTrait;

    protected const NAME = 'admin_duplicate';

    private ?ResourceInterface $copy = null;

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

    protected function getCopy(): ResourceInterface
    {
        if (!$this->copy) {
            throw new LogicException(
                'You get can\'t access the resource copy before ' .
                'Ekyna\Bundle\AdminBundle\Action\DuplicateAction::onInit() call.'
            );
        }

        return $this->copy;
    }

    protected function onInit(): ?Response
    {
        if (null === $source = $this->context->getResource()) {
            throw new NotFoundHttpException();
        }

        $this->copy = $this->copier->copyResource($source);

        return null;
    }

    protected function onPrePersist(): ?Response
    {
        return null;
    }

    protected function doPersist(): ResourceEventInterface
    {
        return $this->getManager()->save($this->copy);
    }

    protected function onPostPersist(): ?Response
    {
        if ($this->request->isXmlHttpRequest()) {
            return parent::onPostPersist();
        }

        return $this->redirect($this->generateResourcePath($this->copy));
    }

    protected function getFormData(): ?object
    {
        return $this->copy;
    }

    protected function getFormButtons(): array
    {
        return [
            'save' => [
                'type'    => SubmitType::class,
                'options' => [
                    'button_class' => 'success',
                    'label'        => t('button.duplicate', [], 'EkynaUi'),
                    'attr'         => ['icon' => 'duplicate'],
                ],
            ]
        ];
    }

    protected function buildJsonData(): array
    {
        $name = $this->context->getConfig()->getCamelCaseName();

        return [
            $name     => $this->copy,
            'success' => true,
        ];
    }

    protected function buildParameters(array $extra = []): array
    {
        return array_replace(parent::buildParameters($extra), [
            'copy' => $this->copy,
        ]);
    }

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::CREATE,
            'route'      => [
                'name'     => 'admin_%s_duplicate',
                'path'     => '/duplicate',
                'resource' => true,
                'methods'  => ['GET', 'POST'],
            ],
            'button'     => [
                'label'        => 'button.duplicate',
                'trans_domain' => 'EkynaUi',
                'theme'        => 'default',
                'icon'         => 'duplicate',
            ],
            'options'    => [
                'template'      => '@EkynaAdmin/Entity/Crud/duplicate.html.twig',
                'form_template' => '@EkynaAdmin/Entity/Crud/_form_confirm.html.twig',
                'serialization' => ['groups' => ['Default'], 'admin' => true],
            ],
        ];
    }
}
