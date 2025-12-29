<?php

$finder = new PhpCsFixer\Finder()
    ->in('src')
    ->in('tests');

return new PhpCsFixer\Config()
    ->setUnsupportedPhpVersionAllowed(true)
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,

        // Automatically adds trailing commas in multiline
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
            ],
        ],

        // This rule leads to @psalm-suppress comments being ignored
        'phpdoc_to_comment' => false,

        // We want to use standard snake case for tests
        'php_unit_method_casing' => [
            'case' => 'snake_case',
        ],

        // Disable one line throw
        'single_line_throw' => false,

        // Yoda style is more difficult to read and the issues it would prevent are already prevented by Psalm
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
            'always_move_variable' => true,
        ],

        // Add spaces around union and intersection types
        'types_spaces' => [
            'space' => 'single',
        ],

        // Nullable types should be explicit even with default values
        'nullable_type_declaration_for_default_null_value' => false,
    ])
    ->setFinder($finder);
