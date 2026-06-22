<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources;

use ZJKiza\ResponseProcessor\Tests\Resources\App\ZJKizaResponseProcessorBundleTestKernel;

class KernelTestCase extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return ZJKizaResponseProcessorBundleTestKernel::class;
    }
}
