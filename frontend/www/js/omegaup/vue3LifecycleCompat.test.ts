import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';

import './vue3LifecycleCompat';

// Each case builds a throwaway component, so this file intentionally defines
// more than one.
/* eslint-disable vue/one-component-per-file */

describe('vue3LifecycleCompat', () => {
  it('runs an options beforeUnmount hook when the component is destroyed', () => {
    const calls: string[] = [];
    const Example = Vue.extend({
      beforeUnmount() {
        calls.push('beforeUnmount');
      },
      render(h) {
        return h('div');
      },
    });

    shallowMount(Example).destroy();

    expect(calls).toEqual(['beforeUnmount']);
  });

  it('runs an options unmounted hook when the component is destroyed', () => {
    const calls: string[] = [];
    const Example = Vue.extend({
      unmounted() {
        calls.push('unmounted');
      },
      render(h) {
        return h('div');
      },
    });

    shallowMount(Example).destroy();

    expect(calls).toEqual(['unmounted']);
  });

  it('runs beforeDestroy and beforeUnmount once each when both are defined', () => {
    const calls: string[] = [];
    const Example = Vue.extend({
      beforeDestroy() {
        calls.push('beforeDestroy');
      },
      beforeUnmount() {
        calls.push('beforeUnmount');
      },
      render(h) {
        return h('div');
      },
    });

    shallowMount(Example).destroy();

    expect(calls).toEqual(['beforeDestroy', 'beforeUnmount']);
  });
});
