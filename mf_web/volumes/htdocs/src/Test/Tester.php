<?php

namespace MF\Test;

use AssertionError;
use Closure;
use Exception;

class Tester
{
    private array $errors;

    public function __construct()
    {
        $this->errors = [];
    }

    public function assertEquals(mixed $actual, mixed $expected, ?string $message = null): bool
    {
        $expectedType = gettype($expected);
        $actualType = gettype($actual);
        if ($expected !== $actual) {
            $this->errors[] = new AssertionFailure(
                $message ?? "Equality assertion between $expectedType and $actualType failed.",
                "Expected : " . var_export($expected, true) . "\n" .
                "Got : " . var_export($actual, true)
            );
            return false;
        }
        return true;
    }

    public function assertArrayEquals(array $expected, array $actual, ?string $message = null): bool
    {
        $diffExpectedActual = $this->getSetDifference($expected, $actual);
        $diffActualExpected = $this->getSetDifference($actual, $expected);
        if ([] !== $diffActualExpected || [] !== $diffExpectedActual) {
            $this->errors[] = new AssertionFailure(
                $message ?? "The actual array differs from the expected array.",
                "Additional keys in expected: " . var_export($diffExpectedActual, true) . "\n" .
                "Additional keys in actual: " . var_export($diffActualExpected, true)
            );
            return false;
        }
        return true;
    }

    public function assertArraySize(array $actual, int $expectedSize, ?string $message = null): bool
    {
        $actualSize = count($actual);
        if ($actualSize !== $expectedSize) {
            $this->errors[] = new AssertionFailure(
                $message ?? 'The actual array does not have the expected length.',
                "Expected size {$expectedSize}, got a size of {$actualSize}.",
            );
            return false;
        }
        return true;
    }

    public function assertNull(mixed $actual, ?string $message = null): bool
    {
        if (null !== $actual) {
            $this->errors[] = new AssertionError(
                $message ?? 'Actual data is not null.',
                'Expected null data, got variable of type ' . gettype($actual) . '.',
            );
            return false;
        }

        return true;
    }

    public function assertStringContains(mixed $actual, string $expectedNeedle, ?string $message = null): bool
    {
        if (!str_contains($actual, $expectedNeedle)) {
            $this->errors[] = new AssertionFailure(
                $message ?? "String does not contain expected needle.",
                "Expected to find {$expectedNeedle} in {$actual}.",
            );
            return false;
        }
        return true;
    }

    public function getSetDifference(array $subset, array $superset): array
    {
        $diffs = [];
        foreach ($subset as $key => $value) {
            if (!key_exists($key, $superset)) {
                $diffs[] = $key;
            } elseif (is_array($value)) {
                if ([] !== $this->getSetDifference($value, $superset[$key]) || [] !== $this->getSetDifference($superset[$key], $value)) {
                    $diffs[] = $key;
                }
            } elseif ($value instanceof \DateTimeImmutable) {
                if (0 !== $value->getTimestamp() - $superset[$key]->getTimestamp()) {
                    $diffs[] = $key;
                }
            } elseif ($value !== $superset[$key]) {
                $diffs[] = $key;
            }
        }
        return $diffs;
    }

    public function assertException(string $expectedExceptionClass, Closure $statement, ?string $message = null): bool
    {
        try {
            $statement->call($this);
        } catch (Exception $e) {
            if ($e instanceof $expectedExceptionClass) {
                return true;
            }
            else {
                $actualExceptionClass = get_class($e);
                
                $this->errors[] = new AssertionFailure(
                    $message ?? "Raised exception not of the expected type.",
                    "Expected :$expectedExceptionClass. Got : $actualExceptionClass.",
                );
                return false;
            }
        }
        $this->errors[] = new AssertionFailure(
            $message ?? "No exceptions were raised.",
            "Expected : $expectedExceptionClass, but no exceptions were thrown.",
        );
        return false;
    }

    public function assertNoException(Closure $statement, ?string $message = null): bool
    {
        try {
            $statement->call($this);
        } catch (Exception $e) {
            $actualExceptionClass = get_class($e);
            $this->errors[] = new AssertionFailure(
                $message ?? "Raised exception not of the expected type.",
                "Expected : No exception. Got : $actualExceptionClass.\nMessage : " . $e->getMessage(),
            );
            return false;
        }
        return true;
    }

    public function assertTrue(mixed $variable, ?string $message = null): bool
    {
        if ($variable !== true) {
            $this->errors[] = new AssertionFailure(
                $message ?? 'Expected true, got ' . gettype($variable),
                'Actual is ' . var_export($variable, true)
            );
            return false;
        }
        return true;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}