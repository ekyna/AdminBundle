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
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

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
     * @var PropertyAccessor
     */
    protected $propertyAccessor;


    /**
     * Constructor
     *
     * @param array $locales
     * @param string $template
     */
    public function __construct(array $locales, $template = 'EkynaAdminBundle:Show:show_div_layout.html.twig')
    {
        $this->locales = $locales;
        $this->template = $template;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        // TODO Use a renderer
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

        if (!isset($options['attr'])) {
            $options['attr'] = [];
        }
        if (isset($options['id'])) {
            $options['attr']['id'] = $options['id'];
            unset($options['id']);
        }

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
        if (!isset($options['attr'])) {
            $options['attr'] = [];
        }
        if (isset($options['id'])) {
            $options['name'] = $options['id'];
            unset($options['id']);
        }

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
            'attr'    => $options['attr'],
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

        if (null !== $content) {
            $content = trim(sprintf(
                '%s %s',
                number_format($content, $options['precision'], ',', ' '),
                $options['append']
            ));
        }

        return $this->renderBlock('show_widget_simple', [
            'content' => $content,
            'attr'    => $options['attr'],
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
            'attr'    => $options['attr'],
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
            'attr'             => $options['attr'],
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
            'target'  => isset($options['target']) ? $options['target'] : '_blank', // TODO as attr
            'content' => $content,
            'attr'    => $options['attr'],
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
            'attr'    => $options['attr'],
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
            'attr'    => $options['attr'],
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

        if (isset($options['locale'])) {
            // TODO append locale to route (rename to path or url)
        }

        return $this->renderBlock('show_widget_tinymce', [
            'height' => $height, // TODO as attr ?
            'route'  => $content,
            'attr'   => $options['attr'],
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
            'attr'    => $options['attr'],
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
            'attr'    => $options['attr'],
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
            'attr'   => $options['attr'],
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
            'attr'  => $options['attr'],
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
            'attr'   => $options['attr'],
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
        $prefix = 'translation';
        if (isset($options['attr']['id'])) {
            $prefix = $options['attr']['id'];
            unset($options['attr']['id']);
        }

        return $this->renderBlock('show_widget_translations', [
            'translations' => $translations->toArray(),
            'vars'         => $this->buildTranslationsVars($options),
            'prefix'       => $prefix,
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

        $prefix = 'seo';
        if (isset($options['attr']['id'])) {
            $prefix = $options['attr']['id'];
            unset($options['attr']['id']);
        }

        return $this->renderBlock('show_widget_seo', [
            'seo'    => $seo,
            'attr'   => $options['attr'],
            'prefix' => $prefix,
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
            'attr'    => $options['attr'],
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
            'map'  => $map,
            'attr' => $options['attr'],
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
            : preg_replace('~[^A-Za-z0-9]+~', '', base64_encode(random_bytes(6))) . '_translations';

        if (!(isset($options['fields']) && is_array($options['fields']))) {
            throw new \InvalidArgumentException("The 'fields' option must be defined.");
        }

        $vars['fields'] = [];

        foreach ($options['fields'] as $property => $config) {
            if (!isset($config['label'])) {
                throw new \InvalidArgumentException("The 'label' option must be defined for the '$property' field.");
            }

            $fieldVars = [
                'label'         => $config['label'],
                'type'          => isset($config['type']) ? $config['type'] : 'text',
                'property_path' => isset($config['property_path']) ? $config['property_path'] : $property,
                'options'       => isset($config['options']) ? $config['options'] : [],
            ];

            if (isset($config['content'])) {
                $fieldVars['content'] = $config['content'];
            }

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
