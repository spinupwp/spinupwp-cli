<?php

use App\Helpers\OptionsHelper;

test('domain slug ', function () {
    expect(OptionsHelper::getDomainSlug('this-is-a-very-long-subdomain.yoursite.com', 16))->toEqual('thisisaverylongs');

    expect(OptionsHelper::getDomainSlug('www.this-is-a-very-long-subdomain.yoursite.com', 16))->toEqual('thisisaverylongs');

    expect(OptionsHelper::getDomainSlug('https://www.this-is-a-very-long-subdomain.yoursite.com', 64))->toEqual('thisisaverylongsubdomain');
});
