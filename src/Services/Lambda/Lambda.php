<?php

namespace Printi\AwsBundle\Services\Lambda;

use Printi\AwsBundle\Services\AwsService;
use Printi\AwsBundle\Services\Lambda\Exception\LambdaException;

/**
 * Class Lambda
 */
class Lambda extends AwsService
{

    /**
     * Invoke a lambda function
     *
     * @param string $lambdaReference
     * @param array  $payload
     *
     * @return mixed
     * @throws LambdaException
     */
    public function invoke(string $lambdaReference, array $payload)
    {
        $lambdaConfig = $this->getResourceConfig($lambdaReference);

        if (!isset($lambdaConfig['function_name'])) {
            throw new LambdaException(LambdaException::TYPE_LAMBDA_CONFIG_NOT_FOUND);
        }

        try {
            $config = [
                'FunctionName' => $lambdaConfig['function_name'],
                'Payload'      => json_encode($payload)
            ];

            $result = $this->getClient($lambdaReference)->invoke($config);
        } catch (\Exception $e) {
            throw new LambdaException(LambdaException::TYPE_LAMBDA_INVOKE_FAILED);
        }

        return $result;
    }
}
