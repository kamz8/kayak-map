<?php

it('keeps repository readme files in english', function () {
    $readmeFiles = collect([
        '.devcontainer/README.md',
        'README.md',
        'devops/README.md',
        'tests/README.md',
        'tests/vitest/README.md',
        'resources/js/dashboard/README.md',
        'resources/js/dashboard/components/README.md',
        'resources/js/dashboard/components/ui/README.md',
        'resources/js/dashboard/modules/README.md',
        'resources/js/dashboard/modules/users/README.md',
    ]);

    $polishSignals = [
        'ą', 'ć', 'ę', 'ł', 'ń', 'ó', 'ś', 'ź', 'ż',
        'Ą', 'Ć', 'Ę', 'Ł', 'Ń', 'Ó', 'Ś', 'Ź', 'Ż',
        'Przegląd', 'Instalacja', 'Użytkowanie', 'Konfiguracja', 'Wymagania', 'Testy jednostkowe', 'Komponenty', 'Moduły',
    ];

    $violations = $readmeFiles
        ->filter(fn (string $path): bool => file_exists(__DIR__.'/../../'.$path))
        ->flatMap(function (string $path) use ($polishSignals): array {
            $content = file_get_contents(__DIR__.'/../../'.$path);

            return collect($polishSignals)
                ->filter(fn (string $signal): bool => str_contains($content, $signal))
                ->map(fn (string $signal): string => $path.' contains '.$signal)
                ->all();
        })
        ->values();

    expect($violations->all())->toBe([]);
});
