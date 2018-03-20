<?php

namespace Printi\AwsBundle\Services\Sqs;

use Aws\Sqs\SqsClient;
use Printi\AwsBundle\Services\Sqs\Exception\SqsException;
use Psr\Log\LoggerInterface;

/**
 * Class Sqs
 */
class Sqs
{
    /** @var SqsClient $sqsClient */
    private $sqsClient;

    /** @var LoggerInterface $logger */
    private $logger;

    /** @var array $sqsConfig */
    private $sqsConfig;


    public function __construct(SqsClient $sqsClient, array $sqsConfig, LoggerInterface $logger)
    {
        $this->sqsConfig = $sqsConfig;
        $this->sqsClient = $sqsClient;
        $this->logger    = $logger;
    }

    /**
     * Send Message on SQS
     *
     * @param string $queueReference
     * @param array  $messageBody The actual body that should be sent (for example an order item info)
     *
     * @return \Aws\Result
     * @throws SqsException
     */

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
        if (!isset($this->sqsConfig[$queueReference])) {
            throw new SqsException(SqsException::TYPE_SQS_CONFIG_NOT_FOUND);
        }

        if (false === $this->sqsConfig[$queueReference]['enable']) {
            throw new SqsException(SqsException::TYPE_SQS_CONFIG_DISABLED);
        }

        try {

            $config = [
                'QueueUrl'    => $this->sqsConfig[$queueReference]['queue_url'],
                'MessageBody' => json_encode($messageBody),
            ];

            if (isset($this->sqsConfig[$queueReference]['queue_type']) && 'fifo' === $this->sqsConfig[$queueReference]['queue_type']) {
                $config['MessageGroupId']         = uniqid();
                $config['MessageDeduplicationId'] = uniqid();
            }

            $result = $this->sqsClient->sendMessage($config);
            $logMessage = sprintf(
                "SQS Sending | Payload : %s | Response Status Code : %d",
                json_encode($messageBody),
                $result->get('@metadata')['statusCode']
            );
            $this->logger->info($logMessage);
        } catch (\Exception $e) {
            $logMessage = sprintf(
                "SQS Sending | Payload : %s | Error : %s",
                json_encode($messageBody),
                $e->getMessage()
            );
            $this->logger->error($logMessage);

            throw new SqsException(SqsException::TYPE_SQS_SENDING_FAILED);
        }

        return $result;
    }
}
