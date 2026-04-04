declare module '@/third_party/js/pagedown/Markdown.Converter.js' {
  interface Hooks {
    chain: (eventName: string, callback: any) => void;
  }

  export class Converter {
    hooks: Hooks;

    makeHtml: (markdown: string) => string;
  }
}
