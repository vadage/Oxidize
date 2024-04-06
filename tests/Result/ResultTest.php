<?php

namespace Oxidize\Test\Result;

use Oxidize\Error\ValueAccessError;
use Oxidize\Result\Result;
use PHPUnit\Framework\TestCase;

final class ResultTest extends TestCase
{
    public function testUnwrapWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->assertSame('Foo', $result->unwrap());
    }

    public function testUnwrapWithError(): void
    {
        $result = Result::error('Foo');
        $this->expectException(ValueAccessError::class);
        $result->unwrap();
    }

    public function testUnwrapErrorWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->expectException(ValueAccessError::class);
        $result->unwrapError();
    }

    public function testUnwrapErrorWithError(): void
    {
        $result = Result::error('Foo');
        $this->assertEquals('Foo', $result->unwrapError());
    }

    public function testIsOkWithOk(): void
    {
        $result = Result::ok();
        $this->assertTrue($result->isOk());
    }

    public function testIsOkWithError(): void
    {
        $result = Result::error();
        $this->assertFalse($result->isOk());
    }

    public function testIsOkAndWithOk(): void
    {
        $result = Result::ok();
        $this->assertFalse($result->isOkAnd(fn() => false));
    }

    public function testIsOkAndWithError(): void
    {
        $result = Result::error();
        $this->assertFalse($result->isOkAnd(fn() => true));
    }

    public function testIsErrorWithOk(): void
    {
        $result = Result::ok();
        $this->assertFalse($result->isError());
    }

    public function testIsErrorWithError(): void
    {
        $result = Result::error();
        $this->assertTrue($result->isError());
    }

    public function testIsErrorAndWithOk(): void
    {
        $result = Result::ok();
        $this->assertFalse($result->isErrorAnd(fn() => true));
    }

    public function testIsErrorAndWithError(): void
    {
        $result = Result::error();
        $this->assertFalse($result->isErrorAnd(fn() => false));
    }

    public function testMapWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->assertEquals('FOO', $result->map(fn(string $s) => strtoupper($s))->unwrap());
    }

    public function testMapWithError(): void
    {
        $result = Result::error();
        $this->assertTrue($result->map(fn(string $s) => strtoupper($s))->isError());
    }

    public function testMapOrWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->assertEquals('FOO', $result->mapOr('Bar', fn(string $s) => strtoupper($s)));
    }

    public function testMapOrWithError(): void
    {
        $result = Result::error('Foo');
        $this->assertEquals('Bar', $result->mapOr('Bar', fn(string $s) => strtoupper($s)));
    }

    public function testMapOrElseWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->assertEquals('FOO', $result->mapOrElse(fn() => 'Bar', fn(string $s) => strtoupper($s)));
    }

    public function testMapOrElseWithError(): void
    {
        $result = Result::error('Foo');
        $this->assertEquals('Bar', $result->mapOrElse(fn() => 'Bar', fn(string $s) => strtoupper($s)));
    }

    public function testAndWithOk(): void
    {
        $result = Result::ok('Foo');
        $comparingResult = Result::ok('Bar');

        $this->assertEquals('Bar', $result->and($comparingResult)->unwrap());
    }

    public function testAndWithError(): void
    {
        $result = Result::error('Foo');
        $comparingResult = Result::ok('Bar');

        $this->assertTrue($result->and($comparingResult)->isError());
    }

    public function testAndThenWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->assertEquals('FOO', $result->andThen(fn(string $s) => Result::ok(strtoupper($s)))->unwrap());
    }

    public function testAndThenWithError(): void
    {
        $result = Result::error('Foo');
        $this->assertTrue($result->andThen(fn(string $s) => Result::ok(strtoupper($s)))->isError());
    }

    public function testAndThenContinueWithOk(): void
    {
        $reference = false;
        $result = Result::ok('Foo');

        $result->andThenContinue(function (string $s) use (&$reference) {
            $reference = $s === 'Foo';
        });

        $this->assertTrue($reference);
    }

    public function testAndThenContinueWithError(): void
    {
        $reference = false;
        $result = Result::error('Foo');

        $result->andThenContinue(function (string $s) use (&$reference) {
            $reference = $s === 'Foo';
        });

        $this->assertFalse($reference);
    }

    public function testOrWithOk(): void
    {
        $result = Result::ok('Foo');
        $comparingResult = Result::ok('Bar');

        $this->assertEquals('Foo', $result->or($comparingResult)->unwrap());
    }

    public function testOrWithError(): void
    {
        $result = Result::error('Foo');
        $comparingResult = Result::ok('Bar');

        $this->assertEquals('Bar', $result->or($comparingResult)->unwrap());
    }

    public function testOrElseWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->assertEquals('Foo', $result->orElse(fn() => Result::ok('Bar'))->unwrap());
    }

    public function testOrElseWithError(): void
    {
        $result = Result::error('Foo');
        $this->assertEquals('Bar', $result->orElse(fn() => Result::ok('Bar'))->unwrap());
    }

    public function testOrElseContinueWithOk(): void
    {
        $reference = false;
        $result = Result::ok('Foo');

        $result->orElseContinue(function (string $s) use (&$reference) {
            $reference = $s === 'Foo';
        });

        $this->assertFalse($reference);
    }

    public function testOrElseContinueWithError(): void
    {
        $reference = false;
        $result = Result::error('Foo');

        $result->orElseContinue(function (string $s) use (&$reference) {
            $reference = $s === 'Foo';
        });

        $this->assertTrue($reference);
    }

    public function testUnwrapOrElseWithOk(): void
    {
        $result = Result::ok('Foo');
        $this->assertEquals('Foo', $result->unwrapOrElse(fn() => 'Bar'));
    }

    public function testUnwrapOrElseWithError(): void
    {
        $result = Result::error('Foo');
        $this->assertEquals('Bar', $result->unwrapOrElse(fn() => 'Bar'));
    }
}
