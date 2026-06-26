<?php

declare(strict_types=1);

use Tkawen\ShippingDz\Support\Phone;

it('normalises DZ phones to 10-digit national form', function (string $in, string $out) {
    expect(Phone::national($in))->toBe($out);
})->with([
    ['0555123456', '0555123456'],
    ['+213 662 11 12 22', '0662111222'],
    ['00213770000000', '0770000000'],
    ['0770-00-00-00', '0770000000'],
    ['', ''],
]);

it('validates DZ mobile numbers', function () {
    expect(Phone::isValidMobile('0555123456'))->toBeTrue()
        ->and(Phone::isValidMobile('+213662111222'))->toBeTrue()
        ->and(Phone::isValidMobile('0211234567'))->toBeFalse()   // landline prefix
        ->and(Phone::isValidMobile('12345'))->toBeFalse();
});
