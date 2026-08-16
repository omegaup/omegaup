/**
 * Pool of test passwords used across Jest tests.
 * Each password is crafted to test a specific validation scenario.
 */

/** Empty password - meets no requirements */
export const PASSWORD_EMPTY = '';

/** Too short - only 3 characters */
export const PASSWORD_TOO_SHORT = 'abc';

/** Has 8+ lowercase chars - meets only length + lowercase */
export const PASSWORD_ONLY_LOWERCASE = 'abcdefgh';

/** Has uppercase - meets uppercase (and lowercase/length) */
export const PASSWORD_WITH_UPPERCASE = 'Password';

/** Has lowercase among uppercase/digits - meets lowercase */
export const PASSWORD_WITH_LOWERCASE = 'PASSWORD1a';

/** Has digit - meets digit (and lowercase/length) */
export const PASSWORD_WITH_DIGIT = 'password1';

/** Has special char - meets special char (and lowercase/length) */
export const PASSWORD_WITH_SPECIAL = 'password!';

/** Meets ALL requirements: length, uppercase, lowercase, digit, special */
export const PASSWORD_VALID = 'Password1!';
