<?php

namespace ITSC\LanguageModeSwitch\Middleware\Frontend;

/*
 * Copyright (C) 2021 Daniel Siepmann <coding@daniel-siepmann.de>
 * Copyright (C) 2021 Patrick Crausaz <info@its-crausaz.ch>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301, USA.
 */

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

/**
 * Teaches TYPO3 to change the language mode based on configured value from actual page.
 *
 * TYPO3 does no longer allow to change language mode via TypoScript.
 * This is set once via site configuration.
 *
 * We extend translated pages with a new field to switch the mode for that specific page.
 * This middleware will fetch that info and modify current language configuration accordingly.
 */
class LanguageModeSwitch implements MiddlewareInterface
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * LanguageModeSwitch constructor.
     * @param FrontendInterface $cache
     * @param QueryBuilder $queryBuilder
     */
    public function __construct(FrontendInterface $cache, QueryBuilder $queryBuilder)
    {
        $this->cache = $cache;
        $this->queryBuilder = $queryBuilder;
        $this->queryBuilder->from('pages');
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $pageArguments = $request->getAttribute('routing', null);
        $language = $request->getAttribute('language', null);
        $mode = $this->getPageDefinedCustomMode($pageArguments, $language);
        if ($mode !== '') {
            $newLanguage = $this->getNewLanguageWithMode($language, $mode);
            $request = $request->withAttribute('language', $newLanguage);
        }
        return $handler->handle($request);
    }

    private function getPageDefinedCustomMode(
        ?PageArguments $pageArguments,
        ?SiteLanguage $siteLanguage
    ): string {
        if ($this->missesRequirements($pageArguments, $siteLanguage)) {
            return '';
        }

        $cacheKey = 'customTranslationMode_' . $pageArguments->getPageId() . '_' . $siteLanguage->getLanguageId();
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        if ($this->pageHasStandAloneContent($pageArguments->getPageId(), $siteLanguage->getLanguageId())) {
            return 'free';
        }

        $queryBuilder = clone $this->queryBuilder;
        $queryBuilder->select('l10n_mode');
        $queryBuilder->where(
            $queryBuilder->expr()->eq(
                'l10n_parent',
                $queryBuilder->createNamedParameter($pageArguments->getPageId())
            ),
            $queryBuilder->expr()->eq(
                'sys_language_uid',
                $queryBuilder->createNamedParameter($siteLanguage->getLanguageId())
            )
        );
        $mode = $queryBuilder->execute()->fetchOne();
        $this->cache->set($cacheKey, $mode, ['pageId_' . $pageArguments->getPageId()]);
        return $mode;
    }

    private function missesRequirements(
        ?PageArguments $pageArguments,
        ?SiteLanguage $siteLanguage
    ): bool {
        return $pageArguments === null
            || $siteLanguage === null
            || $siteLanguage->getLanguageId() <= 0
            ;
    }

    private function getNewLanguageWithMode(
        SiteLanguage $language,
        string $mode
    ): SiteLanguage {
        return new SiteLanguage(
            $language->getLanguageId(),
            $language->getLocale(),
            $language->getBase(),
            array_merge($language->toArray(), [
                'fallbackType' => $mode,
            ])
        );
    }
}
