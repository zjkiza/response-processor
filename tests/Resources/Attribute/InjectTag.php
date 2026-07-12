<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Tests\Resources\Attribute;

use ZJKiza\ResponseProcessor\Attribute\InjectDataAbstract;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class InjectTag extends InjectDataAbstract
{
}
