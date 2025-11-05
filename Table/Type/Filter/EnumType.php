<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Filter;

use Ekyna\Bundle\ResourceBundle\Form\EnumChoiceHelper;
use Ekyna\Component\Table\Extension\Core\Type\Filter\ChoiceType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class EnumType
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Filter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EnumType extends AbstractFilterType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        (new EnumChoiceHelper($this->translator))->configureOptions($resolver);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
