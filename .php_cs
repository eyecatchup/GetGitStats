<?php

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    // use default SYMFONY_LEVEL and change the set with fixers:
    ->fixers([
        '-concat_without_spaces',
        '-phpdoc_no_package',
        '-unalign_double_arrow',
        '-unalign_equals',
        'concat_with_spaces',
        'php_unit_construct',
        'php_unit_strict',
        'strict',
        'strict_param',
		'-yoda_conditions',
		'multiline_spaces_before_semicolon',
		'ordered_use',
		'short_array_syntax'
    ])
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in(__DIR__ . '/tests/MyProject')
            ->in(__DIR__ . '/specs')
            ->in(__DIR__ . '/src')
    )
;
