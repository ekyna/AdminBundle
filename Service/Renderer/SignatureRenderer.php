<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Renderer;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Twig\Environment;

/**
 * Class SignatureRenderer
 * @package Ekyna\Bundle\AdminBundle\Service\Renderer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SignatureRenderer
{
    private Environment $twig;
    private array       $config;
    private string      $defaultLocale;

    public function __construct(Environment $twig, array $config, string $defaultLocale)
    {
        $this->twig = $twig;
        $this->config = array_replace([
            'template' => '@EkynaAdmin/Email/user_signature.html.twig',
            'locale'   => 'en',
            'logo'     => null,
        ], $config);
        $this->defaultLocale = $defaultLocale;
    }

    public function render(?UserInterface $user, string $locale = null): string
    {
        return $this->twig->render($this->config['template'], [
            'user'   => $user,
            'logo'   => $this->config['logo'],
            'locale' => $locale ?: $this->defaultLocale,
        ]);
    }
}
