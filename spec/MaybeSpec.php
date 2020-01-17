<?php

declare(strict_types=1);

namespace Marcosh\LamPHPdaSpec;

use Marcosh\LamPHPda\Maybe;

describe('Maybe', function () {
    it('uses nothing case when nothing', function () {
        $maybe = Maybe::nothing();

        $result = $maybe->eval(
            42,
            function ($value) {
                return $value;
            }
        );

        expect($result)->toEqual(42);
    });

    it('uses just case when just', function () {
        $maybe = Maybe::just(42);

        $result = $maybe->eval(
            0,
            function ($value) {
                return $value * 2;
            }
        );

        expect($result)->toEqual(84);
    });
});