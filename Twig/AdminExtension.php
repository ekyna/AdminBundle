<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

/**
 * AdminExtension
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends \Twig_Extension
{
    protected $logoPath;

    public function __construct($logoPath)
    {
        $this->logoPath = $logoPath;
    }

    /**
     * Returns a list of global variables to add to the existing list.
     *
     * @return array An array of global variables
     */
    public function getGlobals()
    {
        return array(
        	'ekyna_admin_logo_path' => $this->logoPath,
        );
    }

    public function getName()
    {
    	return 'ekyna_admin';
    }
}
