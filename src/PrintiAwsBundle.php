<?php

namespace Printi\AwsBundle;

use Printi\AwsBundle\DependencyInjection\PrintiAwsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PrintiAwsBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new PrintiAwsExtension();
    }
}
