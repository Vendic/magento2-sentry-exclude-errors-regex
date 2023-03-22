<?php declare(strict_types=1);
/**
 * @copyright   Copyright (c) Vendic B.V https://vendic.nl/
 */

namespace Vendic\SentryExcludeErrorsRegex\Plugin;

use JustBetter\Sentry\Helper\Data;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Throwable;

class FilterMatchingErrors
{
    public function __construct(
        private DeploymentConfig $deploymentConfig
    ) {
    }

    public function aroundShouldCaptureException(
        Data $subject,
        callable $proceed,
        Throwable $ex
    ): bool {
        $regexIgnored = $this->getRegexIgnored();

        foreach ($regexIgnored as $regex) {
            try {
                // We surround this with a try/catch because preg_match can throw a warning if the regex is invalid
                $preg_match = preg_match('/' . $regex . '/', $ex->getMessage());
            } catch (\Exception $e) {
                return $proceed($ex);
            }
            if ($preg_match) {
                return false;
            }
        }

        return $proceed($ex);
    }

    protected function getRegexIgnored(): array
    {
        try {
            $regexIgnored = $this->deploymentConfig->get('sentry/ignore_exceptions_regex') ?? [];
        } catch (FileSystemException|RuntimeException $e) {
            return [];
        }
        return $regexIgnored;
    }
}
