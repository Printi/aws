<?php

namespace Printi\AwsBundle\Services\Sqs;

use Printi\AwsBundle\Services\AwsService;
use Printi\AwsBundle\Services\Sqs\Exception\SqsException;

/**
 * Class Sqs
 */
class Sqs extends AwsService
{
    /**
     * Send Message on SQS
     *
     * @param string $queueReference Sqs reference
     * @param array  $messageBody    The actual body that should be sent (for example an order item info)
     *
     * @return \Aws\Result
     * @throws SqsException
     */
    public function send(string $queueReference, array $messageBody = [])
    {
        $queueConfig = $this->getResourceConfig($queueReference);

        if (false === $queueConfig['enable']) {
            throw new SqsException(SqsException::TYPE_SQS_CONFIG_DISABLED);
        }

        try {
            $config = [
                'QueueUrl'    => $queueConfig['queue_url'],
                'MessageBody' => json_encode($messageBody),
            ];

            $isFifo = (
                isset($queueConfig['queue_type']) &&
                'fifo' === $queueConfig['queue_type']
            );

            if ($isFifo) {
                $config['MessageGroupId']         = uniqid();
                $config['MessageDeduplicationId'] = uniqid();
            }

            $result = $this->getClient($queueReference)->sendMessage($config);
        } catch (\Exception $e) {
            throw new SqsException(SqsException::TYPE_SQS_SENDING_FAILED);
        }

        return $result;
    }
}
