<?php

namespace Ekyna\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * Class ResourceEvent
 * @package Ekyna\Bundle\AdminBundle\Event
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceEvent extends Event implements ResourceEventInterface
{
    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var bool
     */
    protected $hard = false;

    /**
     * @var array
     */
    protected $datas = [];

    /**
     * @var array|ResourceMessage[]
     */
    protected $messages = [];

    /**
     * {@inheritdoc}
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * {@inheritdoc}
     */
    public function setHard($hard)
    {
        $this->hard = $hard;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHard()
    {
        return $this->hard;
    }

    /**
     * {@inheritdoc}
     */
    public function addData($key, $value)
    {
        $this->datas[$key] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasData($key)
    {
        return array_key_exists($key, $this->datas);
    }

    /**
     * {@inheritdoc}
     */
    public function getData($key)
    {
        if ($this->hasData($key)) {
            return $this->datas[$key];
        }
        throw new \InvalidArgumentException(sprintf('Undefined "%s" data.', $key));
    }

    /**
     * {@inheritdoc}
     */
    public function addMessages(array $messages)
    {
        foreach ($messages as $message) {
            $this->addMessage($message);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addMessage(ResourceMessage $message)
    {
        if ($message->getType() === ResourceMessage::TYPE_ERROR) {
            $this->stopPropagation();
        }
        array_push($this->messages, $message);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages($type = null)
    {
        if (null !== $type) {
            ResourceMessage::validateType($type);
            $messages = [];
            foreach ($this->messages as $message) {
                if ($message->getType() === $type) {
                    $messages[] = $message;
                }
            }
            return $messages;
        }
        return $this->messages;
    }

    /**
     * {@inheritdoc}
     */
    public function hasMessages($type = null)
    {
        if (null !== $type) {
            ResourceMessage::validateType($type);
            foreach ($this->messages as $message) {
                if ($message->getType() === $type) {
                    return true;
                }
            }
            return false;
        }
        return 0 < count($this->messages);
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrors()
    {
        return $this->hasMessages(ResourceMessage::TYPE_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return $this->getMessages(ResourceMessage::TYPE_ERROR);
    }

    /**
     * {@inheritdoc}
     */
    public function toFlashes(FlashBagInterface $flashBag)
    {
        foreach ($this->messages as $message) {
            $flashBag->add($message->getType(), $message->getMessage());
        }
    }
}
