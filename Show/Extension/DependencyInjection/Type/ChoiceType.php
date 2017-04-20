<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_replace;
use function array_search;
use function call_user_func;
use function class_exists;
use function is_array;
use function is_string;

/**
 * Class ChoiceType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ChoiceType extends AbstractType
{
    private TranslatorInterface $translator;


    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        $choices = $options['choices'];

        if (is_string($choices) && class_exists($choices)) {
            $choices = call_user_func($choices . '::getChoices');
        } elseif (is_callable($choices)) {
            $choices = call_user_func($choices);
        } elseif (!is_array($choices)) {
            throw new InvalidArgumentException('Unexpected choices.');
        }

        if ($options['multiple']) {
            if (!is_array($value)) {
                throw new InvalidArgumentException('Unexpected array value.');
            }
        } else {
            $value = [$value];
        }

        $values = [];
        foreach ($value as $val) {
            if (false !== $choice = array_search($val, $choices, true)) {
                $values[] = (string)$choice;
            }
        }

        if (empty($values)) {
            $values[] = $options['empty_label'];
        }

        $view->vars = array_replace($view->vars, [
            'multiple'            => $options['multiple'],
            'choice_trans_domain' => $options['choice_trans_domain'],
            'choice_trans_params' => $options['choice_trans_params'],
            'values'              => $values,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('choices')
            ->setDefaults([
                'multiple'            => false,
                'choice_trans_domain' => false,
                'choice_trans_params' => [],
                'empty_label'         => $this->translator->trans('value.undefined', [], 'EkynaUi'),
            ])
            ->setAllowedTypes('multiple', 'bool')
            ->setAllowedTypes('choices', ['array', 'callable', 'string'])
            ->setAllowedTypes('choice_trans_domain', ['string', 'bool', 'null'])
            ->setAllowedTypes('choice_trans_params', 'array')
            ->setAllowedTypes('empty_label', 'string')
            ->setNormalizer(
                'choice_trans_domain',
                function (Options $options, $value) {
                    if (true === $value) {
                        return null;
                    }

                    return $value;
                }
            );
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'choice';
    }
}
