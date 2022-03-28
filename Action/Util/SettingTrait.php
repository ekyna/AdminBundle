<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Util;

use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;

/**
 * Trait SettingTrait
 * @package Ekyna\Bundle\AdminBundle\Action\Util
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
trait SettingTrait
{
    protected SettingManagerInterface $settingManager;

    public function setSettingManager(SettingManagerInterface $settingManager): void
    {
        $this->settingManager = $settingManager;
    }

    /**
     * @return mixed
     */
    protected function getSetting(string $name)
    {
        return $this->settingManager->getParameter($name);
    }
}
