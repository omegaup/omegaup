// This file is loaded by Jest's setupFiles (not setupFilesAfterEnv), which
// runs BEFORE the test framework is installed and before any test files are
// loaded. This ensures that console.warn is patched before Vue caches it.
const originalConsoleWarn = console.warn;
console.warn = function (...args: any[]) {
  const msg = String(args[0] || '');
  if (
    msg.includes('Failed to resolve component') ||
    msg.includes('provide() can only be used inside setup()')
  ) {
    return;
  }
  originalConsoleWarn(...args);
};
