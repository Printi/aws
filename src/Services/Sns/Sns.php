<?php

namespace Printi\AwsBundle\Services\Sns;

use Aws\Result as AwsResult;
use Printi\AwsBundle\Services\AwsService;
use Printi\AwsBundle\Services\Sns\Exception\SnsException;

/**
 * Class Sns
 */
class Sns extends AwsService
{
    const SERVICE_NAME = "Sns";

    /**
     * Publishes a notification to Aws SNS
     *
     * @param string $topic
     * @param array  $messageBody The actual body that should be sent (for example an order item info)
     *
     * @return AwsResult
     * @throws SnsException
     */
    public function publish(string $topic, array $messageBody = []): AwsResult
    {
        $topicConfig = $this->getResourceConfig($topic);

        if (false === $topicConfig['enable']) {
            throw new SnsException(SnsException::TYPE_SNS_CONFIG_DISABLED);
        }

        $payload = [
            'TopicArn'         => $topicConfig['topic_arn'],
            'Message'          => json_encode([
                'default' => $messageBody['error_message'] ?? 'Omega message',
                'sqs'     => json_encode($messageBody),
            ]),
            'MessageStructure' => 'json',
        ];

        try {
            $result = $this->getClient($topic)->publish($payload);
        } catch (\Exception $e) {
            throw $e;
            // throw new SnsException(SnsException::TYPE_SNS_PUBLISH_FAILED);
        }

        return $result;
    }
}
