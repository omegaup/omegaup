import { transpileCode } from "@rekarel/core";
import { KarelConsole } from "../desktop/console";
import { SetText } from "./editor";
import { getEditors } from "./editorsInstances";


export function editorTranspile(target:"java"|"pascal") {
    const editor = getEditors()[0]

    const source = editor.state.doc.toString();
    try {
        const response=transpileCode(source, target)
        SetText(editor,`${response}`);
    } catch (e) {
        KarelConsole.GetInstance().SendMessageToConsole("Para cambiar el lenguaje, el código debe compilar", "danger")
    }

}
