import { tags } from "@lezer/highlight"
import { HighlightStyle, syntaxHighlighting } from "@codemirror/language"
import { EditorTheme } from "./editorTheme"
import { EditorView } from "codemirror";


export const ReKarelHighlight: EditorTheme = {
    color: "#fafafa",
    backgroundColor: "rgb(var(--bs-body-bg-rgb))",
    gutterBackgroundColor: "var(--bs-secondary-bg)",
    gutterColor: "var(--bs-emphasis-color)",
    extensions: [
      
      syntaxHighlighting(HighlightStyle.define([
        { tag: tags.atom, color: "#ffda6a" },
        { tag: tags.keyword, color: "#ea868f" },
        { tag: tags.brace, color: "#ea868f" },
        { tag: tags.controlKeyword, color: "#6ea8fe" },
        { tag: tags.definitionKeyword, color: "#6ea8fe" },
        { tag: tags.number, color: "#ffda6a" },
        { tag: tags.operator, color: "#77a1d5" },
        { tag: tags.blockComment, color: "#75b798", fontStyle: "italic" },
        { tag: tags.comment, color: "#75b798", fontStyle: "italic" },
        { tag: tags.function(tags.variableName), color: "#6edff6" },
        { tag: tags.constant(tags.variableName), color: "#ffda6a" },
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



  
export const LightReKarelHighlight: EditorTheme = {
    color: "#2e2e2e",
    backgroundColor: "rgb(var(--bs-body-bg-rgb))",
    gutterBackgroundColor: "var(--bs-secondary-bg)",
    gutterColor: "var(--bs-emphasis-color)",
    extensions: [
      
      syntaxHighlighting(HighlightStyle.define([
        { tag: tags.atom, color: "#ff9c07" },
        { tag: tags.keyword, color: "#c62e3d" },
        { tag: tags.brace, color: "#c62e3d" },
        { tag: tags.controlKeyword, color: "#0d6efd " },
        { tag: tags.definitionKeyword, color: "#0d6efd " },
        { tag: tags.number, color: "#ff9c07" },
        { tag: tags.operator, color: "#77a1d5" },
        { tag: tags.blockComment, color: "#198754", fontStyle: "italic" },
        { tag: tags.comment, color: "#198754", fontStyle: "italic" },
        { tag: tags.function(tags.variableName), color: "#24a7db" },
        { tag: tags.constant(tags.variableName), color: "#ff9c07" },
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
