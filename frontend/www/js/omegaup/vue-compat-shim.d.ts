export {};

declare module 'vue' {
  import { CompatVue } from '@vue/runtime-dom';
  const Vue: CompatVue;
  export default Vue;
  export * from '@vue/runtime-dom';
  const { configureCompat }: CompatVue = Vue;
  export { configureCompat };

  // Allow Vue 2-style render functions in compat mode without explicit typing
  // This makes `render: (createElement) => ...` valid without `: any` annotation
  interface ComponentOptionsBase {
    render?(createElement: any, ...args: any[]): any;
  }
}
