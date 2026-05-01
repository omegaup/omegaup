/**
 * Shared validation patterns and rules
 * These match the backend validators in omegaup/frontend/server/src/Validators.php
 */

/**
 * Alias validation pattern
 * Allows: uppercase (A-Z), lowercase (a-z), digits (0-9), underscore (_), hyphen (-)
 * Matches: /^[a-zA-Z0-9_-]+$/
 */
export const ALIAS_PATTERN = /^[a-zA-Z0-9_-]+$/;

/**
 * Validates an alias string against the ALIAS_PATTERN
 * @param alias The alias to validate
 * @returns true if valid, false otherwise
 */
export function isValidAlias(alias: string): boolean {
  return ALIAS_PATTERN.test(alias);
}
