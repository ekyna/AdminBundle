<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Renderer;

use Ekyna\Component\User\Service\OAuth\OAuthConfigurator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;

use function in_array;

/**
 * Class OauthRenderer
 * @package Ekyna\Bundle\AdminBundle\Service\Renderer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OauthRenderer
{
    private ClientRegistry $clientRegistry;

    public function __construct(ClientRegistry $clientRegistry)
    {
        $this->clientRegistry = $clientRegistry;
    }

    public function getOAuthConnectButtons(string $name): array
    {
        $result = []; // TODO Cache

        $clients = $this->clientRegistry->getEnabledClientKeys();

        if (empty($clients)) {
            return [];
        }

        foreach (array_keys(OAuthConfigurator::OWNERS) as $owner) {
            if (!in_array($name . '_' . $owner, $clients, true)) {
                continue;
            }

            $result[$owner] = OAuthConfigurator::route($name, $owner, false);
        }

        return $result;
    }
}
