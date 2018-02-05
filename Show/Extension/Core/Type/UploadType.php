<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Bundle\CoreBundle\Model\UploadableInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UploadType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UploadType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        if ($value && !$value instanceof UploadableInterface) {
            throw new \InvalidArgumentException("Expected instance of " . UploadableInterface::class);
        }

        parent::build($view, $value, $options);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'height' => 250,
                'route'  => null,
                'locale' => null,
            ])
            ->setAllowedTypes('height', 'int')
            ->setAllowedTypes('route', 'string')
            ->setAllowedTypes('locale', ['null', 'string']);
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'tinymce';
    }
}