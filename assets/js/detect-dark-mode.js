(function () {
  'use strict';

  var root = document.documentElement;
  var storageKey = 'livelatch-theme';
  var legacyStorageKey = 'color-mode';
  var theme = 'light';

  function storedValue(key) {
    try {
      return window.localStorage ? window.localStorage.getItem(key) : null;
    } catch (error) {
      return null;
    }
  }

  function storeValue(key, value) {
    try {
      if (window.localStorage) {
        window.localStorage.setItem(key, value);
      }
    } catch (error) {
      // Storage can be unavailable in private or restricted browser modes.
    }
  }

  var storedTheme = storedValue(storageKey);
  var legacyTheme = storedValue(legacyStorageKey);

  if (storedTheme === 'light' || storedTheme === 'dark') {
    theme = storedTheme;
  } else if (legacyTheme === 'light' || legacyTheme === 'dark') {
    theme = legacyTheme;
  }

  root.setAttribute('data-ll-theme', theme);
  root.style.colorScheme = theme;
  storeValue(storageKey, theme);
  storeValue(legacyStorageKey, theme);
}());
