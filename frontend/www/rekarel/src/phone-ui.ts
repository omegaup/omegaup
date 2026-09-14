import { EditorView } from "@codemirror/view"
import { undo, redo } from "@codemirror/commands"


type Toolbar = Record<string, () => string>

type CodeTab = {
    toolbar: Toolbar,
    simpleCodeInputs: Toolbar,
    indent: string,
    unindent: string,
    undo: string,
    redo: string,

};
interface UIElements {
    navToolbar: Toolbar,
    codeTab: CodeTab
    editor: EditorView,
    mainEdtior: EditorView,
}
function activateButton(toolbar: Toolbar, buttonPressed: string) {
    for (const element in toolbar) {
        $(element).removeClass("text-primary");
    }
    $(buttonPressed).addClass("text-primary");
    toolbar[buttonPressed]();
}

function indent(elements: UIElements) {
    let editor = elements.editor;
    let state = editor.state;
    let head = state.selection.main.head
    let transaction = state.update({
        changes: {
            from: state.doc.lineAt(head).from,
            insert: "\t"
        }
    })
    editor.dispatch(transaction);

}
function unindent(elements: UIElements) {
    let state = elements.editor.state
    let head = state.selection.main.head
    let line = state.doc.lineAt(head)

    let stringOutpt = line.text.replace(/^((\t)|( {0,4}))/, "")
    let transaction = state.update({
        changes: {
            from: line.from,
            to: line.to,
            insert: stringOutpt
        }
    })
    elements.editor.dispatch(transaction);
}

function replaceWithIndetation(elements: UIElements, txt: string) {
    let state = elements.editor.state;
    let head = state.selection.main.head;
    let line = state.doc.lineAt(head);
    let indentation = line.text.match(/\s*/g)[0];
    let text = indentation + txt;
    let transaction = state.update({
        changes: {
            from: line.from,
            to: line.to,
            insert: text
        }
    })
    elements.editor.dispatch(transaction);
}


//TODO: Add support for states
function GetPhoneUIHelper(elements: UIElements) {
    let response = {
        changeCodeToolbar: (button: string) =>
            activateButton(elements.codeTab.toolbar, button),
        changeNavToolbar: (button: string) =>
            activateButton(elements.navToolbar, button),
        indent: () => indent(elements),
        unindent: () => unindent(elements),
        btnSimpleCodeInput: (btn: string) =>
            replaceWithIndetation(elements, elements.codeTab.simpleCodeInputs[btn]()),
        replaceWithIndetation: (txt: string) =>
            replaceWithIndetation(elements, txt),


    };

    for (const btn in elements.navToolbar) {
        $(btn).click(() => response.changeNavToolbar(btn));
    }
    for (const btn in elements.codeTab.toolbar) {
        $(btn).click(() => response.changeCodeToolbar(btn));
    }
    for (const btn in elements.codeTab.simpleCodeInputs) {
        $(btn).click(() => response.btnSimpleCodeInput(btn));
    }

    $(elements.codeTab.indent).click(response.indent)
    $(elements.codeTab.unindent).click(response.unindent)

    $(elements.codeTab.undo).on(
        "click", 
        ()=> { undo(elements.mainEdtior);}
    );
    $(elements.codeTab.redo).on(
        "click", 
        ()=> { redo(elements.mainEdtior);}
    );
    return response
}

export { GetPhoneUIHelper };