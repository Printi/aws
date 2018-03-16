<?php

namespace Printi\AwsBundle\Services\Sns;

use Aws\Sns\SnsClient;
use Printi\AwsBundle\Services\Sns\Exception\SnsException;
use Psr\Log\LoggerInterface;

/**
 * Class Sns
 */
class Sns
{
    /** @var SnsClient $snsClient */
    private $snsClient;

    /** @var LoggerInterface $logger */
    private $logger;

    /** @var array $snsConfig */
    private $snsConfig;


    public function __construct(SnsClient $snsClient, array $snsConfig, LoggerInterface $logger)
    {
        $this->snsConfig = $snsConfig;
        $this->snsClient = $snsClient;
        $this->logger    = $logger;
    }

    /**
     * Publishes a notification to Aws SNS
     *
     * @param string $topic
     * @param array  $messageBody The actual body that should be sent (for example an order item info)
     *
     * @return \Aws\Result
     * @throws SnsException
     */
    public function publish(string $topic, array $messageBody = [])
    {
        if (!isset($this->snsConfig[$topic])) {
            throw new SnsException(SnsException::TYPE_SNS_CONFIG_NOT_FOUND);
        }

        if (false === $this->snsConfig[$topic]['enable']) {
            throw new SnsException(SnsException::TYPE_SNS_CONFIG_DISABLED);
        }

        $message = json_encode(
            [
                'default' => 'Omega message',
                'sqs'     => json_encode($messageBody),
            ]
        );

        $payload = [
            'TopicArn'         => $this->snsConfig[$topic]['topic_arn'],
            'Message'          => $message,
            'MessageStructure' => 'json',
        ];

        try {
            $result     = $this->snsClient->publish($payload);
            $logMessage = sprintf(
                "SNS Publish | Payload : %s | Response Status Code : %d",
                json_encode($payload),
                $result->get('@metadata')['statusCode']
            );
            $this->logger->info($logMessage);
        } catch (\Exception $e) {
            $logMessage = sprintf(
                "SNS Publish | Payload : %s | Error : %s",
                json_encode($payload),
                $e->getMessage()
            );
            $this->logger->error($logMessage);

            throw new SnsException(SnsException::TYPE_SNS_PUBLISH_FAILED);
        }

        return $result;
    }
}
