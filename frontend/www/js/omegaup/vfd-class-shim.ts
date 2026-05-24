/**
 * Webpack alias shim for vue-facing-decorator.
 *
 * vue-facing-decorator exports an empty `Base` class as `Vue`.
 * OmegaUp's codebase calls `Vue.use()` on that import in many components.
 * This shim replaces the internal class module so that `Vue` from
 * `vue-facing-decorator` resolves to the real `@vue/compat` constructor.
 */
import Vue from 'vue';
export const Base = Vue;
