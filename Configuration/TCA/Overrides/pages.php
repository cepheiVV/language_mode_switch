<?php

defined('TYPO3_MODE') or die();

$ll = 'LLL:EXT:language_mode_switch/Resources/Private/Language/locallang_db.xlf:';

$extensionConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
    ->get('language_mode_switch');

$defaultLabel = $ll . 'pages.l10n_mode.default';
if ($extensionConfiguration['automaticMode']) {
    $defaultLabel = $ll . 'pages.l10n_mode.automatic';
}

/**
 * Add extra fields to the pages record
 */
$additionalPagesColumns = [
    'l10n_mode' => [
        'exclude' => true,
        'label' => $ll . 'pages.l10n_mode',
        'description' => $ll . 'pages.l10n_mode.description',
        'displayCond' => 'FIELD:l10n_parent:!=:0',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'items' => [
                [$defaultLabel, ''],
                [$ll . 'pages.l10n_mode.strict', 'strict'],
                [$ll . 'pages.l10n_mode.fallback', 'fallback'],
                [$ll . 'pages.l10n_mode.free', 'free'],
            ],
        ],
    ],
];

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    $additionalPagesColumns
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'pages',
    'l10n_mode',
    '',
    'after:l18n_cfg'
);
