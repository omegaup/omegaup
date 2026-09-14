import { tags } from "@lezer/highlight"
import { HighlightStyle, syntaxHighlighting } from "@codemirror/language"
import { EditorTheme } from "./editorTheme"
import { EditorView } from "codemirror";


export const OMIHighlight: EditorTheme = {
    color: "#FFFF00",
    backgroundColor: "#000080",
    gutterBackgroundColor: "#F5F5F5",
    gutterColor: "#000000",
    extensions: [
      
      syntaxHighlighting(HighlightStyle.define([
        { tag: tags.atom, color: "#00FFFF" },
        { tag: tags.keyword, color: "#00FFFF" },
        { tag: tags.controlKeyword, color: "#00FFFF" },
        { tag: tags.definitionKeyword, color: "#00FFFF" },
        { tag: tags.number, color: "#FF00FF" },
        { tag: tags.operator, color: "#00FFFF" },
        { tag: tags.brace, color: "#00FFFF" },
        { tag: tags.function(tags.variableName), color: "#00FFFF" },
        { tag: tags.constant(tags.variableName), color: "#00FFFF" },
      ])),
      EditorView.theme({
        '&.cm-focused .cm-selectionBackground, ::selection' : {
          backgroundColor: "#0000ff"
        },
        '&.cm-focused .cm-selectionMatch': {
          backgroundColor: "#6b248080"
        },
        "&.cm-focused .cm-cursor": {
          borderLeftColor: "#FFFF00"
        },
      })
    ]
  };

