<?php

namespace Printi\AwsBundle\Services\Lambda\Exception;

use Printi\AwsBundle\Exception\AbstractException;

/**
 * Class LambdaException
 */
class LambdaException extends AbstractException
{
    const TYPE_LAMBDA_CONFIG_NOT_FOUND = "LAMBDA_CONFIG_NOT_FOUND";
    const TYPE_LAMBDA_INVOKE_FAILED    = "LAMBDA_INVOKE_FAILED";

    /**
     * @inheritDoc
     */
    protected function populateCodeMap()
    {
        $this->codeMap = [
            self::TYPE_LAMBDA_CONFIG_NOT_FOUND => 400,
            self::TYPE_LAMBDA_INVOKE_FAILED    => 400,
        ];
    }
}
