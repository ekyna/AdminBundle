<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Tree;

use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\TreeInterface;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class MoveDownAction
 * @package Ekyna\Bundle\AdminBundle\Action\Tree
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MoveDownAction extends AbstractMoveAction
{
    protected const NAME = 'admin_tree_move_down';


    /**
     * @inheritDoc
     */
    public function __invoke(): Response
    {
        $resource = $this->context->getResource();
        if (!$resource instanceof TreeInterface) {
            throw new UnexpectedTypeException($resource, TreeInterface::class);
        }

        /** @var NestedTreeRepository $repository */
        $repository = $this->getRepository();
        $repository->moveDown($resource);

        return $this->redirectToRead();
    }

    /**
     * @inheritDoc
     */
    public static function configureAction(): array
    {
        return array_replace(parent::configureAction(), [
            'name'   => static::NAME,
            'route'  => [
                'name'     => 'admin_%s_tree_move_down',
                'path'     => '/tree-move-down',
                'resource' => true,
                'methods'  => 'GET',
            ],
            'button' => [
                'label' => 'button.move_down',
                'theme' => 'primary',
                'icon'  => 'arrow-down',
            ],
        ]);
    }
}
