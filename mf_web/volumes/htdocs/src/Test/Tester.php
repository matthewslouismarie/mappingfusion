<?php

namespace MF\Test;
use Closure;
use Exception;

class Tester
{
    private array $errors;

    public function __construct() {
        $this->errors = [];
    }

    public function assertEquals(mixed $expected, mixed $actual): bool {
        $expectedType = gettype($expected);
        $actualType = gettype($actual);
        if ($expected !== $actual) {
            $this->errors[] = new AssertionFailure(
                "Equality assertion between $expectedType and $actualType failed.",
                "Expected : " . var_export($expected, true) . "\n" .
                "Got : " . var_export($actual, true)
            );
            return false;
        }
        return true;
    }

    public function assertArrayEquals(array $expected, array $actual): bool {
        $diffExpectedActual = $this->getSetDifference($expected, $actual);
        $diffActualExpected = $this->getSetDifference($actual, $expected);
        if ([] !== $diffActualExpected || [] !== $diffExpectedActual) {
            $this->errors[] = new AssertionFailure(
                "The actual array differs from the expected array.",
                "Additional keys in expected: " . var_export($diffExpectedActual, true) . "\n" .
                "Additional keys in actual: " . var_export($diffActualExpected, true)
            );
            return false;
        }
        return true;
    }

    public function getSetDifference(array $subset, array $superset): array {
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

    public function assertException(string $exceptionClass, Closure $statement): bool {
        try {
            $statement->call($this);
        } catch (Exception $e) {
            $actualExceptionClass = get_class($e);
            if ($actualExceptionClass !== $exceptionClass) {
                $this->errors[] = new AssertionFailure(
                    "Raised exception not of the expected type.",
                    "Expected :$exceptionClass. Got : $actualExceptionClass.",
                );
                return false;
            }
            return true;
        }
        $this->errors[] = new AssertionFailure(
            "No exceptions were raised.",
            "Expected :$exceptionClass. Got nothing.",
        );
        return false;
    }

    public function assertNoException(Closure $statement): bool {
        try {
            $statement->call($this);
        } catch (Exception $e) {
            $actualExceptionClass = get_class($e);
            $this->errors[] = new AssertionFailure(
                "Raised exception not of the expected type.",
                "Expected : No exception. Got : $actualExceptionClass.\n".
                "Message : " . $e->getMessage(),
            );
            return false;
        }
        return true;
    }

    public function getErrors(): array {
        return $this->errors;
    }
}