<?php

namespace Luimedi\Remap\Helpers;

final class Data
{
    public static function get(mixed $target, string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);

        foreach ($segments as $segment) {
            $found = false;
            $target = self::extract($target, $segment, $found, $default);
            if (!$found) {
                return $default;
            }
        }

        return $target;
    }

    private static function extract(mixed $target, string $segment, bool &$found, mixed $default): mixed
    {
        if (is_array($target) && array_key_exists($segment, $target)) {
            $found = true;
            return $target[$segment];
        }

        if ($target instanceof \ArrayAccess && $target->offsetExists($segment)) {
            $found = true;
            return $target[$segment];
        }

        if (is_object($target)) {
            return self::extractFromObject($target, $segment, $found, $default);
        }

        $found = false;
        return $default;
    }

    private static function extractFromObject(object $target, string $segment, bool &$found, mixed $default): mixed
    {
        $ref = new \ReflectionClass($target);

        if ($ref->hasProperty($segment)) {
            $prop = $ref->getProperty($segment);
            if ($prop->isInitialized($target)) {
                $found = true;
                return $prop->getValue($target);
            }
            $found = false;
            return $default;
        }

        if (isset($target->$segment) || property_exists($target, $segment)) {
            $found = true;
            return $target->$segment;
        }

        if (method_exists($target, '__get')) {
            try {
                $val = $target->$segment;
                if ($val === null && !(method_exists($target, '__isset') && $target->__isset($segment))) {
                    $found = false;
                    return $default;
                }
                $found = true;
                return $val;
            } catch (\Throwable) {
                // fall through to not found
            }
        }

        $found = false;
        return $default;
    }
}
