<?php

namespace Ekyna\Bundle\AdminBundle\Show;

use Ekyna\Bundle\AdminBundle\Show\Exception\RuntimeException;

/**
 * Class Renderer
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Renderer implements RendererInterface
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var \Twig_Environment
     */
    private $environment;

    /**
     * @var \Twig_Template[]
     */
    private $templates = [];

    /**
     * @var array
     */
    private $blocks = [];


    /**
     * Constructor.
     *
     * @param RegistryInterface $registry
     * @param \Twig_Environment $environment
     */
    public function __construct(RegistryInterface $registry, \Twig_Environment $environment)
    {
        $this->registry = $registry;
        $this->environment = $environment;
    }

    /**
     * @inheritDoc
     */
    public function renderRow($data, $name = 'text', array $options = [])
    {
        $view = $this->buildView($data, $name, $options);

        $block = $view->vars['row_prefix'] . '_row';
        $template = $this->getTemplateForBlock($block);

        /** @noinspection PhpInternalEntityUsedInspection */
        return $template->renderBlock($block, array_replace($view->vars, ['view' => $view]));
    }

    /**
     * @inheritDoc
     */
    public function renderWidget($data, $name = 'text', array $options = [])
    {
        $view = $this->buildView($data, $name, $options);

        $block = $view->vars['widget_prefix'] . '_widget';
        $template = $this->getTemplateForBlock($block);

        /** @noinspection PhpInternalEntityUsedInspection */
        return $template->renderBlock($block, $view->vars);
    }

    /**
     * Builds the view.
     *
     * @param mixed  $data    The data to render
     * @param string $name    The type name
     * @param array  $options The unresolved options
     *
     * @return View
     */
    private function buildView($data, $name, $options)
    {
        if ($data instanceof View) {
            return $data;
        }

        $type = $this->registry->getType($name);

        $view = new View();

        $options = $type->resolveOptions($options);

        $type->build($view, $data, $options);

        return $view;
    }

    /**
     * Returns the template by its name.
     *
     * @param string $name
     *
     * @return \Twig_Template
     */
    private function getTemplate($name)
    {
        if (isset($this->templates[$name])) {
            return $this->templates[$name];
        }

        /** @var \Twig_Template $template */
        /** @noinspection PhpInternalEntityUsedInspection */
        $template = $this->environment->loadTemplate($name);

        return $this->templates[$name] = $template;
    }

    /**
     * Returns the template fot he given row block name.
     *
     * @param string $block
     *
     * @return \Twig_Template
     */
    private function getTemplateForBlock($block)
    {
        if (isset($this->blocks[$block])) {
            return $this->getTemplate($this->blocks[$block]);
        }

        foreach ($this->registry->getTemplates() as $templateName) {
            $template = $this->getTemplate($templateName);

            /** @noinspection PhpInternalEntityUsedInspection */
            if ($template->hasBlock($block)) {
                $this->blocks[$block] = $templateName;

                return $template;
            }
        }

        throw new RuntimeException("No template found for block '$block'.");
    }
}