declare namespace omegaup {
  export interface UI {
    buildURLQuery: (queryParameters: { [key: string]: string; }) => string;
    info: (message: string) => void;
    error: (message: string) => void;
    escape: (s: string) => string;
    formatDate: (date: Date) => string;
    formatDateTime: (date: Date) => string;
    formatString: (template: string, values: { [key: string]: string; }) => string;
    groupTypeahead: (elem: HTMLElement, cb: (event: HTMLEvent, val: any) => void) => void;
    isVirtual: (contest: omegaup.Contest) => boolean,
    markdownConverter: (options?: MarkdownConverterOptions) => Converter;
    navigateTo: (url: string) => void;
    problemTypeahead: (elem: HTMLElement, cb: (event: HTMLEvent, val: any) => void) => void;
    schoolTypeahead: (elem: any, cb: (event: HTMLEvent, val: any) => void) => void;
    userTypeahead: (elem: HTMLElement, cb: (event: HTMLEvent, val: any) => void) => void;
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
