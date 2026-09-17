import { tags } from "@lezer/highlight"
import { HighlightStyle, syntaxHighlighting } from "@codemirror/language"
import { EditorTheme } from "./editorTheme"
import { EditorView } from "codemirror";


export const darkClassicHighlight: EditorTheme = {
  color: "var(--bs-body-color)",
  backgroundColor: "rgba(var(--bs-body-bg-rgb), var(--bs-bg-opacity))",
  gutterBackgroundColor: "var(--bs-secondary-bg)",
  gutterColor: "var(--bs-emphasis-color)",
  extensions: [
    
    syntaxHighlighting(HighlightStyle.define([
      { tag: tags.atom, color: "#93bf74" },
      { tag: tags.keyword, color: "#C586C0" },
      { tag: tags.className, color: "#C586C0" },
      { tag: tags.brace, color: "#94a4cb" },
      { tag: tags.number, color: "#569CD6" },
      { tag: tags.operator, color: "#77a1d5" },
      { tag: tags.blockComment, color: "#a0b6b6", fontStyle: "italic" },
      { tag: tags.comment, color: "#a0b6b6", fontStyle: "italic" },
      { tag: tags.function(tags.variableName), color: "#9CDCFE" },
      { tag: tags.constant(tags.variableName), color: "#80e9ed" },
    ])),
    EditorView.theme({
      ".cm-selectionBackground": {
        backgroundColor: "#264F7880"
      },
      '.cm-selectionMatch': {
        backgroundColor: "#343A4080"
      },
      '&.cm-focused .cm-selectionBackground, ::selection' : {
        backgroundColor: "#264F78"
      },
      '&.cm-focused .cm-selectionMatch': {
        backgroundColor: "#343A40"
      },
      "&.cm-focused .cm-cursor": {
        borderLeftColor: "var(--bs-body-color)"
      },
    })
  ]
};
