<?php declare(strict_types=1);

/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\SentryExcludeErrorsRegex\Test\Integration;

use JustBetter\Sentry\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Vendic\SentryExcludeErrorsRegex\Plugin\FilterMatchingErrors;

class RegexMatchingTest extends TestCase
{
    const ERROR = 'Placing an order with quote_id diQYtH95xqKMK93hTiO3N4CKulT9RyuS is failed: The payment is REFUSED.';
    /**
     * @var Data|mixed
     */
    private $sentryData;
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->sentryData = $this->objectManager->get(Data::class);
    }

    public function testRegexMatch(): void
    {
        $mock = $this->getMockBuilder(FilterMatchingErrors::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRegexIgnored'])
            ->getMock();
        $mock->method('getRegexIgnored')->willReturn(
            ['^Placing an order with quote_id \\w{32} is failed: The payment is REFUSED\\.$']
        );
        /** @var ObjectManager $objectManager */
        $objectManager = $this->objectManager;
        $objectManager->addSharedInstance($mock, FilterMatchingErrors::class);

        $exception = new LocalizedException(__(self::ERROR));
        $captureBool = $this->sentryData->shouldCaptureException($exception);

        $this->assertFalse($captureBool);
    }

    public function testInvalidRegex(): void
    {
        $mock = $this->getMockBuilder(FilterMatchingErrors::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getRegexIgnored'])
            ->getMock();
        $mock->method('getRegexIgnored')->willReturn(
            // Added a incomplete group structure to make regex invalid.
            ['^Placing an order with quote_id \\w{32} is faile(d: The payment is REFUSED\\.$']
        );
        /** @var ObjectManager $objectManager */
        $objectManager = $this->objectManager;
        $objectManager->addSharedInstance($mock, FilterMatchingErrors::class);

        $exception = new LocalizedException(__(self::ERROR));
        $captureBool = $this->sentryData->shouldCaptureException($exception);

        $this->assertTrue($captureBool);
    }

    public function testNoMatch(): void
    {
        $exception = new LocalizedException(__(self::ERROR));
        $captureBool = $this->sentryData->shouldCaptureException($exception);
        $this->assertTrue($captureBool);
    }
}
