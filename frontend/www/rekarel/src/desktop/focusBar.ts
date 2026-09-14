import { WorldViewController } from "../worldViewController/worldViewController";

export type FocusToolbar = {
    origin: JQuery,
    karel: JQuery,
    selector: JQuery,
    
}

export class FocusBar {    
    data: FocusToolbar
    
    constructor(ui:FocusToolbar) {
        this.data = ui;
    }
    
    Connect() {        
        const worldController = WorldViewController.GetInstance();
        this.data.karel.on("click", ()=>worldController.FocusKarel());
        this.data.origin.on("click", ()=>worldController.FocusOrigin());
        this.data.selector.on("click", ()=>worldController.FocusSelection());
    }
}