<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Entity\Gallery;
use Ekyna\Bundle\CmsBundle\Model\SeoInterface;
use Ekyna\Bundle\CoreBundle\Model\ImageInterface;

/**
 * Class ShowExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
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
            new \Twig_SimpleFunction('show_row', array($this, 'renderRow'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders the show row.
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
        } elseif ($type == 'datetime' || $type == 'date') {
            if ($type == 'date') {
                $options['time'] = false;
            }
            $content = $this->renderDatetimeWidget($content, $options);
        } elseif ($type == 'tel') {
            $content = $this->renderTelWidget($content, $options);
        } elseif ($type == 'color') {
            $content = $this->renderColorWidget($content, $options);
        } elseif ($type == 'tinymce') {
            $content = $this->renderTinymceWidget($content, $options);
        } elseif ($type == 'image') {
            $content = $this->renderImageWidget($content, $options);
        } elseif ($type == 'gallery') {
            $content = $this->renderGalleryWidget($content, $options);
        } elseif ($type == 'images') {
            $content = $this->renderImagesWidget($content, $options);
        } elseif ($type == 'seo') {
            $content = $this->renderSeoWidget($content, $options);
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
     * Renders the checkbox row.
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
     * Renders the number widget.
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
     * Renders the textarea widget.
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
     * Renders the entity widget.
     *
     * @param mixed $entities
     * @param array $options
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function renderEntityWidget($entities, array $options = array())
    {
        if (!array_key_exists('field', $options)) {
            // throw new \InvalidArgumentException('Missing "field" option for entity widget.');
            $options['field'] = null;
        }
        if (!array_key_exists('route', $options)) {
            $options['route'] = null;
        }
        if (!array_key_exists('route_params', $options)) {
            $options['route_params'] = array();
        }
        if (!array_key_exists('route_params_map', $options)) {
            $options['route_params_map'] = array('id' => 'id');
        }

        if (null !== $entities && !($entities instanceof Collection)) {
            $entities = new ArrayCollection(array($entities));
        }

        $vars = array(
            'route' => $options['route'],
            'field' => $options['field'],
            'route_params'     => $options['route_params'],
            'route_params_map' => $options['route_params_map'],
            'entities' => $entities
        );

        return $this->renderBlock('show_widget_entity', $vars);
    }

    /**
     * Renders the url widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderUrlWidget($content, array $options = array())
    {
        $vars = array(
            'target' => isset($options['target']) ? $options['target'] : '_blank',
            'content' => $content
        );

        return $this->renderBlock('show_widget_url', $vars);
    }

    /**
     * Renders the datetime widget.
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
        if (!array_key_exists('date_format', $options)) {
            $options['date_format'] = 'medium';
        }
        if (!array_key_exists('time_format', $options)) {
            $options['time_format'] = $options['time'] ? 'medium' : 'none';
        }
        if (!array_key_exists('locale', $options)) {
            $options['locale'] = null;
        }
        if (!array_key_exists('timezone', $options)) {
            $options['timezone'] = null;
        }
        if (!array_key_exists('format', $options)) {
            $options['format'] = '';
        }

        $vars = array(
            'content' => $content,
            'options' => $options,
        );

        return $this->renderBlock('show_widget_datetime', $vars);
    }

    /**
     * Renders the color widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderColorWidget($content, array $options = array())
    {
        return $this->renderBlock('show_widget_color', array(
            'content' => $content
        ));
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
     * Renders the simple widget.
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
     * Renders the tel (phoneNumber) widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderTelWidget($content, array $options = array())
    {
        return $this->renderBlock('show_widget_tel', array(
            'content' => $content
        ));
    }

    /**
     * Renders the image widget.
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
     * Renders the cms gallery widget.
     *
     * @param Gallery $gallery
     * @param array $options
     *
     * @return string
     */
    protected function renderGalleryWidget(Gallery $gallery = null, array $options = array())
    {
        return $this->renderBlock('show_widget_images', array(
            'images' => null !== $gallery ? $gallery->getImages() : [],
        ));
    }

    /**
     * Renders the image "gallery" widget.
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
     * Renders the seo widget.
     *
     * @param SeoInterface $seo
     * @param array $options
     *
     * @return string
     */
    protected function renderSeoWidget(SeoInterface $seo, array $options = array())
    {
        return $this->renderBlock('show_widget_seo', array(
            'seo' => $seo
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
