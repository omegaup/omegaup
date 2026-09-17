import { Decoration, DecorationSet} from "@codemirror/view"
import {EditorView} from "@codemirror/view"
import {StateField, StateEffect, RangeSet} from "@codemirror/state"

const parseErrorState = StateEffect.define<{from: number, to: number}>({
    map: ({from, to}, change) => ({from: change.mapPos(from), to: change.mapPos(to)})
  })
  
  const underlineMark = Decoration.mark({class: "cm-underline"})
  
  const parseErrorField =  StateField.define<DecorationSet>({
    create() {
      return Decoration.none
    },
    update(underlines, tr) {
      underlines = underlines.map(tr.changes)
      for (let e of tr.effects) if (e.is(parseErrorState)) {
        if (e.value.from === -1) {
          underlines = RangeSet.empty
        }else {
          underlines = underlines.update({
            add: [underlineMark.range(e.value.from, e.value.to)]
          })
        }
      }
      return underlines
    },
    provide: f => EditorView.decorations.from(f)
  })
  const underlineTheme = EditorView.baseTheme({
    ".cm-underline": { 
      backgroundImage: `url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="6" height="3">%3Cpath%20d%3D%22m0%202.5%20l2%20-1.5%20l1%200%20l2%201.5%20l1%200%22%20stroke%3D%22%23d11%22%20fill%3D%22none%22%20stroke-width%3D%22.9%22%2F%3E</svg>')`,
      backgroundPosition: "left bottom",
      backgroundRepeat: "repeat-x",
      paddingBottom: "0.7px",
  
     }
     
  })
  
  
  export function underlineError(view: EditorView, line:number, from:number, to:number) {
    if (from === to) {
      return;
    }
    let real_from = view.state.doc.line(line).from + from;
    let real_to = view.state.doc.line(line).from + to;
    let effects:StateEffect<unknown>[] =  [parseErrorState.of({from:real_from, to:real_to})]
  
  
    if (!view.state.field(parseErrorField, false))
      effects.push(StateEffect.appendConfig.of([parseErrorField,
                                                underlineTheme]))
    view.dispatch({effects})
    return true
  }
  
  
  export function clearUnderlineError(view: EditorView) {
    let effects:StateEffect<unknown>[] =  [parseErrorState.of({from:-1, to:-1})]
  
  
    if (!view.state.field(parseErrorField, false))
      effects.push(StateEffect.appendConfig.of([parseErrorField,
                                                underlineTheme]))
    view.dispatch({effects})
    return true
  }