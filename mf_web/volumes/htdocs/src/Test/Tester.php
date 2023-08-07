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