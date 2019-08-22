declare namespace omegaup {
  export interface UI {
    buildURLQuery: (queryParameters: { [key: string]: string; }) => string;
    info: (message: string) => void;
    error: (message: string) => void;
    escape: (s: string) => string;
    formatDate: (date: Date) => string;
    formatString: (template: string, values: { [key: string]: string; }) => string;
    markdownConverter: (options?: MarkdownConverterOptions) => Converter;
    navigateTo: (url: string) => void;
    userTypeahead: (elem: HTMLElement, cb: (event: HTMLEvent, val: any) => void) => void;
    schoolTypeahead: (elem: any, cb: (event: HTMLEvent, val: any) => void) => void;
  };

  interface MarkdownConverterOptions {
    preview: boolean;
  }

  interface Converter {
    makeHtml: (text: string) => string;
  }
}

declare let UI: omegaup.UI;
export default UI;
