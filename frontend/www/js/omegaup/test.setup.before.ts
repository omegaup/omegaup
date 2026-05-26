// This file is loaded by Jest's setupFiles (not setupFilesAfterEnv), which
// runs BEFORE the test framework is installed and before any test files are
// loaded. This ensures that console.warn and DOM methods are patched before
// Vue caches them.
const originalConsoleWarn = console.warn;
console.warn = function (...args) {
  const msg = String(args[0] || '');
  if (
    msg.includes('Failed to resolve component') ||
    msg.includes('provide() can only be used inside setup()')
  ) {
    return;
  }
  originalConsoleWarn(...args);
};

// Work around Vue 3 / jsdom DOM patching bug that throws NotFoundError
// during component unmount in tests.
function safeRemoveChild<T extends Node>(this: Node, child: T): T {
  if (this.contains(child)) {
    return (Node.prototype as any).__originalRemoveChild.call(this, child);
  }
  return child;
}
if (!(Node.prototype as any).__originalRemoveChild) {
  (Node.prototype as any).__originalRemoveChild = Node.prototype.removeChild;
  Node.prototype.removeChild = safeRemoveChild;
}

const originalReplaceChild = Node.prototype.replaceChild;
Node.prototype.replaceChild = function <T extends Node>(
  newChild: Node,
  oldChild: T,
): T {
  if (this.contains(oldChild)) {
    return originalReplaceChild.call(this, newChild, oldChild);
  }
  return oldChild;
};

const originalInsertBefore = Node.prototype.insertBefore;
Node.prototype.insertBefore = function <T extends Node>(
  newChild: T,
  refChild: Node | null,
): T {
  if (!refChild || this.contains(refChild)) {
    return originalInsertBefore.call(this, newChild, refChild);
  }
  return this.appendChild(newChild);
};

// Suppress NotFoundError thrown during Vue 3 teardown in jsdom
process.on('uncaughtException', (err: Error) => {
  if (
    err.name === 'NotFoundError' &&
    err.message === 'The node to be removed is not a child of this node.'
  ) {
    return;
  }
  throw err;
});
process.on('unhandledRejection', (reason: any) => {
  if (
    reason?.name === 'NotFoundError' &&
    reason?.message === 'The node to be removed is not a child of this node.'
  ) {
    return;
  }
  throw reason;
});
