<?php

namespace Oxidize\Result;

use Closure;
use Oxidize\Error\ValueAccessError;

final readonly class Result
{
    private function __construct(
        private ResultType $type,
        private mixed $ok = null,
        private mixed $error = null,
    ) { }

    public static function ok(mixed $ok = null): Result
    {
        return new Result(ResultType::OK, ok: $ok);
    }

    public static function error(mixed $error = null): Result
    {
        return new Result(ResultType::ERROR, error: $error);
    }

    public function unwrap(): mixed
    {
        if (!$this->isOk()) {
            throw new ValueAccessError(sprintf('Called `unwrap` on %1$s Result.', $this->type->name));
        }
        return $this->ok;
    }

    public function unwrapError(): mixed
    {
        if (!$this->isError()) {
            throw new ValueAccessError(sprintf('Called `unwrapError` on %1$s Result.', $this->type->name));
        }
        return $this->error;
    }

    public function isOk(): bool
    {
        return $this->type === ResultType::OK;
    }

    public function isOkAnd(Closure $invokable): bool
    {
        if ($this->isError()) {
            return false;
        }
        return $invokable($this->ok);
    }

    public function isError(): bool
    {
        return !$this->isOk();
    }

    public function isErrorAnd(Closure $invokable): bool
    {
        if ($this->isOk()) {
            return false;
        }
        return $invokable($this->error);
    }

    public function map(Closure $invokable): Result
    {
        if ($this->isOk()) {
            return Result::ok($invokable($this->ok));
        }
        return Result::error($this->error);
    }

    public function mapOr(mixed $fallback, Closure $invokable): mixed
    {
        if ($this->isOk()) {
            return $invokable($this->ok);
        }
        return $fallback;
    }

    public function mapOrElse(Closure $fallback, Closure $invokable): mixed
    {
        if ($this->isOk()) {
            return $invokable($this->ok);
        }
        return $fallback($this->error);
    }

    public function and(Result $result): Result
    {
        if ($this->isOk()) {
            return $result;
        }
        return Result::error($this->error);
    }

    public function andThen(Closure $invokable): Result
    {
        if ($this->isOk()) {
            return $invokable($this->ok);
        }
        return Result::error($this->error);
    }

    public function andThenContinue(Closure $invokable): Result
    {
        if ($this->isOk()) {
            $invokable($this->ok);
            return Result::ok($this->ok);
        }
        return Result::error($this->error);
    }

    public function or(Result $result): Result
    {
        if ($this->isOk()) {
            return Result::ok($this->ok);
        }
        return $result;
    }

    public function orElse(Closure $invokable): Result
    {
        if ($this->isOk()) {
            return Result::ok($this->ok);
        }
        return $invokable($this->error);
    }

    public function orElseContinue(Closure $invokable): Result
    {
        if ($this->isOk()) {
            return Result::ok($this->ok);
        }
        $invokable($this->error);
        return Result::error($this->error);
    }

    public function unwrapOrElse(Closure $invokable): mixed
    {
        if ($this->isOk()) {
            return $this->ok;
        }
        return $invokable($this->error);
    }
}
