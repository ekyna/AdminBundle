<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Pool\Configuration;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * ResourceController
 */
class ResourceController extends Controller
{
    /**
     * @var Configuration
     */
    protected $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function homeAction(Request $request)
    {
        return $this->redirect($this->generateUrl($this->configuration->getRoute('list')));
    }

    public function listAction(Request $request)
    {
        $this->isGranted('VIEW');

        $table = $this->get('table.factory')
            ->createBuilder($this->configuration->getTableType())
            ->getTable($this->configuration->getId());

        $resources = $this->get('table.generator')->generateView($table);

        return $this->render(
            $this->configuration->getTemplate('list.html'),
            array(
                $this->getResourcePluralName() => $resources
            )
        );
        
        return $this->render(
	        $this->configuration->getTemplate('list.html')
        );
    }

    public function newAction(Request $request)
    {
        $this->isGranted('CREATE');

        $resource = $this->createNew();

        $resourceName = $this->getResourceName();
        $form = $this->createForm($this->configuration->getFormType(), $resource);

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $this->persist($resource);

            $this->addFlash('La resource a été créé avec succès.', 'success');

            return $this->redirect(
                $this->generateUrl(
                    $this->configuration->getRoute('show'),
                    array(
                        sprintf('%sId', $resourceName) => $resource->getId()
                    )
                )
            );
        }

        return $this->render(
            $this->configuration->getTemplate('new.html'),
            array(
                $resourceName => $resource,
                'form' => $form->createView()
            )
        );
    }

    public function showAction(Request $request)
    {
        $resource = $this->findResourceOrThrowException();

        $this->isGranted('VIEW', $resource);

        $resourceName = $this->getResourceName();

        return $this->render(
            $this->configuration->getTemplate('show.html'),
            array(
                $resourceName => $resource
            )
        );
    }

    public function editAction(Request $request)
    {
        $resource = $this->findResourceOrThrowException();

        $this->isGranted('EDIT', $resource);

        $resourceName = $this->getResourceName();
        $form = $this->createForm($this->configuration->getFormType(), $resource);

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $this->persist($resource);

            $this->addFlash('La resource a été modifiée avec succès.', 'success');

            return $this->redirect(
                $this->generateUrl(
                    $this->configuration->getRoute('show'),
                    array(
                        sprintf('%sId', $resourceName) => $resource->getId()
                    )
                )
            );
        }

        return $this->render(
            $this->configuration->getTemplate('edit.html'),
            array(
                $resourceName => $resource,
                'form' => $form->createView()
            )
        );
    }

    public function removeAction(Request $request)
    {
        $resource = $this->findResourceOrThrowException();

        $this->isGranted('DELETE', $resource);

        $resourceName = $this->getResourceName();
        $form = $this->createConfirmationForm();

        $form->handleRequest($this->getRequest());
        if ($form->isValid()) {
            $this->remove($resource);

            $this->addFlash('La resource a été supprimée avec succès.', 'success');

            return $this->redirect(
                $this->generateUrl(
                    $this->configuration->getRoute('list')
                )
            );
        }

        return $this->render(
            $this->configuration->getTemplate('remove.html'),
            array(
                $resourceName => $resource,
                'form' => $form->createView()
            )
        );
    }

    protected function getResourceName()
    {
        return $this->configuration->getResourceName();
    }

    protected function getResourcePluralName()
    {
        return Inflector::pluralize($this->configuration->getResourceName());
    }

    protected function findResourceOrThrowException($id = null)
    {
        if(null === $id) {
            $id = $this->getRequest()->attributes->get(sprintf('%sId', $this->configuration->getResourceName()));
        }
        if(null === $resource = $this->getRepository()->findOneBy(array('id' => $id))) {
            throw new NotFoundHttpException('Resource introuvable');
        }
        return $resource;
    }

    /**
     * Checks if the attributes are granted against the current token.
     *
     * @param mixed      $attributes
     * @param mixed|null $object
     * 
     * @throws AuthenticationCredentialsNotFoundException when the security context has no authentication token.
     *
     * @return Boolean
     */
    protected function isGranted($attributes, $object = null, $throwException = true)
    {
        if(is_null($object)) {
            $object = $this->configuration->getObjectIdentity();
        }else{
            $object = $this->get('ekyna_admin.pool_registry')->getObjectIdentity($object);
        }
        if(!$this->get('security.context')->isGranted($attributes, $object)) {
            if($throwException) {
                throw new AccessDeniedHttpException('You are not allowed to view this resource.');
            }
            return false;
        }
        return true;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getManager()
    {
        return $this->get($this->configuration->getServiceKey('manager'));
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository()
    {
        return $this->get($this->configuration->getServiceKey('repository'));
    }

    protected function createNew()
    {
        $class = $this->configuration->getResourceClass();
        return new $class;
    }

    protected function persist($resource)
    {
        $em = $this->getManager();
        $em->persist($resource);
        $em->flush();
    }

    protected function remove($resource)
    {
        $em = $this->getManager();
        $em->remove($resource);
        $em->flush();
    }

    protected function createConfirmationForm($message = null)
    {
        if(null === $message) {
            $message = 'Confirmer la suppression ?';
        }

        return $this->createFormBuilder()
            ->add('confirm', 'checkbox', array(
                'label' => $message,
                'attr' => array('align_with_widget' => true),
                'required' => true
            ))
            ->getForm()
        ;
    }

    protected function addFlash($message, $type = 'info')
    {
        $this->get('session')->getFlashBag()->add($type, $message);
    }
}
