import Vue, { ComponentOptions } from 'vue';
import { Component } from 'vue-property-decorator';

/**
 * Vue 2.7 still invokes `beforeDestroy` and `destroyed`, and vue-class-component
 * only copies those names from class components onto the options object. Vue 3
 * renamed them to `beforeUnmount` and `unmounted`. Register the Vue 3 names and
 * alias them onto the Vue 2 hooks so the new names run on the current runtime.
 */
declare module 'vue/types/options' {
  // V matches Vue's generic ComponentOptions so the hooks merge correctly.
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  interface ComponentOptions<V extends Vue> {
    beforeUnmount?(): void;
    unmounted?(): void;
  }
}

Component.registerHooks(['beforeUnmount', 'unmounted']);

const { optionMergeStrategies } = Vue.config;
optionMergeStrategies.beforeUnmount = optionMergeStrategies.beforeDestroy;
optionMergeStrategies.unmounted = optionMergeStrategies.destroyed;

type Vue2HookName = 'beforeDestroy' | 'destroyed';
type Vue3HookName = 'beforeUnmount' | 'unmounted';
type HookHandler = (this: Vue) => void;

function hookFns(hook: unknown): HookHandler[] {
  if (Array.isArray(hook)) {
    return (hook as HookHandler[]).slice();
  }
  if (typeof hook === 'function') {
    return [hook as HookHandler];
  }
  return [];
}

function aliasHook(
  options: ComponentOptions<Vue>,
  vue3Name: Vue3HookName,
  vue2Name: Vue2HookName,
): void {
  // Vue merges lifecycle hooks into arrays at runtime. The public option
  // types still describe a single method, so write through a record.
  const record = (options as unknown) as Record<string, unknown>;
  const vue3Hooks = hookFns(record[vue3Name]);
  if (vue3Hooks.length === 0) {
    return;
  }
  const merged = hookFns(record[vue2Name]);
  for (const fn of vue3Hooks) {
    if (merged.indexOf(fn) === -1) {
      merged.push(fn);
    }
  }
  record[vue2Name] = merged;
}

Vue.mixin({
  beforeCreate(this: Vue): void {
    aliasHook(this.$options, 'beforeUnmount', 'beforeDestroy');
    aliasHook(this.$options, 'unmounted', 'destroyed');
  },
});
