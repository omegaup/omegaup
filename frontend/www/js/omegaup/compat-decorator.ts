/**
 * Compatibility shim: vue-property-decorator -> vue-facing-decorator
 *
 * This module re-exports vue-facing-decorator decorators under the
 * vue-property-decorator namespace. By adding a webpack / jest alias:
 *
 *   'vue-property-decorator' -> '<rootDir>/frontend/www/js/omegaup/compat-decorator.ts'
 *
 * zero component files need their import lines changed.
 *
 * Only Inject is used in a handful of files and is shimmed here.
 */

export { default as Vue } from 'vue';
export {
  Component,
  Prop,
  Watch,
  Emit,
  Ref,
} from 'vue-facing-decorator';

/**
 * Minimal @Inject shim for the few components that still use it.
 * vue-facing-decorator does not provide Inject, so we implement it
 * as a simple property decorator that stores the injection key on
 * the component options.
 */
export function Inject(key?: string | symbol) {
  return function (target: any, propertyKey: string) {
    const ctor = target.constructor;
    const opts = (ctor.options ??= {} as any);
    opts.inject ??= {};
    (opts.inject as any)[propertyKey] = key || propertyKey;
  };
}
