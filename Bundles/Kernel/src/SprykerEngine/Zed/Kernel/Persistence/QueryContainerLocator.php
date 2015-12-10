<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace SprykerEngine\Zed\Kernel\Persistence;

use SprykerEngine\Zed\Propel\Communication\Plugin\Connection;
use Generated\Zed\Ide\AutoCompletion;
use SprykerEngine\Shared\Kernel\ClassResolver\ClassNotFoundException;
use SprykerEngine\Shared\Kernel\Locator\LocatorException;
use SprykerEngine\Shared\Kernel\LocatorLocatorInterface;
use SprykerEngine\Shared\Kernel\AbstractLocator;
use SprykerEngine\Zed\Kernel\BundleDependencyProviderLocator;
use SprykerEngine\Zed\Kernel\Container;
use SprykerFeature\Shared\Library\Log;

class QueryContainerLocator extends AbstractLocator
{

    const PROPEL_CONNECTION = 'propel connection';

    /**
     * @var string
     */
    protected $bundle = 'Kernel';

    /**
     * @var string
     */
    protected $layer = 'Persistence';

    /**
     * @var string
     */
    protected $suffix = 'Factory';

    /**
     * @var string
     */
    protected $application = 'Zed';

    /**
     * @param string $bundle
     * @param LocatorLocatorInterface $locator
     * @param string|null $className
     *
     * @throws LocatorException
     *
     * @return object
     */
    public function locate($bundle, LocatorLocatorInterface $locator, $className = null)
    {
        $factory = $this->getFactory($bundle);

        $queryContainer = $factory->create($bundle . 'QueryContainer');

        try {
            // TODO Make singleton because of performance
            $bundleConfigLocator = new BundleDependencyProviderLocator();
            $bundleBuilder = $bundleConfigLocator->locate($bundle, $locator);
            $container = new Container();
            $container[self::PROPEL_CONNECTION] = function () use ($locator) {
                /* @var $locator AutoCompletion */
                return (new Connection())->get();
            };
            $bundleBuilder->providePersistenceLayerDependencies($container);
            $queryContainer->setExternalDependencies($container);
        } catch (ClassNotFoundException $e) {
            // TODO remove try-catch when all bundles have a DependencyProvider
            Log::log($bundle, 'builder_missing.log');
        }

        return $queryContainer;
    }

    /**
     * @param string $bundle
     *
     * @return bool
     */
    public function canLocate($bundle)
    {
        $factory = $this->getFactory($bundle);

        return $factory->exists($bundle . 'QueryContainer');
    }

}
