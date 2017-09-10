<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Entity\UserPin;
use Ekyna\Bundle\CoreBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class PinController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PinController extends Controller
{
    /**
     * Remove action.
     *
     * @param int $id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function removeAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        if (null !== $pin = $em->getRepository(UserPin::class)->find($id)) {
            $em->remove($pin);
            $em->flush();
        }

        return new JsonResponse(['success' => true]);
    }

    /**
     * Resource pin action.
     *
     * @param string $name
     * @param string $identifier
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourcePinAction($name, $identifier)
    {
        if (null === $resource = $this->findResource($name, $identifier)) {
            throw $this->createNotFoundException('Resource not found.');
        }

        $pin = $this->get('ekyna_admin.helper.pin_helper')->pinResource($resource);

        return new JsonResponse([
            'added' => $pin->toArray(),
        ]);
    }

    /**
     * Resource unpin action.
     *
     * @param string $name
     * @param string $identifier
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function resourceUnpinAction($name, $identifier)
    {
        if (null === $resource = $this->findResource($name, $identifier)) {
            throw $this->createNotFoundException('Resource not found.');
        }

        $data = [];

        if (null !== $pin = $this->get('ekyna_admin.helper.pin_helper')->unpinResource($resource)) {
            $data['removed'] = $pin->toArray();
        }

        return new JsonResponse($data);
    }

    /**
     * Finds the resource.
     *
     * @param string $name
     * @param string $identifier
     *
     * @return \Ekyna\Component\Resource\Model\ResourceInterface|null
     */
    private function findResource($name, $identifier)
    {
        $config = $this->get('ekyna_resource.configuration_registry')->findConfiguration($name);

        /** @var \Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface $repository */
        $repository = $this->get($config->getServiceKey('repository'));

        return $repository->find($identifier);
    }
}
