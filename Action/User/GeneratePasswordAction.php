<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\User;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\Event\UserEvents;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Mailer\AdminMailer;
use Ekyna\Bundle\AdminBundle\Show\Exception\UnexpectedTypeException;
use Ekyna\Bundle\ResourceBundle\Action\AbstractAction;
use Ekyna\Bundle\ResourceBundle\Action\HelperTrait;
use Ekyna\Bundle\ResourceBundle\Action\ManagerTrait;
use Ekyna\Bundle\ResourceBundle\Action\ResourceEventDispatcherTrait;
use Ekyna\Bundle\UiBundle\Action\FlashTrait;
use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\User\Service\Security\SecurityUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

use function sprintf;

/**
 * Class GeneratePasswordAction
 * @package Ekyna\Bundle\AdminBundle\Action\User
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GeneratePasswordAction extends AbstractAction implements AdminActionInterface
{
    use HelperTrait;
    use ManagerTrait;
    use ResourceEventDispatcherTrait;
    use FlashTrait;

    private SecurityUtil $securityUtil;
    private AdminMailer  $mailer;

    public function __construct(SecurityUtil $securityUtil, AdminMailer $mailer)
    {
        $this->securityUtil = $securityUtil;
        $this->mailer = $mailer;
    }

    public function __invoke(): Response
    {
        /** @var UserInterface $resource */
        $resource = $this->context->getResource();

        if (!$resource instanceof UserInterface) {
            throw new UnexpectedTypeException($resource, UserInterface::class);
        }

        // Prevent changing password of super admin
        if (in_array('ROLE_SUPER_ADMIN', $resource->getGroup()->getRoles())) {
            throw new AccessDeniedHttpException();
        }

        $redirect = $this->generateResourcePath($resource);

        $manager = $this->getManager();

        $event = $manager->createResourceEvent($resource);

        // Pre generate event
        $this
            ->getResourceEventDispatcher()
            ->dispatch($event, UserEvents::PRE_GENERATE_PASSWORD);

        if ($event->isPropagationStopped()) {
            $this->addFlashFromEvent($event);

            return $this->redirect($redirect);
        }

        $password = $this->securityUtil->generatePassword();

        $resource
            ->setPassword('TriggerPersistence')
            ->setPlainPassword($password);

        $event
            ->addMessage(new ResourceMessage(
                sprintf('Generated password : "%s".', $password),
                ResourceMessage::TYPE_INFO
            ))
            ->addData('password', $password);

        // Update event
        $manager->update($event);

        if (!$event->isPropagationStopped()) {
            $this
                ->mailer
                ->sendNewPasswordEmail($resource, $password);

            $event->addMessage(
                ResourceMessage::create('user.message.credentials_sent')->setDomain('EkynaAdmin')
            );
        }

        // Flashes
        $this->addFlashFromEvent($event);

        return $this->redirect($redirect);
    }

    public static function configureAction(): array
    {
        return [
            'permission' => Permission::UPDATE,
            'route'      => [
                'name'     => 'admin_%s_generate_password',
                'path'     => '/generate-password',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'button'     => [
                'label'        => 'user.button.generate_password',
                'theme'        => 'warning',
                'trans_domain' => 'EkynaAdmin',
            ],
        ];
    }
}
