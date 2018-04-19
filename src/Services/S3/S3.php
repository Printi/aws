<?php

namespace Printi\AwsBundle\Services\S3;

use Aws\S3\S3Client;
use Printi\AwsBundle\Services\AwsService;
use Printi\AwsBundle\Services\S3\Exception\S3Exception;

/**
 * Class S3
 */
class S3 extends AwsService
{
    const SERVICE_NAME = "S3";

    /**
     * Download Pdf file from S3 bucket
     *
     * @param string $objectUrl      Download file url
     * @param string $bucket         The Bucket config key
     * @param string $expiration     Expiration time
     *
     * @return string
     * @throws S3Exception
     */
    public function signFileUrl(string $objectUrl, string $bucket, string $expiration = '+10 minutes'): string
    {
        $bucketName = $this->getResourceConfig($bucket)['name'];
        $cmdParams  = [
            'Bucket' => $bucketName,
            'Key'    => $this->getS3KeyFromObjectUrl(
                $objectUrl,
                $bucketName
            ),
        ];

        $cmd     = $this->getClient($bucket)->getCommand('GetObject', $cmdParams);
        $request = $this->s3Client->createPresignedRequest($cmd, $expiration);

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
        $objectUrl = false;

        if (preg_match("/upload\/temp\/(.*)/", $url, $matches)) {
            $originPath = parse_url($url, PHP_URL_PATH);
            $targetPath = sprintf(
                'upload/connected_files/%s/%s',
                $orderItemId,
                basename($matches[1])
            );
            $objectUrl = $this->copyFile($bucket, $originPath, $targetPath);
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
    public function copyFile(string $bucket, string $originPath, string $targetPath)
    {
        $bucketName = $this->getResourceConfig($bucket)['name'];
        $response   = $this->getClient($bucket)->copyObject([
            'Bucket'     => $bucketName,
            'CopySource' => $originPath,
            'Key'        => $targetPath,
        ]);

        return $response['ObjectURL'] ?? '';
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
