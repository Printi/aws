<?php

namespace Printi\AwsBundle\Services\S3;

use Aws\S3\S3Client;
use Printi\AwsBundle\Services\S3\Exception\S3Exception;
use Psr\Log\LoggerInterface;

/**
 * Class S3
 */
class S3
{

    /** @var LoggerInterface $logger */
    private $logger;

    /** @var S3Client $s3Client */
    private $s3Client;

    /** @var array $s3Config */
    private $s3Config;

    public function __construct(S3Client $s3Client, array $s3Config, LoggerInterface $logger)
    {
        $this->s3Config = $s3Config;
        $this->s3Client = $s3Client;
        $this->logger   = $logger;
    }

    /**
     * Download Pdf file from S3 bucket
     *
     * @param string $objectUrl      Download file url
     * @param string $bucket         The Bucket config key
     * @param string $expirationTime Expiration time
     *
     * @return string
     * @throws S3Exception
     */
    public function signFileUrl(string $objectUrl, string $bucket, string $expirationTime = '+10 minutes'): string
    {

        if (!isset($this->s3Config[$bucket]['bucket'])) {
            throw new S3Exception(S3Exception::TYPE_S3_BUCKET_CONFIG_NOT_FOUND);
        }

        $bucketName = $this->s3Config[$bucket]['bucket'];
        $cmd        = $this->s3Client->getCommand(
            'GetObject',
            [
                'Bucket' => $bucketName,
                'Key'    => $this->getS3KeyFromObjectUrl($objectUrl, $bucketName),
            ]
        );

        $request = $this->s3Client->createPresignedRequest($cmd, $expirationTime);

        return (string) $request->getUri();
    }

    /**
     * Move S3 temp file to final location
     *
     * @param int    $orderItemId The order item id
     * @param string $url         Temp file url
     * @param string $bucket      The Bucket name
     *
     * @return mixed|string
     * @throws S3Exception
     */
    public function moveTempToFinal(int $orderItemId, string $url, string $bucket)
    {
        if (!isset($this->s3Config[$bucket]['bucket'])) {
            throw new S3Exception(S3Exception::TYPE_S3_BUCKET_CONFIG_NOT_FOUND);
        }

        $objectUrl  = false;
        $bucketName = $this->s3Config[$bucket]['bucket'];
        if (preg_match("/upload\/temp\/(.*)/", $url, $matches)) {
            $originPath = parse_url($url, PHP_URL_PATH);
            $targetPath = sprintf('upload/connected_files/%s/%s', $orderItemId, basename($matches[1]));
            $objectUrl  = $this->copyFile($bucketName, $originPath, $targetPath);
        }

        return $objectUrl;
    }

    /**
     * Copy S3 bucket file
     *
     * @param string $bucketName S3 Bucket Name
     * @param string $originPath The Origin file path
     * @param string $targetPath The Target file path
     *
     * @return mixed|string
     */
    public function copyFile(string $bucketName, string $originPath, string $targetPath)
    {
        $response = $this->s3Client->copyObject(
            [
                'Bucket'     => $bucketName,
                'Key'        => $targetPath,
                'CopySource' => $originPath,
            ]
        );

        return isset($response['ObjectURL']) ? $response['ObjectURL'] : '';
    }

    /**
     * Retrieve S3 key url from a full S3 url
     * Looks like we can have 2 kind of urls
     *      https://alpha-upload-dev.s3-sa-east-1.amazonaws.com/briefing/800301/800480/800301_800480_14072017_1344_3.pdf
     *      https://s3-sa-east-1.amazonaws.com/alpha-upload-dev/briefing/800301/800480/800301_800480_14072017_1344_3.pdf
     * where
     *      'alpha-upload-dev' is the value of $this->bucketConfig['bucket']
     * and we should always return 'briefing/800301/800480/800301_800480_14072017_1344_3.pdf'
     *
     * @param string $objectUrl The S3 full url
     * @param string $bucket    The Bucket name
     *
     * @return bool|string with s3 key url
     */
    protected function getS3KeyFromObjectUrl($objectUrl, $bucket)
    {
        $pattern = "/.*" . preg_quote($bucket) . "[^\/]*\/(.*)/";
        if (preg_match($pattern, $objectUrl, $match)) {
            return $match[1];
        }

        // OLD live behavior: we receive objectUrl like https://s3-sa-east-1.amazonaws.com/[bucketName]/
        $objectParts = explode($bucket, $objectUrl);

        return substr(array_pop($objectParts), 1);
    }
}
