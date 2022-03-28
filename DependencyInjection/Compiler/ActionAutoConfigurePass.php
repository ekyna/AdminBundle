<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\AdminBundle\Action\Util;
use Ekyna\Component\Resource\Bridge\Symfony\DependencyInjection\Compiler\ActionAutoConfigurePass as BasePass;

/**
 * Class ActionAutoConfigurePass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ActionAutoConfigurePass extends BasePass
{
    protected function getAutoconfigureMap(): array
    {
        return [
            Util\BreadcrumbTrait::class => [
                'setMenuBuilder' => 'ekyna_admin.menu.builder',
            ],
            Util\ModalTrait::class      => [
                'setRenderer' => 'ekyna_ui.modal.renderer',
            ],
            Util\SettingTrait::class    => [
                'setSettingManager' => 'ekyna_setting.manager',
            ],
            Util\TableTrait::class      => [
                'setTableFactory' => 'table.factory',
            ],
        ];
    }
}
