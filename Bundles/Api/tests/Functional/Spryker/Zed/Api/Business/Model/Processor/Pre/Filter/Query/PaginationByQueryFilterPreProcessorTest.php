<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Functional\Spryker\Zed\Api\Business\Model\Processor\Pre\Filter\Query;

use Codeception\TestCase\Test;
use Generated\Shared\Transfer\ApiFilterTransfer;
use Generated\Shared\Transfer\ApiRequestTransfer;
use Spryker\Zed\Api\ApiConfig;
use Spryker\Zed\Api\Business\Model\Processor\Pre\Filter\Query\PaginationByQueryFilterPreProcessor;

/**
 * @group Functional
 * @group Spryker
 * @group Zed
 * @group Api
 * @group Business
 * @group Model
 * @group Processor
 * @group Pre
 * @group Filter
 * @group Query
 * @group PaginationByQueryFilterPreProcessorTest
 */
class PaginationByQueryFilterPreProcessorTest extends Test
{

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * @return void
     */
    public function testProcessWithDefaults()
    {
        $config = new ApiConfig();
        $processor = new PaginationByQueryFilterPreProcessor($config);

        $apiRequestTransfer = new ApiRequestTransfer();
        $apiRequestTransfer->setFilter(new ApiFilterTransfer());

        $apiRequestTransferAfter = $processor->process($apiRequestTransfer);
        $this->assertSame(20, $apiRequestTransferAfter->getFilter()->getLimit());
        $this->assertSame(0, $apiRequestTransferAfter->getFilter()->getOffset());
    }

    /**
     * @return void
     */
    public function testProcessWithDefaultsPageTwo()
    {
        $config = new ApiConfig();
        $processor = new PaginationByQueryFilterPreProcessor($config);

        $apiRequestTransfer = new ApiRequestTransfer();
        $apiRequestTransfer->setFilter(new ApiFilterTransfer());
        $apiRequestTransfer->setQueryData([
            PaginationByQueryFilterPreProcessor::LIMIT => 20,
            PaginationByQueryFilterPreProcessor::PAGE => 2,
        ]);

        $apiRequestTransferAfter = $processor->process($apiRequestTransfer);
        $this->assertSame(20, $apiRequestTransferAfter->getFilter()->getLimit());
        $this->assertSame(20, $apiRequestTransferAfter->getFilter()->getOffset());
    }

    /**
     * @return void
     */
    public function testProcessWithCustomLimit()
    {
        $config = new ApiConfig();
        $processor = new PaginationByQueryFilterPreProcessor($config);

        $apiRequestTransfer = new ApiRequestTransfer();
        $apiRequestTransfer->setFilter(new ApiFilterTransfer());
        $apiRequestTransfer->setQueryData([
            PaginationByQueryFilterPreProcessor::LIMIT => 10,
            PaginationByQueryFilterPreProcessor::PAGE => 5,
        ]);

        $apiRequestTransferAfter = $processor->process($apiRequestTransfer);
        $this->assertSame(10, $apiRequestTransferAfter->getFilter()->getLimit());
        $this->assertSame(40, $apiRequestTransferAfter->getFilter()->getOffset());
    }

}
