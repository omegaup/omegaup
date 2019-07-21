declare namespace omegaup {
  export interface UI {
    buildURLQuery: (queryParameters: { [key: string]: string; }) => string;
    error: (message: string) => void;
    escape: (s: string) => string;
    formatDate: (date: Date) => string;
    formatString: (template: string, values: { [key: string]: string; }) => string;
    markdownConverter: (options?: MarkdownConverterOptions) => converter;
    navigateTo: (url: string) => void;
    userTypeahead: (elem: HTMLElement, cb: (event: HTMLEvent, val: any) => void) => void;
  };

  interface MarkdownConverterOptions {
    preview: boolean;
  }

  interface converter {
    makeHtml: (text: string) => string;
  }
}

declare let UI: omegaup.UI;
export default UI;
