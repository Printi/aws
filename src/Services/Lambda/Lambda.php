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
                'FunctionName'   => $lambdaConfig['function_name'],
                'InvocationType' => 'Event', // for asynchronous lambda call
                'Payload'        => json_encode($payload),
            ];
            $client = $this->getClient($lambdaReference);
            $result = $client->invoke($config);
        } catch (\Exception $e) {
            var_dump($e);
            die;
            throw new LambdaException(LambdaException::TYPE_LAMBDA_INVOKE_FAILED);
        }

        return $result;
    }

    /**
     * Download any file to Aws s3 bucket
     *
     * @param string $downloadFileUrl The file to be download
     * @param string $bucket          The bucket name
     * @param string $key             The bucket key name
     * @param array  $callback        The callback array
     *
     * @return mixed
     * @throws LambdaException
     */
    public function downloadFileToS3(string $downloadFileUrl, string $bucket, string $key, array $callback = [])
    {
        $payload = [
            'file'   => $downloadFileUrl,
            'target' => [
                'bucket' => $bucket,
                'key'    => $key,
            ],
        ];

        if (!empty($callback)) {
            $payload['callback'] = $callback;
        }

        try {
            $result = $this->invoke('om2_import_to_s3', $payload);
        } catch (\Exception $e) {
            throw new LambdaException(LambdaException::TYPE_LAMBDA_INVOKE_FAILED);
        }

        return $result;
    }
}
