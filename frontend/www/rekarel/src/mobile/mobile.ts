import { ControlBar, ControlBarData } from "../desktop/controlBar"
import { FocusBar, FocusToolbar } from "../desktop/focusBar"
import { WorldBar, WorldToolbarData } from "../desktop/worldToolbar"
import { getEditors } from "../editor/editorsInstances"
import { KarelController } from "../KarelController"
import { WorldViewController } from "../worldViewController/worldViewController"
import { ZoomControl, ZoomControlData } from "./zoomControl"

type MobileUIData = {
    controls:ControlBarData
    focus: FocusToolbar
    startExec: JQuery,
    worldBar: WorldToolbarData,
    previousOpBtn:JQuery,
    zoomControl:ZoomControlData,
}


type MobileState = "execution" | "code" | "world"


export class MobileUI {
    private controlBar: ControlBar
    private focusBar: FocusBar
    private worldBar: WorldBar
    private startExec: JQuery
    private previousOpBtn: JQuery
    private state: MobileState
    private zoomControl:ZoomControl
    private static _instance:MobileUI

    constructor(data:MobileUIData) {
        MobileUI._instance = this;
        this.controlBar = new ControlBar(data.controls, WorldViewController.GetInstance());
        this.focusBar = new FocusBar(data.focus);
        this.worldBar = new WorldBar(data.worldBar);
        this.startExec = data.startExec;
        this.previousOpBtn = data.previousOpBtn;
        this.controlBar.Init();
        this.focusBar.Connect();
        this.worldBar.Connect();
        this.worldBar.OnClick(this.OnWorldOp.bind(this));

        KarelController.GetInstance().RegisterStateChangeObserver((_, state)=> {
            if (state === "unstarted" && this.state === "execution") {
                this.SetState("world");
                return;
            }
            if (state !== "unstarted" && this.state !== "execution") {
                this.SetState("execution");
            }
        });
        $(`*[data-kl-state="world"]`).addClass("d-none");
        $(`*[data-kl-state="execution"]`).addClass("d-none");
        $(`*[data-kl-state="code"]`).addClass("d-none");
        this.state ="world";
        this.SetState("world");

        this.Connect();

        const editor = getEditors()[0];
        $(editor.contentDOM).on("focus", ()=> {
            if (this.state !== "execution") {
                this.SetState("code");
            }
        })

        $("#worldCanvas").on("focus", ()=> {            
            if (this.state !== "execution") {
                this.SetState("world");
            }
        })

        this.zoomControl = new ZoomControl(data.zoomControl);
    }

    static GetInstance() {
        return this._instance;
    }

    SetState(state:MobileState) {
        $(`*[data-kl-state="${this.state}"]`).addClass("d-none");;
        $(`*[data-kl-state2="${this.state}"]`).addClass("d-none");;
        this.state = state;        
        $(`*[data-kl-state="${this.state}"]`).removeClass("d-none");
        $(`*[data-kl-state2="${this.state}"]`).removeClass("d-none");

    }

    private Connect() {
        this.startExec.on("click", ()=> {
            this.SetState("execution");
        })
    }

    private OnWorldOp(e: JQuery.ClickEvent) {
        const target = $(e.target);
        const icon = target.find("i"); 
        this.previousOpBtn.empty();
        this.previousOpBtn.append(icon.clone());
        this.previousOpBtn.off("click.repeat");
        this.previousOpBtn.on("click.repeat", ()=>target.trigger("click"));
    }
    
}