import { EditorView } from "codemirror";
import { createEditors } from "./editor";

let editors = createEditors();

export function getEditors() {
    return editors;
}

