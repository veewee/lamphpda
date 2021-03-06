<?php

declare(strict_types=1);

namespace Marcosh\LamPHPda;

use Marcosh\LamPHPda\Brand\EitherBrand;
use Marcosh\LamPHPda\HK\HK;
use Marcosh\LamPHPda\Typeclass\Applicative;
use Marcosh\LamPHPda\Typeclass\Apply;
use Marcosh\LamPHPda\Typeclass\Functor;

/**
 * @template A
 * @template B
 * @implements Functor<EitherBrand, B>
 * @implements Apply<EitherBrand, B>
 * @implements Applicative<EitherBrand, B>
 * @psalm-immutable
 */
final class Either implements Functor, Apply, Applicative
{
    /** @var bool */
    private $isRight;

    /**
     * @var mixed
     * @psalm-var A|null
     */
    private $leftValue;

    /**
     * @var mixed
     * @psalm-var B|null
     */
    private $rightValue;

    /**
     * @param bool $isRight
     * @param mixed $leftValue
     * @psalm-param A|null $leftValue
     * @param mixed $rightValue
     * @psalm-param B|null $rightValue
     */
    private function __construct(bool $isRight, $leftValue = null, $rightValue = null)
    {
        $this->isRight = $isRight;
        $this->leftValue = $leftValue;
        $this->rightValue = $rightValue;
    }

    /**
     * @template C
     * @template D
     * @param mixed $value
     * @psalm-param C $value
     * @return Either
     * @psalm-return Either<C, D>
     * @psalm-pure
     */
    public static function left($value): Either
    {
        return new self(false, $value);
    }

    /**
     * @template C
     * @template D
     * @param mixed $value
     * @psalm-param D $value
     * @return Either
     * @psalm-return Either<C, D>
     * @psalm-pure
     */
    public static function right($value): Either
    {
        return new self(true, null, $value);
    }

    /**
     * @template C
     * @template D
     * @param HK $hk
     * @psalm-param HK<EitherBrand, D> $hk
     * @return Either
     * @psalm-return Either<C, D>
     * @psalm-pure
     */
    private static function fromBrand(HK $hk): Either
    {
        /** @var Either $hk */
        return $hk;
    }

    /**
     * @template C
     * @param callable $ifLeft
     * @psalm-param callable(A): C $ifLeft
     * @param callable $ifRight
     * @psalm-param callable(B): C $ifRight
     * @return mixed
     * @psalm-return C
     * @psalm-pure
     */
    public function eval(
        callable $ifLeft,
        callable $ifRight
    ) {
        if ($this->isRight) {
            /** @psalm-suppress PossiblyNullArgument */
            return $ifRight($this->rightValue);
        }

        /** @psalm-suppress PossiblyNullArgument */
        return $ifLeft($this->leftValue);
    }

    /**
     * @template C
     * @param callable $f
     * @psalm-param callable(B): C $f
     * @return Either
     * @psalm-return Either<A, C>
     * @psalm-pure
     */
    public function map(callable $f): Either
    {
        return $this->eval(
            /**
             * @psalm-param A $value
             * @psalm-return Either<A, C>
             */
            fn($value) => self::left($value),
            /**
             * @psalm-param B $value
             * @psalm-return Either<A, C>
             */
            fn($value) => self::right($f($value))
        );
    }

    /**
     * @template C
     * @param Apply $f
     * @psalm-param Apply<EitherBrand, callable(B): C> $f
     * @return Either
     * @psalm-return Either<A, C>
     * @psalm-pure
     */
    public function apply(Apply $f): Either
    {
        $f = self::fromBrand($f);

        return $f->eval(
            (/**
             * @psalm-param A $a
             * @psalm-return Either<A, C>
             */
            fn($a) => self::left($a)),
            (/**
             * @psalm-param callable(B): C $f
             * @psalm-return Either<A, C>
             */
            fn($f) => $this->eval(
                (/**
                 * @psalm-param A $a
                 * @psalm-return Either<A, C>
                 */
                fn($a) => self::left($a)),
                (/**
                 * @psalm-param B $b
                 * @psalm-return Either<A, C>
                 */
                fn($b) => self::right($f($b)))
            ))
        );
    }

    /**
     * @template C
     * @template D
     * @param mixed $a
     * @psalm-param D $a
     * @return Either
     * @psalm-return Either<C, D>
     * @psalm-pure
     */
    public static function pure($a): Either
    {
        return Either::right($a);
    }

    /**
     * @return bool
     * @psalm-pure
     */
    public function isLeft(): bool
    {
        return $this->eval(
            /**
             * @psalm-param A $left
             * @psalm-return bool
             */
            fn($left) => true,
            /**
             * @psalm-param B $right
             * @psalm-return bool
             */
            fn($right) => false
        );
    }

    /**
     * @return bool
     * @psalm-pure
     */
    public function isRight(): bool
    {
        return $this->eval(
            /**
             * @psalm-param A $left
             * @psalm-return bool
             */
            fn($left) => false,
            /**
             * @psalm-param A $right
             * @psalm-return bool
             */
            fn($right) => true
        );
    }
}
