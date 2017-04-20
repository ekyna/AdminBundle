<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Fixtures;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdminProcessor
 * @package Ekyna\Bundle\AdminBundle\Service\Fixtures
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminProcessor implements ProcessorInterface
{
    private UserPasswordEncoderInterface $encoder;


    /**
     * Constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function preProcess(string $id, $object): void
    {
        if ($object instanceof UserInterface && !empty($password = $object->getPlainPassword())) {
            $object
                ->setPassword($this->encoder->encodePassword($object, $password))
                ->eraseCredentials();
        }
    }

    public function postProcess(string $id, $object): void
    {

    }
}
