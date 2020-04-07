declare module '@/third_party/js/pagedown/Markdown.Converter.js' {
  export type ImageMapping = {
    [url: string]: string;
  };

  type ProblemSettings = any;

  interface Hooks {
    chain: (eventName: string, callback: any) => void;
  }

  export class Converter {
    hooks: Hooks;
    _settings?: ProblemSettings;
    _imageMapping?: ImageMapping;

    makeHtml: (markdown: string) => string;
    makeHtmlWithImages: (
      markdown: string,
      imageMapping: ImageMapping,
      settings: ProblemSettings,
    ) => string;
  }
}
