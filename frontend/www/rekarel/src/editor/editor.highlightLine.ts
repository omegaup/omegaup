import {EditorView, Decoration, DecorationSet, ViewPlugin,  ViewUpdate, WidgetType} from "@codemirror/view"
import {Compartment, Extension, Facet, RangeSetBuilder } from "@codemirror/state"
import { splitPanels } from "../split"

let highlightedLine = new Compartment

const karelLineTheme = EditorView.baseTheme({
    ".cm-karelLine.cm-activeLine": {backgroundColor: "rgba(255, 255, 0, 0.5)"}, //TODO: Edit this based on the theme
    ".cm-karelLine": {backgroundColor: "#22872a44"}, //TODO: Edit this based on the theme
  
  })
  
  const karelLineFacet = Facet.define<number, number>({
    combine: values => values.length ? Math.min(...values) : -1
  })
  
  
  
  const lineHighlight = Decoration.line({
    attributes: {class: "cm-karelLine"}
  })
  
  function lineHighlightDeco(view: EditorView) {
    let pos = view.state.facet(karelLineFacet)
    let builder = new RangeSetBuilder<Decoration>()
    if (pos!==-1) {
        let line = view.state.doc.line(pos)
        builder.add(line.from, line.from, lineHighlight)
      }
    return builder.finish()
  }
  
  const lineHighlighting = ViewPlugin.fromClass(class {
    decorations: DecorationSet
  
    constructor(view: EditorView) {
      this.decorations = lineHighlightDeco(view)
    }
  
    update(update: ViewUpdate) {
      if (update.startState.facet(karelLineFacet) != update.state.facet(karelLineFacet))
        this.decorations = lineHighlightDeco(update.view)
    }
  }, {
    decorations: v => v.decorations
  })
  



  
let executionCursor = new Compartment


const karelColumnFacet = Facet.define<number, number>({
    combine: values => values.length ? Math.min(...values) : -1
})


class CursorWidget extends WidgetType {
    constructor() { super() }

    eq(other: CursorWidget) { return true; }

    toDOM() {
        let wrap = document.createElement("small");
        wrap.setAttribute("aria-hidden", "true");
        wrap.className = "cm-execution-cursor";
        let icon = wrap.appendChild(document.createElement("i"));
        icon.classList.add("bi");
        icon.classList.add("bi-caret-right-fill");
        return wrap;
    }

    ignoreEvent() { return true; }
}

function cursor(view: EditorView) {
    let line = view.state.facet(karelLineFacet)
    let column = view.state.facet(karelColumnFacet)
    if (column === -1 || line === -1)
      return Decoration.none
    const deco = Decoration.widget({
        widget: new CursorWidget(),
        side: -1
    });
    return Decoration.set([
        deco.range(view.state.doc.line(line).from+column)
    ])
}

const columnCursorPlugin = ViewPlugin.fromClass(class {
    decorations: DecorationSet

    constructor(view: EditorView) {
        this.decorations = Decoration.none
    }

    update(update: ViewUpdate) {
        if (
          update.startState.facet(karelColumnFacet) !== update.state.facet(karelColumnFacet)
          || update.startState.facet(karelLineFacet) !== update.state.facet(karelLineFacet)
        )
            this.decorations = cursor(update.view)
    }
}, {
    decorations: v => v.decorations,

})

export function highlightKarelActiveInstruction(): Extension {
  return [
    karelLineTheme,
    highlightedLine.of(karelLineFacet.of(-1)),
    executionCursor.of(karelColumnFacet.of(-1)),
    lineHighlighting,
    columnCursorPlugin
  ]
}


  
export function HighlightKarelInstruction(editor:EditorView, line:number, column: number) {
  editor.dispatch({
    effects: [
      highlightedLine.reconfigure(karelLineFacet.of(line)),
      executionCursor.reconfigure(karelColumnFacet.of(column))
    ]
  })
}
