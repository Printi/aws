<?php

namespace Printi\AwsBundle\Exception;

/**
 * BaseException
 */
abstract class AbstractException extends \Exception
{
    /** @var array The HTTP Status code map */
    protected $codeMap = [];

    /**
     * @param string $msg
     * @param int    $code
     */
    public function __construct(string $msg, int $code = null)
    {
        $this->populateCodeMap();

        $code = $code ?? $this->codeMap[$msg];

        parent::__construct($msg, $code ?? 400);
    }

    /**
     * This method should be used to populate the HTTP Status
     * code map so we can use it to both translate and properly code our exceptions.
     *
     * @return void
     */
    abstract protected function populateCodeMap();
}
