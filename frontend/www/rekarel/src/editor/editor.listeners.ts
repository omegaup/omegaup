import { EditorView } from "codemirror";
import { KarelController } from "../KarelController";
import { getEditors } from "./editorsInstances";
import { HighlightKarelInstruction } from "./editor.highlightLine";



export function RegisterHighlightListeners() {
    const editor = getEditors()[0];
    const controller = KarelController.GetInstance();
    controller.RegisterStepController((_, state)=> {
        const line = controller.GetRuntime().state.line + 1;
        const column = controller.GetRuntime().state.column;
        const codeLine = editor.state.doc.line(line)
        HighlightKarelInstruction(editor, line, column);
        editor.dispatch({     
            effects:EditorView.scrollIntoView(codeLine.from)
        });
    })
    controller.RegisterResetObserver((_)=> {
        HighlightKarelInstruction(editor, -1, -1);
    })
  }