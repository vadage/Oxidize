<?php

namespace Oxidize\Test\Option;

use Oxidize\Error\ValueAccessError;
use Oxidize\Option\Option;
use PHPUnit\Framework\TestCase;

final class OptionTest extends TestCase
{
    public function testIsSome(): void
    {
        $option = Option::some('Foo');
        $this->assertTrue($option->isSome());
    }

    public function testIsNone(): void
    {
        $option = Option::none();
        $this->assertTrue($option->isNone());
    }

    public function testIsSomeAndWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertTrue($option->isSomeAnd(fn(string $s) => $s === 'Foo'));
    }

    public function testIsSomeAndWithNone(): void
    {
        $option = Option::none();
        $this->assertFalse($option->isSomeAnd(fn(string $s) => $s === 'Foo'));
    }

    public function testUnwrapWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('Foo', $option->unwrap());
    }

    public function testUnwrapWithNone(): void
    {
        $option = Option::none();
        $this->expectException(ValueAccessError::class);
        $option->unwrap();
    }

    public function testUnwrapOrWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('Foo', $option->unwrapOr('Bar'));
    }

    public function testUnwrapOrWithNone(): void
    {
        $option = Option::none();
        $this->assertEquals('Foo', $option->unwrapOr('Foo'));
    }

    public function testMapWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('FOO', $option->map(fn(string $s) => strtoupper($s))->unwrap());
    }

    public function testMapWithNone(): void
    {
        $option = Option::none();
        $this->assertTrue($option->map(fn(string $s) => strtoupper($s))->isNone());
    }

    public function testMapOrWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('FOO', $option->mapOr('Bar', fn(string $s) => strtoupper($s)));
    }

    public function testMapOrWithNone(): void
    {
        $option = Option::none();
        $this->assertEquals('Foo', $option->mapOr('Foo', fn(string $s) => strtoupper($s)));
    }

    public function testMapOrElseWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('FOO', $option->mapOrElse(fn() => 'Bar', fn(string $s) => strtoupper($s)));
    }

    public function testMapOrElseWithNone(): void
    {
        $option = Option::none();
        $this->assertEquals('Foo', $option->mapOrElse(fn() => 'Foo', fn(string $s) => strtoupper($s)));
    }

    public function testOkOrWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('Foo', $option->okOr('Bar')->unwrap());
    }

    public function testOkOrWithNone(): void
    {
        $option = Option::none();
        $this->assertEquals('Foo', $option->okOr('Foo')->unwrapError());
    }

    public function testOkOrElseWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('Foo', $option->okOrElse(fn() => 'Bar')->unwrap());
    }

    public function testOkOrElseWithNone(): void
    {
        $option = Option::none();
        $this->assertEquals('Foo', $option->okOrElse(fn() => 'Foo')->unwrapError());
    }

    public function testAndThenWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('FOO', $option->andThen(fn(string $s) => Option::some(strtoupper($s)))->unwrap());
    }

    public function testAndThenWithNone(): void
    {
        $option = Option::none();
        $this->assertTrue($option->andThen(fn(string $s) => Option::some(strtoupper($s)))->isNone());
    }

    public function testAndThenContinueWithSome(): void
    {
        $reference = false;
        $option = Option::some('Foo');

        $option->andThenContinue(function (string $s) use (&$reference) {
            $reference = $s === 'Foo';
        });

        $this->assertTrue($reference);
    }

    public function testAndThenContinueWithNone(): void
    {
        $reference = false;
        $option = Option::none();

        $option->andThenContinue(function (string $s) use (&$reference) {
            $reference = $s === 'Foo';
        });

        $this->assertFalse($reference);
    }

    public function testAndWithSome(): void
    {
        $option = Option::some('Foo');
        $comparingOption = Option::some('Bar');

        $this->assertEquals('Bar', $option->and($comparingOption)->unwrap());
    }

    public function testAndWithNone(): void
    {
        $option = Option::none();
        $comparingOption = Option::some('Foo');

        $this->assertTrue($option->and($comparingOption)->isNone());
    }

    public function testFilterMatching(): void
    {
        $option = Option::some('Foo');
        $this->assertTrue($option->filter(fn(string $s) => $s === 'Foo')->isSome());
    }

    public function testFilterNonMatch(): void
    {
        $option = Option::some('Foo');
        $this->assertTrue($option->filter(fn(string $s) => $s === 'Bar')->isNone());
    }

    public function testOrWithSome(): void
    {
        $option = Option::some('Foo');
        $comparingOption = Option::some('Bar');

        $this->assertEquals('Foo', $option->or($comparingOption)->unwrap());
    }

    public function testOrWithNone(): void
    {
        $option = Option::none();
        $comparingOption = Option::some('Foo');

        $this->assertEquals('Foo', $option->or($comparingOption)->unwrap());
    }

    public function testOrElseWithSome(): void
    {
        $option = Option::some('Foo');
        $this->assertEquals('Foo', $option->orElse(fn() => Option::some('Bar'))->unwrap());
    }

    public function testOrElseWithNone(): void
    {
        $option = Option::none();
        $this->assertEquals('Foo', $option->orElse(fn() => Option::some('Foo'))->unwrap());
    }

    public function testOrElseContinueWithSome(): void
    {
        $reference = false;
        $option = Option::some('Foo');

        $option->orElseContinue(function () use (&$reference) {
            $reference = true;
        });

        $this->assertFalse($reference);
    }

    public function testOrElseContinueWithNone(): void
    {
        $reference = false;
        $option = Option::none();

        $option->orElseContinue(function () use (&$reference) {
            $reference = true;
        });

        $this->assertTrue($reference);
    }
}
