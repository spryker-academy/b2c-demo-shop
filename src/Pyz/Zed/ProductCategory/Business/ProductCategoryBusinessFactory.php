<?php

/**
 * This file is part of the Spryker Demoshop.
 * For full license information, please view the LICENSE file that was distributed with this source code.
 */

namespace Pyz\Zed\ProductCategory\Business;

use Pyz\Zed\ProductCategory\Business\Internal\DemoData\ProductCategoryMappingInstall;
use Spryker\Shared\Library\Reader\Csv\CsvReader;
use Spryker\Zed\Messenger\Business\Model\MessengerInterface;
use Spryker\Zed\ProductCategory\Business\ProductCategoryBusinessFactory as SprykerBusinessFactory;
use Spryker\Zed\ProductCategory\Business\ProductCategoryManager;
use Spryker\Zed\ProductCategory\Business\TransferGenerator;
use Spryker\Zed\ProductCategory\ProductCategoryDependencyProvider;

/**
 * @method \Pyz\Zed\ProductCategory\ProductCategoryConfig getConfig()
 * @method \Spryker\Zed\ProductCategory\Persistence\ProductCategoryQueryContainer getQueryContainer()
 */
class ProductCategoryBusinessFactory extends SprykerBusinessFactory
{

    /**
     * @param \Spryker\Zed\Messenger\Business\Model\MessengerInterface $messenger
     *
     * @return \Pyz\Zed\ProductCategory\Business\Internal\DemoData\ProductCategoryMappingInstall
     */
    public function createDemoDataInstaller(MessengerInterface $messenger)
    {
        $installer = new ProductCategoryMappingInstall(
            $this->createProductCategoryManager(),
            $this->getCategoryFacade(),
            $this->getProductFacade(),
            $this->getLocaleFacade(),
            $this->createCSVReader(),
            $this->getConfig()->getDemoDataCSVPath()
        );
        $installer->setMessenger($messenger);

        return $installer;
    }

    /**
     * @return \Spryker\Shared\Library\Reader\Csv\CsvReaderInterface
     */
    protected function createCSVReader()
    {
        return new CsvReader();
    }

    /**
     * @return \Spryker\Zed\ProductCategory\Business\ProductCategoryManagerInterface
     */
    public function createProductCategoryManager()
    {
        return new ProductCategoryManager(
            $this->getCategoryQueryContainer(),
            $this->getQueryContainer(),
            $this->getProductFacade(),
            $this->getCategoryFacade(),
            $this->getTouchFacade(),
            $this->getCmsFacade(),
            $this->getProvidedDependency(ProductCategoryDependencyProvider::PLUGIN_PROPEL_CONNECTION)
        );
    }

    /**
     * @return \Spryker\Zed\ProductCategory\Business\TransferGeneratorInterface
     */
    public function createProductCategoryTransferGenerator()
    {
        return new TransferGenerator();
    }

}
