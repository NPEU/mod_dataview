<?php

namespace NPEU\Module\Dataview\Site\Dispatcher;

use Joomla\CMS\Dispatcher\AbstractModuleDispatcher;
use Joomla\CMS\Helper\HelperFactoryAwareInterface;
use Joomla\CMS\Helper\HelperFactoryAwareTrait;
use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

/**
 * Dispatcher class for mod_dataview
 *
 * @since  4.4.0
 */
class Dispatcher extends AbstractModuleDispatcher implements HelperFactoryAwareInterface
{
    use HelperFactoryAwareTrait;

    /**
     * Returns the layout data.
     *
     * @return  array
     */
    protected function getLayoutData(): array
    {
        $data   = parent::getLayoutData();
        $params = $data['params'];


        $data['twig'] = $this->getHelperFactory()
            ->getHelper('DataviewHelper')
            ->getTwig($params, $this->getApplication());

        $this->getHelperFactory()->getHelper('DataviewHelper')->loadAssets($params);

        return $data;
    }
}
