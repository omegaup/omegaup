import { tags } from "@lezer/highlight"
import { HighlightStyle, syntaxHighlighting } from "@codemirror/language"
import { EditorTheme } from "./editorTheme"
import { EditorView } from "codemirror";

export const SepiaTheme: EditorTheme = {
    color: "#3a3d42",
    backgroundColor: "#fffde5",
    gutterBackgroundColor: "#d9ceb6",
    gutterColor: "#2d2e3b",
    extensions: [
      
      syntaxHighlighting(HighlightStyle.define([
        { tag: tags.atom, color: "#707894" },
        { tag: tags.keyword, color: "#cb3551" },
        { tag: tags.controlKeyword, color: "#3f9c4f" },
        { tag: tags.definitionKeyword, color: "#3f9c4f" },
        { tag: tags.number, color: "#22867e" },
        { tag: tags.operator, color: "#3f9c4f" },
        { tag: tags.blockComment, color: "#973d1a", fontStyle: "italic" },
        { tag: tags.comment, color: "#973d1a", fontStyle: "italic" },
        { tag: tags.function(tags.variableName), color: "#1f34a1" },
        { tag: tags.constant(tags.variableName), color: "#a1789a" },
      ])),
      EditorView.theme({
        '&.cm-focused .cm-selectionBackground, ::selection' : {
          backgroundColor: "#53d49180"
        },
        '&.cm-focused .cm-selectionMatch': {
          backgroundColor: "#b3c6c750"
        }
        
      })
    ]
  };



  