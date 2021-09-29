
# TYPO3 Language Mode Switch
Teaches TYPO3 to change the language mode based on configured value on page level.

TYPO3 does no longer allow to change the language mode via TypoScript.
This is set once for the whole website in the site configuration.

We extend translated pages with a new field to switch the mode for that specific page.
This middleware will fetch that info and modify current language configuration accordingly.
  
  
## Automatic mode
Automatic mode can be enabled in the extension configuration. It is off by default.
If automatic mode is enabled the frontend will behave according to what the page module displays for each language.
When it says "Connected Mode", then "fallback" will be used in the frontend.
When it says "Free Mode", then "free" will be used in the frontend.

With automatic mode enabled, you can still override the behaviour in the translated page properties.
