<?php

declare(strict_types=1);

use Tkawen\ShippingDz\Support\Wilayas;

it('has all 69 wilayas in both scripts', function () {
    expect(Wilayas::count())->toBe(69)
        ->and(Wilayas::AR)->toHaveCount(69)
        ->and(Wilayas::LATIN)->toHaveCount(69);
});

it('resolves names by id', function () {
    expect(Wilayas::ar(16))->toBe('الجزائر')
        ->and(Wilayas::latin(16))->toBe('Alger')
        ->and(Wilayas::latin(23))->toBe('Annaba')
        ->and(Wilayas::latin(69))->toBe('El Abiodh Sidi Cheikh');
});

it('reverse-resolves names (accent-insensitive) to ids', function () {
    expect(Wilayas::idFromName('Alger'))->toBe(16)
        ->and(Wilayas::idFromName('Bejaia'))->toBe(6)   // no accent
        ->and(Wilayas::idFromName('Béjaïa'))->toBe(6)   // with accents
        ->and(Wilayas::idFromName('الجزائر'))->toBe(16)
        ->and(Wilayas::idFromName('nope'))->toBeNull();
});

it('flags the 11 post-2025 wilayas', function () {
    expect(Wilayas::isPost2025(59))->toBeTrue()
        ->and(Wilayas::isPost2025(69))->toBeTrue()
        ->and(Wilayas::isPost2025(58))->toBeFalse()
        ->and(Wilayas::POST_2025)->toHaveCount(11);
});
