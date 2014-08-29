<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CoreBundle\Model\ImageInterface;

/**
 * ShowExtension
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ShowExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Template
     */
    protected $template;

    /**
     * Constructor
     *
     * @param string $template
     */
    public function __construct($template = 'EkynaAdminBundle:Show:show_div_layout.html.twig')
    {
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        if (!$this->template instanceof \Twig_Template) {
            $this->template = $environment->loadTemplate($this->template);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'show_row' => new \Twig_Function_Method($this, 'renderRow', array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders a show row.
     *
     * @param mixed $content
     * @param string $type
     * @param string $label
     * @param array $options
     *
     * @return string
     */
    public function renderRow($content, $type = null, $label = null, array $options = array())
    {
        $compound = false;

        if ($type == 'checkbox') {
            $content = $this->renderCheckboxWidget($content, $options);
        } elseif ($type == 'number') {
            $content = $this->renderNumberWidget($content, $options);
        } elseif ($type == 'textarea') {
            $content = $this->renderTextareaWidget($content, $options);
        } elseif ($type == 'entity') {
            $content = $this->renderEntityWidget($content, $options);
        } elseif ($type == 'url') {
            $content = $this->renderUrlWidget($content, $options);
        } elseif ($type == 'datetime') {
            $content = $this->renderDatetimeWidget($content, $options);
        } elseif ($type == 'tinymce') {
            $content = $this->renderTinymceWidget($content, $options);
        } elseif ($type == 'image') {
            $content = $this->renderImageWidget($content, $options);
        } elseif ($type == 'images') {
            $content = $this->renderImagesWidget($content, $options);
        } else {
            $content = $this->renderSimpleWidget($content, $options);
        }

        $vars = array(
            'label' => $label !== null ? $label : false,
            'content' => $content,
            'compound' => $compound
        );

        /* Fix boostrap columns */
        $vars['label_nb_col'] = isset($options['label_nb_col']) ? intval($options['label_nb_col']) : (strlen($label) > 0 ? 2 : 0);
        $vars['nb_col'] = isset($options['nb_col']) ? intval($options['nb_col']) : 12 - $vars['label_nb_col'];

        return $this->renderBlock('show_row', $vars);
    }

    /**
     * Renders a checkbox row.
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function renderCheckboxWidget($content)
    {
        return $this->renderBlock('show_widget_checkbox', array(
            'content' => $content
        ));
    }

    /**
     * Renders a number widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderNumberWidget($content, array $options = array())
    {
        $options = array_merge(array(
            'precision' => 2,
            'append' => '',
        ), $options);

        return $this->renderBlock('show_widget_simple', array(
            'content' => trim(sprintf('%s %s', number_format($content, $options['precision'], ',', ' '), $options['append']))
        ));
    }

    /**
     * Renders a textarea widget.
     *
     * @param mixed $content
     *
     * @return string
     */
    protected function renderTextareaWidget($content)
    {
        return $this->renderBlock('show_widget_textarea', array(
            'content' => $content
        ));
    }

    /**
     * Renders an entity widget.
     *
     * @param mixed $entities
     * @param array $options
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function renderEntityWidget($entities, array $options = array())
    {
        // TODO: Test $entity
        if (!(isset($options['route']) && isset($options['field']))) {
            throw new \RuntimeException('Missing option(s) for entity widget.');
        }
        if (!isset($options['route_params_map'])) {
            $options['route_params_map'] = array('id' => 'id');
        }

        if (null !== $entities && !($entities instanceof Collection)) {
            $entities = new ArrayCollection(array($entities));
        }

        $vars = array(
            'route' => $options['route'],
            'field' => $options['field'],
            'route_params_map' => $options['route_params_map'],
            'entities' => $entities
        );

        return $this->renderBlock('show_widget_entity', $vars);
    }

    /**
     * Renders an url widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderUrlWidget($content, array $options = array())
    {
        $vars = array(
            'target' => isset($options['show_widget_url']) ? $options['show_widget_url'] : '_blank',
            'content' => $content
        );

        return $this->renderBlock('show_widget_url', $vars);
    }

    /**
     * Renders a datetime widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderDatetimeWidget($content, array $options = array())
    {
        if (!array_key_exists('time', $options)) {
            $options['time'] = true;
        }

        $vars = array(
            'content' => $content,
            'time' => $options['time'],
        );

        return $this->renderBlock('show_widget_datetime', $vars);
    }

    /**
     * Renders a tinymce widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderTinymceWidget($content, array $options = array())
    {
        $height = isset($options['height']) ? intval($options['height']) : 0;
        if (0 >= $height) {
            $height = 250;
        }
        return $this->renderBlock('show_widget_tinymce', array(
            'height' => $height,
            'route' => $content
        ));
    }

    /**
     * Renders a simple widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderSimpleWidget($content, array $options = array())
    {
        return $this->renderBlock('show_widget_simple', array(
            'content' => $content
        ));
    }

    /**
     * Renders an image widget.
     *
     * @param ImageInterface $image
     * @param array $options
     *
     * @return string
     */
    protected function renderImageWidget(ImageInterface $image = null, array $options = array())
    {
        return $this->renderBlock('show_widget_image', array(
            'image' => $image
        ));
    }

    /**
     * Renders an image "gallery" widget.
     *
     * @param Collection $images
     * @param array $options
     *
     * @return string
     */
    protected function renderImagesWidget(Collection $images, array $options = array())
    {
        return $this->renderBlock('show_widget_images', array(
            'images' => $images
        ));
    }

    /**
     * Renders a block.
     *
     * @param string $name
     * @param array $vars
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function renderBlock($name, $vars)
    {
        if (!$this->template->hasBlock($name)) {
            throw new \RuntimeException('Block "' . $name . '" not found.');
        }
        return $this->template->renderBlock($name, $vars);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_show_extension';
    }
}
