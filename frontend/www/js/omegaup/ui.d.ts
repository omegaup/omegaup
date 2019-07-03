declare namespace omegaup {
  export interface UI {
    buildURLQuery: (queryParameters: { [key: string]: string; }) => string;
    escape: (s: string) => string;
    formatString: (template: string, values: { [key: string]: string; }) => string;
    navigateTo: (url: string) => void;
    userTypeahead: (elem: HTMLElement, cb: (event: HTMLEvent, val: any) => void) => void;
    formatDate: (date: Date) => string;
  };
}

declare let UI: omegaup.UI;
export default UI;
