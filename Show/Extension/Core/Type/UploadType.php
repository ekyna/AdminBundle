<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\UnexpectedTypeException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Component\Resource\Model\UploadableInterface;
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
    public function build(View $view, $value, array $options = []): void
    {
        if ($value && !$value instanceof UploadableInterface) {
            throw new UnexpectedTypeException($value, UploadableInterface::class);
        }

        parent::build($view, $value, $options);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
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
    public static function getName(): string
    {
        return 'tinymce';
    }
}
