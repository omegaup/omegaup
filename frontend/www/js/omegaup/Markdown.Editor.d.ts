declare module '@/third_party/js/pagedown/Markdown.Editor.js' {
  import * as Markdown from '@/third_party/js/pagedown/Markdown.Converter.js';

  type HookCallback = (text?: string, ...args: any[]) => string | void;

  interface Hooks {
    set: (eventName: string, callback: HookCallback) => void;
    chain: (eventName: string, callback: HookCallback) => void;
  }

  interface EditorOptions {
    strings?: {
      bold?: string;
      boldexample?: string;
      cancel?: string;
      code?: string;
      codeexample?: string;
      heading?: string;
      headingexample?: string;
      help?: string;
      hr?: string;
      image?: string;
      imagedescription?: string;
      imagedialog?: string;
      italic?: string;
      italicexample?: string;
      link?: string;
      linkdescription?: string;
      linkdialog?: string;
      litem?: string;
      ok?: string;
      olist?: string;
      quote?: string;
      quoteexample?: string;
      redo?: string;
      redomac?: string;
      ulist?: string;
      undo?: string;
    };
    wrapImageInLink?: boolean;
    convertImagesToLinks?: boolean;
    panels?: {
      buttonBar: HTMLElement;
      preview: HTMLElement | null;
      input: HTMLElement;
    };
  }

  export class Editor {
    hooks: Hooks;

    constructor(
      markdownConverter: Markdown.Converter,
      idPostfix?: string,
      options?: EditorOptions,
    );

    getConverter: () => Markdown.Converter;
    run: () => void;
    refreshPreview(): () => void;
  }
}
