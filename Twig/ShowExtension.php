<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Bundle\CmsBundle\Model\SeoInterface;
use Ekyna\Bundle\CoreBundle\Model\UploadableInterface;
use Ekyna\Bundle\MediaBundle\Model\MediaInterface;
use Ivory\GoogleMap\Base\Coordinate;
use Ivory\GoogleMap\Map;
use Ivory\GoogleMap\Overlays\Marker;

/**
 * Class ShowExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShowExtension extends \Twig_Extension
{
    /**
     * @var \Twig_Template
     */
    protected $template;

    /**
     * @var array
     */
    protected $locales;


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
        return [
            new \Twig_SimpleFunction(
                'show_row',
                [$this, 'renderRow'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'show_translations_row',
                [$this, 'renderTranslationsRow'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Renders the show row.
     *
     * @param mixed  $content
     * @param string $type
     * @param string $label
     * @param array  $options
     *
     * @return string
     */
    public function renderRow($content, $type = null, $label = null, array $options = [])
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
        } elseif ($type == 'upload') {
            $content = $this->renderUploadWidget($content, $options);
        } elseif ($type == 'media') {
            $content = $this->renderMediaWidget($content, $options);
        } elseif ($type == 'medias') {
            $content = $this->renderMediasWidget($content, $options);
        } elseif ($type == 'translations') {
            $content = $this->renderTranslationsWidget($content, $options);
        } elseif ($type == 'seo') {
            $content = $this->renderSeoWidget($content, $options);
        } elseif ($type == 'key_value_collection') {
            $content = $this->renderKeyValueCollectionWidget($content, $options);
        } elseif ($type == 'coordinate') {
            $content = $this->renderCoordinateWidget($content, $options);
        } else {
            $content = $this->renderSimpleWidget($content, $options);
        }

        $vars = [
            'label'    => $label !== null ? $label : false,
            'content'  => $content,
            'compound' => $compound,
        ];

        /* Fix bootstrap columns */
        $vars['label_nb_col'] = isset($options['label_nb_col']) ? intval($options['label_nb_col']) : (strlen($label) > 0 ? 2 : 0);
        $vars['nb_col'] = isset($options['nb_col']) ? intval($options['nb_col']) : 12 - $vars['label_nb_col'];

        return $this->renderBlock('show_row', $vars);
    }

    /**
     * Renders the show translations row.
     *
     * @param Collection $translations
     * @param array      $options
     *
     * @return string
     */
    public function renderTranslationsRow(Collection $translations, array $options = [])
    {
        return $this->renderBlock('show_row_translations', [
            'translations' => $translations->toArray(),
            'vars'         => $this->buildTranslationsVars($options),
        ]);
    }

    /**
     * Renders the checkbox row.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderCheckboxWidget($content, array $options = [])
    {
        return $this->renderBlock('show_widget_checkbox', [
            'content' => $content,
        ]);
    }

    /**
     * Renders the number widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderNumberWidget($content, array $options = [])
    {
        $options = array_merge([
            'precision' => 2,
            'append'    => '',
        ], $options);

        return $this->renderBlock('show_widget_simple', [
            'content' => trim(sprintf('%s %s', number_format($content, $options['precision'], ',', ' '), $options['append'])),
        ]);
    }

    /**
     * Renders the textarea widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderTextareaWidget($content, array $options = [])
    {
        $options = array_replace([
            'html' => false,
        ], $options);

        return $this->renderBlock('show_widget_textarea', [
            'content' => $content,
            'options' => $options,
        ]);
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
    protected function renderEntityWidget($entities, array $options = [])
    {
        if (!array_key_exists('field', $options)) {
            // throw new \InvalidArgumentException('Missing "field" option for entity widget.');
            $options['field'] = null;
        }
        if (!array_key_exists('route', $options)) {
            $options['route'] = null;
        }
        if (!array_key_exists('route_params', $options)) {
            $options['route_params'] = [];
        }
        if (!array_key_exists('route_params_map', $options)) {
            $options['route_params_map'] = [];
        }

        if (null !== $entities && !($entities instanceof Collection)) {
            $entities = new ArrayCollection([$entities]);
        }

        $vars = [
            'route'            => $options['route'],
            'field'            => $options['field'],
            'route_params'     => $options['route_params'],
            'route_params_map' => $options['route_params_map'],
            'entities'         => $entities,
        ];

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
    protected function renderUrlWidget($content, array $options = [])
    {
        $vars = [
            'target'  => isset($options['target']) ? $options['target'] : '_blank',
            'content' => $content,
        ];

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
    protected function renderDatetimeWidget($content, array $options = [])
    {
        if (!array_key_exists('time', $options)) {
            $options['time'] = true;
        }
        if (!array_key_exists('date_format', $options)) {
            $options['date_format'] = 'short';
        }
        if (!array_key_exists('time_format', $options)) {
            $options['time_format'] = $options['time'] ? 'short' : 'none';
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

        $vars = [
            'content' => $content,
            'options' => $options,
        ];

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
    protected function renderColorWidget($content, array $options = [])
    {
        return $this->renderBlock('show_widget_color', [
            'content' => $content,
        ]);
    }

    /**
     * Renders a tinymce widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderTinymceWidget($content, array $options = [])
    {
        $height = isset($options['height']) ? intval($options['height']) : 0;
        if (0 >= $height) {
            $height = 250;
        }

        return $this->renderBlock('show_widget_tinymce', [
            'height' => $height,
            'route'  => $content,
        ]);
    }

    /**
     * Renders the simple widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderSimpleWidget($content, array $options = [])
    {
        return $this->renderBlock('show_widget_simple', [
            'content' => $content,
        ]);
    }

    /**
     * Renders the tel (phoneNumber) widget.
     *
     * @param mixed $content
     * @param array $options
     *
     * @return string
     */
    protected function renderTelWidget($content, array $options = [])
    {
        return $this->renderBlock('show_widget_tel', [
            'content' => $content,
        ]);
    }

    /**
     * Renders the uploadable widget.
     *
     * @param UploadableInterface $upload
     * @param array               $options
     *
     * @return string
     */
    protected function renderUploadWidget(UploadableInterface $upload = null, array $options = [])
    {
        return $this->renderBlock('show_widget_upload', [
            'upload' => $upload,
        ]);
    }

    /**
     * Renders the media widget.
     *
     * @param MediaInterface $media
     * @param array          $options
     *
     * @return string
     */
    protected function renderMediaWidget(MediaInterface $media = null, array $options = [])
    {
        return $this->renderBlock('show_widget_media', [
            'media' => $media,
        ]);
    }

    /**
     * Renders the medias widget.
     *
     * @param Collection $medias
     * @param array      $options
     *
     * @return string
     */
    protected function renderMediasWidget(Collection $medias, array $options = [])
    {
        $medias = array_map(function ($m) {
            /** @var \Ekyna\Bundle\MediaBundle\Model\MediaSubjectInterface $m */
            return $m->getMedia();
        }, $medias->toArray());

        return $this->renderBlock('show_widget_medias', [
            'medias' => $medias,
        ]);
    }

    /**
     * Renders the translations widget.
     *
     * @param Collection $translations
     * @param array      $options
     *
     * @return string
     */
    protected function renderTranslationsWidget(Collection $translations, array $options = [])
    {
        return $this->renderBlock('show_widget_translations', [
            'translations' => $translations->toArray(),
            'vars'         => $this->buildTranslationsVars($options),
        ]);
    }

    /**
     * Renders the seo widget.
     *
     * @param SeoInterface $seo
     * @param array        $options
     *
     * @return string
     */
    protected function renderSeoWidget(SeoInterface $seo = null, array $options = [])
    {
        if (null === $seo) {
            $seo = new \Ekyna\Bundle\CmsBundle\Entity\Seo;
        }

        return $this->renderBlock('show_widget_seo', [
            'seo' => $seo,
        ]);
    }

    /**
     * Renders the key value collection widget.
     *
     * @param array $content
     * @param array $options
     *
     * @return string
     */
    protected function renderKeyValueCollectionWidget(array $content, array $options = [])
    {
        return $this->renderBlock('show_widget_key_value_collection', [
            'content' => $content,
        ]);
    }

    /**
     * Renders the coordinate widget.
     *
     * @param Coordinate $coordinate
     * @param array      $options
     *
     * @return string
     */
    protected function renderCoordinateWidget(Coordinate $coordinate = null, array $options = [])
    {
        $map = new Map();
        $map->setAutoZoom(true);
        $map->setMapOptions([
            'minZoom'          => 3,
            'maxZoom'          => 18,
            'disableDefaultUI' => true,
        ]);
        $map->setStylesheetOptions([
            'width'  => '100%',
            'height' => '320px',
        ]);

        /** @var \Ivory\GoogleMap\Base\Coordinate $coordinate */
        if (null !== $coordinate && null !== $coordinate->getLatitude() && null !== $coordinate->getLongitude()) {
            $marker = new Marker();
            $marker->setPosition($coordinate);
            $map->addMarker($marker);
        }

        return $this->renderBlock('show_widget_coordinate', [
            'map' => $map,
        ]);
    }

    /**
     * Renders a block.
     *
     * @param string $name
     * @param array  $vars
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
     * Builds the translations widget vars.
     *
     * @param array $options
     *
     * @return array
     */
    private function buildTranslationsVars(array $options)
    {
        $vars = ['locales' => $this->locales];

        $vars['name'] = isset($options['name'])
            ? $options['name']
            : strtr(base64_encode(random_bytes(4)), '+/=', '');

        if (!(isset($options['fields']) && is_array($options['fields']))) {
            throw new \InvalidArgumentException("The 'fields' option must be defined.");
        }

        $vars['fields'] = [];

        foreach ($options['fields'] as $property => $config) {
            if (!isset($config['label'])) {
                throw new \InvalidArgumentException("The 'label' option must be defined for the '$property' field.");
            }

            $fieldVars = ['label' => $config['label']];

            if (isset($config['style'])) {
                if (!in_array($config['style'], ['inline', 'block'], true)) {
                    throw new \InvalidArgumentException(
                        "The 'style' option is not valid for the '$property' field (expected 'inline' or 'block)."
                    );
                }
                $fieldVars['style'] = $config['style'];
            } else {
                $fieldVars['style'] = 'inline';
            }

            // TODO fields label / widget col

            $vars['fields'][$property] = $fieldVars;
        }

        return $vars;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_show_extension';
    }
}
