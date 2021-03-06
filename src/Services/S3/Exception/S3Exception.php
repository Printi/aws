<?php

namespace Printi\AwsBundle\Services\S3\Exception;

use Printi\AwsBundle\Exception\AbstractException;

/**
 * Class S3Exception
 */
class S3Exception extends AbstractException
{
    const TYPE_S3_BUCKET_NAME_CANNOT_BLANK      = "S3_BUCKET_NAME_CANNOT_BLANK";
    const TYPE_S3_BUCKET_CONFIG_NOT_FOUND       = "S3_BUCKET_CONFIG_NOT_FOUND";
    const TYPE_S3_BUCKET_NOT_FOUND              = "S3_BUCKET_NOT_FOUND";
    const TYPE_FILES_DOESNT_EXIST               = "FILES_DOESNT_EXIST";
    const TYPE_S3_DIRECTORY_NOT_FOUND           = "S3_DIRECTORY_NOT_FOUND";
    const TYPE_COULD_NOT_GET_INFO_FROM_FILE_URL = "COULD_NOT_GET_INFO_FROM_FILE_URL";

    /**
     * @inheritDoc
     */
    protected function populateCodeMap()
    {
        $this->codeMap = [
            self::TYPE_S3_BUCKET_NAME_CANNOT_BLANK      => 400,
            self::TYPE_S3_BUCKET_CONFIG_NOT_FOUND       => 400,
            self::TYPE_S3_BUCKET_NOT_FOUND              => 400,
            self::TYPE_FILES_DOESNT_EXIST               => 400,
            self::TYPE_S3_DIRECTORY_NOT_FOUND           => 400,
            self::TYPE_COULD_NOT_GET_INFO_FROM_FILE_URL => 400,
        ];
    }
}
