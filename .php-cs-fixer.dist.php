<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests');

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'strict_types' => true,
        'declare_strict_types' => true,
        'native_function_invocation' => ['include' => ['@all']],
        'no_unused_imports' => true,
        'ordered_imports' => true,
        'phpdoc_to_comment' => false,
        'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
