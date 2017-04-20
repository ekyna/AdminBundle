<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class MoveUpAction
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MoveUpAction extends AbstractMoveAction
{
    protected const NAME = 'admin_move_up';


    /**
     * @inheritDoc
     */
    public function __invoke(): Response
    {
        return $this->moveResource(-1);
    }

    /**
     * @inheritDoc
     */
    public static function configureAction(): array
    {
        return array_replace(parent::configureAction(), [
            'name'   => static::NAME,
            'route'  => [
                'name'     => 'admin_%s_move_up',
                'path'     => '/move-up',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'button' => [
                'label' => 'button.move_up',
                'theme' => 'primary',
                'icon'  => 'arrow-up',
            ],
        ]);
    }
}
