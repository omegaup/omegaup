import {HighlightStyle} from "@codemirror/language"
import { EditorView } from "codemirror"
import { SetEditorTheme } from "../editor";
import {Extension} from "@codemirror/state"
import { getEditors } from "../editorsInstances";
type combo = Extension



export type EditorTheme = {
    extensions:combo,
    color:string,
    backgroundColor:string,
    gutterBackgroundColor:string,
    gutterColor:string,
} 




export function applyTheme(theme:EditorTheme) {
    SetEditorTheme(theme.extensions, getEditors()[0]);
    const root = $(":root")[0];
    root.style.setProperty("--editor-color", theme.color);
    root.style.setProperty("--editor-background", theme.backgroundColor);
    root.style.setProperty("--editor-gutter-bg", theme.gutterBackgroundColor);
    root.style.setProperty("--editor-gutter", theme.gutterColor);
}