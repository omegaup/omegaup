declare module 'v-tooltip' {
  interface VTooltip {
    bind: (el: any, _ref?: any) => void;
    unbind: (el: any) => void;
    update: (el: any, _ref?: any) => void;
  }
  export const VTooltip: VTooltip;
}