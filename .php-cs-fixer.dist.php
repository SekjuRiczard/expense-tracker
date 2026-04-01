<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/api/src') // Linter ma zaglądać tutaj
    ->append([__FILE__]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true, // Podstawa to standard Symfony
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,   // TO ROZWIĄŻE TWÓJ PROBLEM: wymusi 'use Exception;'
            'import_constants' => true,
            'import_functions' => true,
        ],
        'yoda_style' => false, // Opcjonalnie: wyłącza styl (null === $var) jeśli go nie lubisz
        'concat_space' => ['spacing' => 'one'],
    ])
    ->setFinder($finder);