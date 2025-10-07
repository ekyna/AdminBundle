<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Service\Filesystem\FilesystemHelper;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\UploadableInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractDownloadAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractDownloadAction extends AbstractAction implements AdminActionInterface
{
    public function __construct(
        private readonly FilesystemOperator $filesystem
    ) {
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof UploadableInterface) {
            throw new UnexpectedTypeException($resource, UploadableInterface::class);
        }

        $helper = new FilesystemHelper($this->filesystem);

        try {
            return $helper->createFileResponse($resource->getPath());
        } catch (FilesystemException) {
        }

        return new Response('File does not exist or is not available', Response::HTTP_NOT_FOUND);
    }
}
