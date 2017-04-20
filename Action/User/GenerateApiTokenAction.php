<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\User;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\User\Service\SecurityUtil;
use Symfony\Component\HttpFoundation\Response;

use function sprintf;

/**
 * Class GenerateApiTokenAction
 * @package Ekyna\Bundle\AdminBundle\Action\User
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GenerateApiTokenAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use FlashTrait;

    private SecurityUtil $securityUtil;

    public function __construct(SecurityUtil $securityUtil)
    {
        $this->securityUtil = $securityUtil;
    }

    public function __invoke(): Response
    {
        $resource = $this->context->getResource();

        if (!$resource instanceof UserInterface) {
            throw new UnexpectedTypeException($resource, UserInterface::class);
        }

        $token = $this->securityUtil->generateToken();

        $resource->setApiToken($token);

        // Update event
        $event = $this->getManager()->update($resource);

        if (!$event->hasErrors()) {
            $event->addMessage(new ResourceMessage(
                sprintf('Generated token : "%s".', $token),
                ResourceMessage::TYPE_INFO
            ));
        }

        // Flashes
        $this->addFlashFromEvent($event);

        return $this->redirect($this->generateResourcePath($resource));
    }

    public static function configureAction(): array
    {
        return [
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_generate_api_token',
                'path'     => '/generate-api-token',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'button'     => [
                'label'        => 'user.button.generate_api_token',
                'trans_domain' => 'EkynaAdmin',
                'theme'        => 'warning',
            ],
        ];
    }
}
