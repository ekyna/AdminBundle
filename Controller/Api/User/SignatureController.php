<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Controller\Api\User;

use DateTime;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\AdminBundle\Service\Renderer\SignatureRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class SignatureController
 * @package Ekyna\Bundle\AdminBundle\Controller\Api\User
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SignatureController
{
    private UserRepositoryInterface $repository;
    private SignatureRenderer       $renderer;
    private bool                    $debug;

    public function __construct(UserRepositoryInterface $repository, SignatureRenderer $renderer, bool $debug)
    {
        $this->repository = $repository;
        $this->renderer = $renderer;
        $this->debug = $debug;
    }

    public function __invoke(Request $request): Response
    {
        $id = $request->attributes->getInt('id');

        $user = $this->repository->find($id);

        if (null === $user) {
            throw new NotFoundHttpException();
        }

        $response = new Response();
        $response
            ->setExpires(new DateTime('+1 hour'))
            ->setLastModified($user->getUpdatedAt());

        /* TODO Purge on setting (signature_pre/post) change
         * if (!$this->debug && !$response->isNotModified($request)) {
            return $response;
        }*/

        $content = $this->renderer->render($user);

        $response->setContent($content);

        $response->headers->set('Content-Type', 'text/plain');

        return $response;
    }
}
