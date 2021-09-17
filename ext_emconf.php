<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Language Mode Switch',
    'description' => 'Allows to switch the language mode in page level',
    'category' => 'misc',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4'
        ]
    ],
    'autoload' => [
        'psr-4' => [
            'ITSC\\LanguageModeSwitch\\' => 'Classes'
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 1,
    'version' => '1.1.0',
];
