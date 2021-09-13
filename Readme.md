
# TYPO3 Language Mode Switch
Teaches TYPO3 to change the language mode based on configured value on page level.

TYPO3 does no longer allow to change the language mode via TypoScript.
This is set once for the whole website in the site configuration.

We extend translated pages with a new field to switch the mode for that specific page.
This middleware will fetch that info and modify current language configuration accordingly.
  