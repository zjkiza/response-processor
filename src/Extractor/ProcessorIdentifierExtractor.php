<?php

declare(strict_types=1);

namespace ZJKiza\ResponseProcessor\Extractor;

use ZJKiza\ResponseProcessor\Attribute\ProcessorIdentifier;
use ZJKiza\ResponseProcessor\Contract\IdentifierExtractorInterface;

class ProcessorIdentifierExtractor implements IdentifierExtractorInterface
{
    private const ATTRIBUTE_FOR_IDENTIFIER = ProcessorIdentifier::class;

    /** @var array<class-string, \ReflectionProperty|null> */
    private array $propertyCache = [];

    public function extract(object $object): ?string
    {
        $property = $this->resolveProperty($object);

        if ($property === null) {
            return null;
        }

        $value = $property->getValue($object);

        return $value !== null ? (string) $value : null;
    }

    private function resolveProperty(object $object): ?\ReflectionProperty
    {
        $class = $object::class;

        if (isset($this->propertyCache[$class])) {
            return $this->propertyCache[$class];
        }

        $reflection = new \ReflectionClass($object);

        foreach ($reflection->getProperties() as $property) {
            if (!empty($property->getAttributes(self::ATTRIBUTE_FOR_IDENTIFIER))) {
                $this->propertyCache[$class] = $property;

                return $property;
            }
        }

        return $this->propertyCache[$class] = null;
    }
}
