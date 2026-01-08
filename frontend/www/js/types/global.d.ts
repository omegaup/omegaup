declare global {
  var jQuery: any;
  var $: any;
  interface Window {
    jQuery: any;
    $: any;
  }
}

export {};
