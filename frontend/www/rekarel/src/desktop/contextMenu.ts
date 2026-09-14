import bootstrap from "bootstrap"
import { WorldViewController } from "../worldViewController/worldViewController"
import { AppVars } from "../volatileMemo"
import { EvaluateToolbar } from "./evaluate"
import { WorldBar, WorldToolbarData } from "./worldToolbar"

export type ContextMenuData = {
    toggler: JQuery,
    container: JQuery
    worldBar: WorldToolbarData,
    evaluate: EvaluateToolbar
    
}

export class DesktopContextMenu {
    toggler: JQuery
    container: JQuery
    worldBar: WorldBar
    evaluate: EvaluateToolbar


    constructor(data: ContextMenuData, worldCanvas:JQuery, worldController:WorldViewController) {
        this.toggler = data.toggler;
        this.container = data.container;
        this.worldBar = new WorldBar(data.worldBar);
        this.evaluate = data.evaluate;
        this.Hook(worldCanvas, worldController);
    }

    
    private Hook(worldCanvas:JQuery, worldController:WorldViewController ) {
        worldCanvas.on("contextmenu", (e)=>{
            const dropmenu =new bootstrap.Dropdown(this.toggler[0]);
            dropmenu.hide();
            this.container[0].style.setProperty("top", `${e.pageY}px`);
            this.container[0].style.setProperty("left", `${e.pageX}px`);      
            this.ToggleContextMenu();
            e.preventDefault();
        })
        const ContextAction = (target: JQuery, method:()=> void) => {
            target.on(
                "click",
                (()=> {
                    this.ToggleContextMenu();
                    method();
                }).bind(this)
            );
        }
        this.worldBar.Connect();
        this.worldBar.OnClick((()=> {
            this.ToggleContextMenu();
        }).bind(this))

        ContextAction(this.evaluate.evaluate, ()=>worldController.SetCellEvaluation(true) );
        ContextAction(this.evaluate.ignore, ()=>worldController.SetCellEvaluation(false) );
    }

    ToggleContextMenu() {
        const dropmenu = new bootstrap.Dropdown(this.toggler[0]);
        if (this.toggler.attr("aria-expanded")==="false") {
            dropmenu.show();
        } else {
            dropmenu.hide();
        }
    }

}