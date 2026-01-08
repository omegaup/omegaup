declare var jQuery: any;
declare var $: any;

interface Window {
  jQuery: any;
}

declare module '@fortawesome/fontawesome-svg-core' {
  export const library: { add: (...icons: any[]) => void };
}

declare module 'intro.js' {
  const intro: any;
  export default intro;
}

export {};
