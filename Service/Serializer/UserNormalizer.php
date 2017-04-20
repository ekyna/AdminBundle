<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Serializer;

use Ekyna\Bundle\AdminBundle\Model;
use Ekyna\Component\Resource\Bridge\Symfony\Serializer\ResourceNormalizer;

/**
 * Class UserNormalizer
 * @package Ekyna\Bundle\AdminBundle\Service\Serializer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserNormalizer extends ResourceNormalizer
{
    /**
     * @inheritDoc
     *
     * @param Model\UserInterface $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = parent::normalize($object, $format, $context);

        if ($this->contextHasGroup(['Default', 'User', 'Search'], $context)) {
            $data = array_replace([
                'email'      => $object->getEmail(),
                'first_name' => $object->getFirstName(),
                'last_name'  => $object->getLastName(),
                'group'      => $object->getGroup()->getId(),
            ], $data);
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        //$resource = parent::denormalize($data, $class, $format, $context);

        throw new \Exception('Not yet implemented');
    }
}
