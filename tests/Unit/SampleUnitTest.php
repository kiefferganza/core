<?php

test('basic math operations work', function () {
    expect(2 + 2)->toBe(4);
    expect(10 - 5)->toBe(5);
    expect(3 * 3)->toBe(9);
});
