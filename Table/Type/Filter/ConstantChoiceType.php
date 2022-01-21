<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Filter;

use Ekyna\Bundle\ResourceBundle\Form\ConstantChoiceTypeHelper;
use Ekyna\Component\Table\Extension\Core\Type\Filter\ChoiceType;
use Ekyna\Component\Table\Filter\AbstractFilterType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ConstantChoiceType
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Filter
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConstantChoiceType extends AbstractFilterType
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        (new ConstantChoiceTypeHelper($this->translator))->configureOptions($resolver);
    }

    public function getParent(): ?string
    {
        return ChoiceType::class;
    }
}
