<?php

declare(strict_types=1);

test('readme exists', function () {
    expect(file_exists(base_path('README.md')))->toBeTrue();
});

test('readme includes local setup commands', function () {
    $readme = file_get_contents(base_path('README.md')) ?: '';

    expect($readme)->toContain('composer install')
        ->and($readme)->toContain('composer setup')
        ->and($readme)->toContain('composer dev');
});
