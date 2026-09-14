import { EditorView } from "codemirror";

export function testSkipFlag(editor:EditorView, line:number) {
    const text = editor.state.doc.line(line).text
    const pascal = /\@saltatela/g
    const java = /\@autoSkip/g

    return pascal.test(text) || java.test(text)
}