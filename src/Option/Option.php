<?php

namespace Oxidize\Option;

use Closure;
use Oxidize\Error\ValueAccessError;
use Oxidize\Result\Result;

final readonly class Option
{
    private function __construct(
        private OptionType $type,
        private mixed $some = null,
    ) { }

    public static function some(mixed $some): Option
    {
        return new Option(OptionType::SOME, $some);
    }

    public static function none(): Option
    {
        return new Option(OptionType::NONE);
    }

    public function isSome(): bool
    {
        return $this->type === OptionType::SOME;
    }

    public function isNone(): bool
    {
        return !$this->isSome();
    }

    public function isSomeAnd(Closure $invokable): bool
    {
        if ($this->isNone()) {
            return false;
        }
        return $invokable($this->some);
    }

    public function unwrap(): mixed
    {
        if (!$this->isSome()) {
            throw new ValueAccessError(sprintf('Called `unwrap` on %1$s Option.', $this->type->name));
        }
        return $this->some;
    }

    public function unwrapOr(mixed $fallback)
    {
        if ($this->isSome()) {
            return $this->some;
        }
        return $fallback;
    }

    public function map(Closure $invokable): Option
    {
        if ($this->isSome()) {
            return Option::some($invokable($this->some));
        }
        return Option::none();
    }

    public function mapOr(mixed $fallback, Closure $invokable): mixed
    {
        if ($this->isSome()) {
            return $invokable($this->some);
        }
        return $fallback;
    }

    public function mapOrElse(Closure $fallback, Closure $invokable): mixed
    {
        if ($this->isSome()) {
            return $invokable($this->some);
        }
        return $fallback();
    }

    public function okOr(mixed $error): Result
    {
        if ($this->isSome()) {
            return Result::ok($this->some);
        }
        return Result::error($error);
    }

    public function okOrElse(Closure $invokable): Result
    {
        if ($this->isSome()) {
            return Result::ok($this->some);
        }
        return Result::error($invokable());
    }

    public function andThen(Closure $invokable): Option
    {
        if ($this->isSome()) {
            return $invokable($this->some);
        }
        return Option::none();
    }

    public function andThenContinue(Closure $invokable): Option
    {
        if ($this->isSome()) {
            $invokable($this->some);
            return Option::some($this->some);
        }
        return Option::none();
    }

    public function and(Option $option): Option
    {
        if ($this->isSome()) {
            return $option;
        }
        return Option::none();
    }

    public function filter(Closure $predicate): Option
    {
        if ($this->isSomeAnd(fn() => $predicate($this->some))) {
            return Option::some($this->some);
        }
        return Option::none();
    }

    public function or(Option $option): Option
    {
        if ($this->isSome()) {
            return Option::some($this->some);
        }
        return $option;
    }

    public function orElse(Closure $invokable): Option
    {
        if ($this->isSome()) {
            return Option::some($this->some);
        }
        return $invokable();
    }

    public function orElseContinue(Closure $invokable): Option
    {
        if ($this->isSome()) {
            return Option::some($this->some);
        }
        $invokable();
        return Option::none();
    }
}
