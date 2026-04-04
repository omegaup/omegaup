// A module that has test-mockable functions that interact with the global
// window.location.

export function getLocationHash(): string {
  return window.location.hash;
}

export function setLocationHash(hash: string): void {
  window.location.hash = hash;
}
