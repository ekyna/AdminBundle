<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Entity\UserPin;
use Ekyna\Bundle\AdminBundle\Helper\PinHelper;
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

        $data = [];

        if (null !== $pin = $em->getRepository(UserPin::class)->find($id)) {
            $data['removed'] = $pin->toArray();

            $em->remove($pin);
            $em->flush();
        }

        return new JsonResponse($data);
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

        $pinData = $this->get(PinHelper::class)->pinResource($resource);

        return new JsonResponse([
            'added' => $pinData,
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

        if (null !== $pinData = $this->get(PinHelper::class)->unpinResource($resource)) {
            $data['removed'] = $pinData;
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

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $repository->find($identifier);
    }
}
