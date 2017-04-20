<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action as RA;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Exception\LogicException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SummaryAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SummaryAction extends RA\AbstractAction implements AdminActionInterface
{
    use RA\SerializerTrait;
    use RA\TemplatingTrait;
    use Util\BreadcrumbTrait;

    protected const NAME = 'admin_summary';


    public function __invoke(): Response
    {
        if (!$this->request->isXmlHttpRequest()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        if (null === $resource = $this->context->getResource()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        $response = new Response();

        /* TODO if ($resource instanceof TimestampableInterface) {
            $response->setLastModified($resource->getUpdatedAt());

            if ($response->isNotModified($this->request)) {
                return $response;
            }
        } else {
            $response->setExpires(new DateTime('+3 min')); // TODO
        }*/

        $response->setVary(['Accept', 'Accept-Encoding']);

        $json = true;
        $accept = $this->request->getAcceptableContentTypes();

        if (in_array('application/json', $accept, true)) {
            $response->headers->add(['Content-Type' => 'application/json']);
        } elseif (in_array('text/html', $accept, true)) {
            $json = false;
        } else {
            return new Response('Unsupported content type.', Response::HTTP_NOT_FOUND);
        }

        if ($json) {
            $content = $this->getSerializer()->serialize($resource, 'json', ['groups' => ['Summary']]);
            $response->setContent($content);

            return $response;
        }

        if (empty($this->options['template'])) {
            throw new LogicException('Template option is not defined');
        }

        $content = $this->getSerializer()->normalize($resource, 'json', ['groups' => ['Summary']]);

        $content = $this->renderView($this->options['template'], $content);

        $response->setContent($content);

        return $response;
    }

    public static function configureAction(): array
    {
        return [
            'name'       => static::NAME,
            'permission' => Permission::READ,
            'route'      => [
                'name'     => 'admin_%s_summary',
                'path'     => '/summary',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'options'    => [
                'serialization_group' => 'Summary', // TODO Constant
                'template'            => null,
                'expose'              => true,
            ],
        ];
    }

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefined(['template', 'serialization_group'])
            ->setDefault('expose', true)
            ->setAllowedTypes('template', 'string')
            ->setAllowedTypes('serialization_group', 'string');
    }
}
