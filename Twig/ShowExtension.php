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
        if (! $this->template instanceof \Twig_Template) {
            $this->template = $environment->loadTemplate($this->template);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'show_row' => new \Twig_Function_Method($this, 'renderRow', array(
                'is_safe' => array(
                    'html'
                )
            ))
        );
    }

    public function renderRow($content, $type = null, $label = null, array $options = array())
    {
        $compound = false;

        if ($type == 'checkbox') {
            $content = $this->renderCheckboxWidget($content, $options);
        } elseif ($type == 'textarea') {
            $content = $this->renderTextareaWidget($content, $options);
        } elseif ($type == 'entity') {
            $content = $this->renderEntityWidget($content, $options);
        } elseif ($type == 'url') {
            $content = $this->renderUrlWidget($content, $options);
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

    protected function renderCheckboxWidget($content)
    {
        return $this->renderBlock('show_widget_checkbox', array(
            'content' => $content
        ));
    }

    protected function renderTextareaWidget($content)
    {
        return $this->renderBlock('show_widget_textarea', array(
            'content' => $content
        ));
    }

    protected function renderEntityWidget($entities, array $options = array())
    {
        // TODO: Test $entity
        if (! (isset($options['route']) && isset($options['field']))) {
            throw new \RuntimeException('Missing option(s) for entity widget.');
        }

        if (! ($entities instanceof Collection)) {
            $entities = new ArrayCollection(array(
                $entities
            ));
        }

        $vars = array(
            'route' => $options['route'],
            'field' => $options['field'],
            'entities' => $entities
        );

        return $this->renderBlock('show_widget_entity', $vars);
    }

    protected function renderUrlWidget($content, array $options = array())
    {
        $vars = array(
            'target' => isset($options['show_widget_url']) ? $options['show_widget_url'] : '_blank',
            'content' => $content
        );

        return $this->renderBlock('show_widget_url', $vars);
    }

    protected function renderTinymceWidget($content, array $options = array())
    {
        return $this->renderBlock('show_widget_tinymce', array(
            'content' => $content
        ));
    }

    protected function renderSimpleWidget($content, array $options = array())
    {
        return $this->renderBlock('show_widget_simple', array(
            'content' => $content
        ));
    }

    protected function renderImageWidget(ImageInterface $image = null, array $options = array())
    {
        return $this->renderBlock('show_widget_image', array(
            'image' => $image
        ));
    }

    protected function renderImagesWidget(Collection $images, array $options = array())
    {
        return $this->renderBlock('show_widget_images', array(
            'images' => $images
        ));
    }

    protected function renderBlock($name, $vars)
    {
        if (! $this->template->hasBlock($name)) {
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
