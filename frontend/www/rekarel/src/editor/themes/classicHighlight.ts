import { tags } from "@lezer/highlight";
import { HighlightStyle , syntaxHighlighting} from "@codemirror/language";
import { EditorTheme } from "./editorTheme";
import { EditorView } from "codemirror";



export const classicHighlight: EditorTheme = {
  color: "var(--bs-body-color)",
  backgroundColor: "rgba(var(--bs-body-bg-rgb), var(--bs-bg-opacity))",
  gutterBackgroundColor: "var(--bs-secondary-bg)",
  gutterColor: "var(--bs-emphasis-color)",
  extensions: [
    syntaxHighlighting(HighlightStyle.define([
      { tag: tags.atom, color: "rgb(6, 150, 14)" },
      { tag: tags.keyword, color: "rgb(147, 15, 128)" },
      { tag: tags.className, color: "rgb(147, 15, 128)" },
      { tag: tags.brace, color: "rgb(60, 76, 114)" },
      { tag: tags.number, color: "rgb(0, 0, 205)" },
      { tag: tags.operator, color: "rgb(104, 118, 135)" },
      { tag: tags.operator, color: "rgb(104, 118, 135)" },
      { tag: tags.blockComment, color: "rgb(104, 118, 135)", fontStyle: "italic" },
      { tag: tags.comment, color: "rgb(104, 118, 135)", fontStyle: "italic" },
      { tag: tags.function(tags.variableName), color: "rgb(49, 132, 149)" },
      { tag: tags.constant(tags.variableName), color: "#060bbd" },
    ])),
    EditorView.theme({
      '&.cm-focused .cm-selectionBackground, ::selection' : {
        backgroundColor: "#ADD6FFA0"
      },
      '&.cm-focused .cm-selectionMatch': {
        backgroundColor: "#E5EBF1"
      }
    })
  ]
};
