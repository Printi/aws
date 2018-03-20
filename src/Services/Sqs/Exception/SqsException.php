<?php

namespace Printi\AwsBundle\Services\Sqs\Exception;

use Printi\AwsBundle\Exception\AbstractException;

/**
 * Class SqsException
 */
class SqsException extends AbstractException
{
    const TYPE_SQS_CONFIG_NOT_FOUND = "SQS_CONFIG_NOT_FOUND";
    const TYPE_SQS_CONFIG_DISABLED  = "SQS_CONFIG_DISABLED";
    const TYPE_SQS_SENDING_FAILED   = "SQS_SENDING_FAILED";

    /**
     * @inheritDoc
     */
    protected function populateCodeMap()
    {
        $this->codeMap = [
            self::TYPE_SQS_CONFIG_NOT_FOUND => 500,
            self::TYPE_SQS_CONFIG_DISABLED  => 403,
            self::TYPE_SQS_SENDING_FAILED   => 400,
        ];
    }
}
