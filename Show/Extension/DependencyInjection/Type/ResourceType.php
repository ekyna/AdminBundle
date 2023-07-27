<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Exception\RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function current;
use function is_array;

/**
 * Class ResourceType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceType extends AbstractType
{
    private ?PropertyAccessorInterface $accessor = null;

    public function __construct(
        private readonly ResourceHelper $helper
    ) {
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        if (null === $value) {
            $resources = [];
        } elseif (!is_array($value)) {
            $resources = [$value];
        } else {
            $resources = $value;
        }

        if (null === $view->vars['label']) {
            if (!empty($options['resource'])) {
                $config = $this->helper->getResourceConfig($options['resource']);
            } elseif (false !== $resource = current($resources)) {
                $config = $this->helper->getResourceConfig($resource);
            } else {
                throw new RuntimeException('Failed to resolve resource configuration.');
            }

            $view->vars['label'] = $config->getResourceLabel(is_array($value));
            $view->vars['label_trans_domain'] = $config->getTransDomain();
        }

        $value = [];
        foreach ($resources as $resource) {
            if (null !== $path = $options['property_path']) {
                $label = $this->getAccessor()->getValue($resource, $path);
            } else {
                $label = (string)$resource;
            }

            if ($this->helper->isGranted($options['action'], $resource)) {
                $value[$label] = $this->helper->generateResourcePath($resource, $options['action']);
            } else {
                $value[$label] = null;
            }
        }

        $view->vars['value'] = $value;
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'action'        => ReadAction::class,
                'resource'      => null,
                'property_path' => null,
            ])
            ->setAllowedTypes('action', 'string')
            ->setAllowedTypes('resource', ['null', 'string'])
            ->setAllowedTypes('property_path', ['null', 'string']);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'resource';
    }

    /**
     * Returns the property accessor.
     *
     * @return PropertyAccessorInterface
     */
    private function getAccessor(): PropertyAccessorInterface
    {
        if (null !== $this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
