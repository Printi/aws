<?php

namespace Printi\AwsBundle\Services\S3;

use Printi\AwsBundle\Services\AwsService;
use Printi\AwsBundle\Services\S3\Exception\S3Exception;

/**
 * Class S3
 */
class S3 extends AwsService
{
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
        $bucketName = $this->getS3BucketName($bucket);
        $cmdParams  = [
            'Bucket' => $bucketName,
            'Key'    => $this->getS3KeyFromObjectUrl(
                $objectUrl,
                $bucketName
            ),
        ];

        $cmd     = $this->getClient($bucket)->getCommand('GetObject', $cmdParams);
        $request = $this->getClient($bucket)->createPresignedRequest($cmd, $expiration);

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
        /**
         * If the file is already on our `connected_files` folder, we can just
         * return the provided URL as it's already where it should be.
         */
        if (preg_match("/(.*)\/upload\/connected_files\/(.*)/", $url, $matches)) {
            return $url;
        }

        $objectUrl  = false;
        $originUrl  = "";
        $targetPath = $url;

        if (preg_match("/(.*)\/upload\/temp\/(\d*)(.*)/", $url, $matches)) {
            /**
             * ! We MUST rebuild the originUrl as well in case we have a /temp/ file
             * ! because sometimes the URLs sent by alpha won't have a slash after the
             * ! temp token, resulting in a wrong file path.
             *
             * * Here we're *trying* to overcome this issue, although it's not possible
             * * to have a 100% success rate because:
             *
             * * 1. Alpha's "random" folder number is not consistent at all, so we may
             * *    have some variations in length, therefore we MUST capture 0-N digits
             * *    after /temp/.
             *
             * * 2. If the actual file name begins with a number, this strategy will
             * *    also fail, because of the rule described above.
             */
            $originUrl = sprintf(
                '%s/upload/temp/%s/%s',
                $matches[1],
                $matches[2],
                basename($matches[3])
            );
            $targetPath = sprintf(
                'upload/connected_files/%s/%s',
                $orderItemId,
                basename($matches[3])
            );

            $targetUrl    = $matches[1] . $targetPath;
            $originExists = $this->fileExists($bucket, $originUrl);
            $targetExists = $this->fileExists($bucket, $targetUrl);

            if (!$originExists && $targetExists) {
                return $targetUrl;
            }

            if (!$originExists && !$targetExists) {
                throw new S3Exception(S3Exception::TYPE_FILES_DOESNT_EXIST);
            }

            return $this->copyFile($bucket, $originUrl, $targetPath);
        }
    }

    /**
     * This method
     *
     * @param string $url
     *
     * @return bool
     */
    public function fileExists(string $bucket, string $url): bool
    {
        $urlInfo = $this->getUrlInfo($url);
        if (!$urlInfo) {
            return false;
        }

        return $this->getClient($bucket)->doesObjectExist(
            $urlInfo['bucket'],
            $urlInfo['key']
        );
    }

    /**
     * This method strips all the useful information from an S3 file URL:
     * - type  : Can be "vHost" or "path"
     * - bucket: The bucket name
     * - key   : The actual object key
     *
     * @param string $url
     *
     * @return array
     */
    public function getUrlInfo(string $url): array
    {
        /**
         * * We can receive a different number of URL styles from S3. We have 2 main
         * * variatons:
         *
         * * - virtual-hosted style:
         * *      - https://bucket.s3.amazonaws.com/path/to/object
         * *      - https://bucket.s3-aws-region.amazonaws.com/path/to/object
         *
         * * - path style:
         * *      - https://s3.amazonaws.com/bucket/path/to/object
         * *          - This one is used specifically for the us-east-1 region
         * *      - http://s3-aws-region.amazonaws.com/bucket/path/to/object
         *
         * * So, in order to make sure we're able to extract the bucket and object key
         * * from the URL we needed two different regex expressions, one for each URL
         * * style.
         *
         * ! Note: the order here is important, because the "path" style will match
         * !       vHost as well, but the matches won't make sense here. Because of
         * !       that, we MUST check vHost before path.
         */
        $regex = [
            'vHost' => [
                'exp' => '/(?:[https:\/\/]*)([^.]+)\.s3(.*)\.amazonaws\.com\/(.*)/',
                'idx' => ['bucket' => 1, 'key' => 3],
            ],
            'path'  => [
                'exp' => '/(?:[\S]*)\.amazonaws\.com\/([^\/]*)\/(.*)/',
                'idx' => ['bucket' => 1, 'key' => 2],
            ],
        ];

        foreach ($regex as $type => $info) {
            if (preg_match($info['exp'], $url, $matches)) {
                return [
                    'originalUrl' => $url,
                    'type'        => $type,
                    'bucket'      => $matches[$info['idx']['bucket']],
                    'key'         => $matches[$info['idx']['key']],
                ];
            }
        }

        return [];
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
        $bucketName = $this->getS3BucketName($bucket);
        $response   = $this->getClient($bucket)->copyObject([
            'Bucket'     => $bucketName,
            'CopySource' => $originPath,
            'Key'        => $targetPath,
        ]);

        return $response['ObjectURL'] ?? '';
    }

    /**
     * Get s3 bucket name by reference
     *
     * @param string $bucketReference
     *
     * @return string
     */
    public function getS3BucketName(string $bucketReference): string
    {
        return $this->getResourceConfig($bucketReference)['bucket'];
    }

    /**
     * Get s3 bucket file url
     *
     * @param string $bucketReference
     * @param string $key
     *
     * @return string
     */
    public function getS3BucketFileUrl(string $bucketReference, string $key): string
    {
        $bucketName = $this->getS3BucketName($bucketReference);
        return $this->getClient($bucketReference)->getObjectUrl($bucketName, $key);
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
