<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\CoreBundle\Twig\UiExtension;

/**
 * Class AdminExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends \Twig_Extension
{
    /**
     * @var ResourceHelper
     */
    private $helper;

    /**
     * @var string
     */
    private $logoPath;

    /**
     * @var UiExtension
     */
    private $ui;

    /**
     * Constructor.
     *
     * @param ResourceHelper $helper
     * @param UiExtension    $ui
     * @param string         $logoPath
     */
    public function __construct(
        ResourceHelper $helper,
        UiExtension $ui,
        $logoPath
    ) {
        $this->helper = $helper;
        $this->ui = $ui;
        $this->logoPath = $logoPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('admin_resource_btn',    [$this, 'renderResourceButton'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('admin_resource_access', [$this, 'hasResourceAccess']),
            new \Twig_SimpleFunction('admin_resource_path',   [$this, 'generateResourcePath']),
        ];
    }

    /**
     * Renders a resource action button.
     *
     * @param mixed $resource
     * @param string $action
     * @param array  $options
     * @param array  $attributes
     *
     * @return string
     */
    public function renderResourceButton($resource, $action = 'view', array $options = [], array $attributes = [])
    {
        if ($this->helper->isGranted($resource, $action)) {
            $options = array_merge($this->getButtonOptions($action), $options);

            $label = null;
            if (array_key_exists('label', $options)) {
                $label = $options['label'];
                unset($options['label']);
            } elseif (array_key_exists('short', $options)) {
                if ($options['short']) {
                    $label = 'ekyna_core.button.' . $action;
                }
                unset($options['short']);
            }
            if (null === $label) {
                $config = $this->helper->getRegistry()->findConfiguration($resource);
                $label = sprintf('%s.button.%s', $config->getId(), $action);
            }

            if (!array_key_exists('path', $options)) {
                $options['path'] = $this->helper->generateResourcePath($resource, $action);
            }
            if (!array_key_exists('type', $options)) {
                $options['type'] = 'link';
            }

            return $this->ui->renderButton(
                $label,
                $options,
                $attributes
            );
        }

        return '';
    }

    /**
     * Returns whether the user has access granted or not on the given resource for the given action.
     *
     * @param mixed $resource
     * @param string $action
     *
     * @return bool
     */
    public function hasResourceAccess($resource, $action = 'view')
    {
        return $this->helper->isGranted($resource, $action);
    }

    /**
     * Returns the resource path.
     *
     * @param mixed $resource
     * @param string $action
     *
     * @return string
     */
    public function generateResourcePath($resource, $action = 'view')
    {
        return $this->helper->generateResourcePath($resource, $action);
    }

    /**
     * Returns the default button options for the given action.
     *
     * @param string $action
     *
     * @return array
     */
    private function getButtonOptions($action)
    {
        if ($action == 'new') {
            return [
                'theme' => 'primary',
                'icon' => 'plus',
            ];
        } elseif ($action == 'edit') {
            return [
                'theme' => 'warning',
                'icon' => 'pencil',
            ];
        } elseif ($action == 'remove') {
            return [
                'theme' => 'danger',
                'icon' => 'trash',
            ];
        } elseif ($action == 'show') {
            return [
                'icon' => 'eye-open',
            ];
        } elseif ($action == 'list') {
            return [
                'icon' => 'list',
            ];
        }
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobals()
    {
        return [
            'ekyna_admin_logo_path' => $this->logoPath,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ekyna_admin';
    }
}
