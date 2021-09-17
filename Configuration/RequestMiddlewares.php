<?php

return [
    'frontend' => [
        'itsc/language-mode-switch' => [
            'target' => \ITSC\LanguageModeSwitch\Middleware\Frontend\LanguageModeSwitch::class,
            'after' => [
                'typo3/cms-frontend/page-argument-validator',
            ],
            'before' => [
                'typo3/cms-frontend/tsfe',
            ],
        ],
    ],
];
