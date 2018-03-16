<?php

namespace Printi\AwsBundle\Services\Sns\Exception;

use Printi\AwsBundle\Exception\AbstractException;

/**
 * Class SnsException
 */
class SnsException extends AbstractException
{
    const TYPE_SNS_CONFIG_NOT_FOUND = "SNS_CONFIG_NOT_FOUND";
    const TYPE_SNS_CONFIG_DISABLED  = "SNS_CONFIG_DISABLED";
    const TYPE_SNS_PUBLISH_FAILED   = "SNS_PUBLISH_FAILED";

    /**
     * @inheritDoc
     */
    protected function populateCodeMap()
    {
        $this->codeMap = [
            self::TYPE_SNS_CONFIG_NOT_FOUND => 500,
            self::TYPE_SNS_CONFIG_DISABLED  => 403,
            self::TYPE_SNS_PUBLISH_FAILED   => 400,
        ];
    }
}
