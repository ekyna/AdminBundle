<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Controller;

use Ekyna\Bundle\AdminBundle\Entity\UserPin;
use Ekyna\Bundle\AdminBundle\Service\Pin\PinHelper;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PinController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PinController
{
    private PinHelper                  $pinHelper;
    private ResourceRegistryInterface  $resourceRegistry;
    private RepositoryFactoryInterface $repositoryFactory;


    /**
     * Constructor.
     *
     * @param PinHelper                  $pinHelper
     * @param ResourceRegistryInterface  $resourceRegistry
     * @param RepositoryFactoryInterface $repositoryFactory
     */
    public function __construct(
        PinHelper $pinHelper,
        ResourceRegistryInterface $resourceRegistry,
        RepositoryFactoryInterface $repositoryFactory
    ) {
        $this->pinHelper = $pinHelper;
        $this->resourceRegistry = $resourceRegistry;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Remove action.
     *
     * @param int $id
     *
     * @return Response
     */
    public function remove(int $id): Response
    {
        $data = [];

        $userPin = $this->pinHelper->getRepository()->find($id);
        if ($userPin instanceof UserPin) {
            $data['removed'] = $userPin->toArray();

            $this->pinHelper->removePin($userPin);
        }

        return new JsonResponse($data);
    }

    /**
     * Resource pin action.
     *
     * @param string $name
     * @param int    $identifier
     *
     * @return Response
     */
    public function resourcePin(string $name, int $identifier): Response
    {
        if (null === $resource = $this->findResource($name, $identifier)) {
            throw new NotFoundHttpException('Resource not found');
        }

        $pinData = $this->pinHelper->pinResource($resource);

        return new JsonResponse([
            'added' => $pinData,
        ]);
    }

    /**
     * Resource unpin action.
     *
     * @param string $name
     * @param int    $identifier
     *
     * @return Response
     */
    public function resourceUnpin(string $name, int $identifier): Response
    {
        if (null === $resource = $this->findResource($name, $identifier)) {
            throw new NotFoundHttpException('Resource not found');
        }

        $data = [];

        if (null !== $pinData = $this->pinHelper->unpinResource($resource)) {
            $data['removed'] = $pinData;
        }

        return new JsonResponse($data);
    }

    /**
     * Finds the resource.
     *
     * @param string $name
     * @param int    $id
     *
     * @return ResourceInterface|null
     */
    private function findResource(string $name, int $id): ?ResourceInterface
    {
        $config = $this->resourceRegistry->find($name);

        $repository = $this->repositoryFactory->getRepository($config->getEntityClass());

        return $repository->find($id);
    }
}
