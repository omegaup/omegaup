import { EditorView } from 'codemirror'
import bootstrap from 'bootstrap';
import { WorldRenderer, WRStyle } from '../worldRenderer';
import { WorldViewController, Gizmos } from "../worldViewController/worldViewController";
import { ControllerState, KarelController } from '../KarelController';
import { freezeEditors, RegisterEditorTextSetListener, unfreezeEditors } from '../editor/editor';
import { ContextMenuData, DesktopContextMenu } from './contextMenu';
import { CallStack, CallStackUI } from './callStack';
import { ConsoleTab, KarelConsole } from './console';
import { DefaultWRStyle } from '../KarelStyles';
import { ControlBar, ControlBarData } from './controlBar';
import { AppVars } from '../volatileMemo';
import { HistoryToolbar } from '../worldViewController/commit';
import { GetCurrentSetting } from '../settings';
import { FocusBar, FocusToolbar } from './focusBar';
import { WorldBar, WorldToolbarData } from './worldToolbar';
import { EvaluateToolbar } from './evaluate';
import { ToastController, ToastUI } from './toast';
import { KarelNumbers } from '@rekarel/core';


type InputModeToolbar = {
    indicator: JQuery,
    alternate:JQuery,
    drag:JQuery
}

interface DesktopElements {
    desktopEditor: EditorView
    worldContainer: JQuery,
    worldCanvas: JQuery,
    gizmos: Gizmos,
    lessZoom: JQuery,
    moreZoom: JQuery,
    controlBar: ControlBarData,    
    inputMode: InputModeToolbar
    worldToolbar: WorldToolbarData,    
    focus: FocusToolbar,
    history: HistoryToolbar,
    evaluate: EvaluateToolbar,
    context: ContextMenuData,
    console: ConsoleTab,
    callStack: CallStackUI,
    toast: ToastUI
};

class DesktopController {
    editor: EditorView;

    worldContainer: JQuery;
    worldCanvas: JQuery;
    lessZoom: JQuery;
    moreZoom: JQuery;
    
    controlbar: ControlBar

    beeperBagInput: JQuery
    infiniteBeeperInput: JQuery

    delayInput: JQuery
    delayAdd: JQuery
    delayRemove: JQuery

    inputModeToolbar: InputModeToolbar;

    worldBar: WorldBar;
    evaluateToolbar: EvaluateToolbar;

    focusControlBar: FocusBar;
    historyToolbar: HistoryToolbar;

    contextMenu: DesktopContextMenu

    worldController: WorldViewController;
    karelController: KarelController;

    console:KarelConsole
    callStack: CallStack

    toasts: ToastController

    private isControlInPlayMode: boolean
    private static _instance: DesktopController
    
    constructor (elements: DesktopElements, karelController: KarelController) {
        this.editor = elements.desktopEditor;
        this.worldContainer = elements.worldContainer;
        this.worldCanvas = elements.worldCanvas;
        this.lessZoom = elements.lessZoom;
        this.moreZoom = elements.moreZoom;

        this.beeperBagInput = elements.controlBar.beeperInput;
        this.infiniteBeeperInput = elements.controlBar.infiniteBeeperInput;

        this.delayInput = elements.controlBar.delayInput;
        this.delayAdd = elements.controlBar.delayAdd;
        this.delayRemove = elements.controlBar.delayRemove;

        this.inputModeToolbar = elements.inputMode;
        this.worldBar = new WorldBar(elements.worldToolbar);
        this.evaluateToolbar = elements.evaluate;

        this.focusControlBar = new FocusBar(elements.focus);
        this.historyToolbar = elements.history;

        
        
        this.console =  new KarelConsole(elements.console);
        
        this.karelController = karelController;
        
        this.worldController = new WorldViewController(
            new WorldRenderer(
                (this.worldCanvas[0] as HTMLCanvasElement).getContext("2d"),
                DefaultWRStyle,
                1
            ),
            karelController,
            elements.worldContainer[0],
            elements.gizmos
        );
        this.contextMenu = new DesktopContextMenu(elements.context, elements.worldCanvas, this.worldController);
        this.karelController.RegisterStateChangeObserver(this.OnKarelControllerStateChange.bind(this));
        
        this.isControlInPlayMode = false;
        this.callStack = new CallStack(elements.callStack);
        this.controlbar = new ControlBar(elements.controlBar, this.worldController);
        this.toasts = new ToastController(elements.toast)

        DesktopController._instance = this;

    }

    static GetInstance() {
        return DesktopController._instance;
    }

    Init() {
        $(window).on("resize", this.ResizeCanvas.bind(this));
        $(window).on("focus", this.ResizeCanvas.bind(this));
        $(window).on("keydown", this.HotKeys.bind(this));

        this.worldContainer.on("scroll", this.calculateScroll.bind(this));
        $("body").on(
            "mouseup",
            this.worldController.ClickUp.bind(this.worldController)
        );
        this.worldCanvas.on(
            "mousemove",
            this.worldController.TrackMouse.bind(this.worldController)
        );
        this.worldCanvas.on(
            "mousedown",
            this.worldController.ClickDown.bind(this.worldController)
        );
        this.worldCanvas.on(
            "touchstart",
            ()=>{
                if (GetCurrentSetting().autoInputMode === true) 
                    this.SetAlternativeInput();
            }
        );

        
        this.worldCanvas.on(
            "pointerdown",
            this.worldController.PointerDown.bind(this.worldController)
        );
        this.worldCanvas.on(
            "pointerup pointercancel pointerout pointerleave",
            this.worldController.PointerUp.bind(this.worldController)
        );
        this.worldCanvas.on(
            "pointermove",
            this.worldController.PointerMove.bind(this.worldController)
        );
        

        
        this.lessZoom.on("click", ()=> {
            let scale = this.worldController.scale / 1.41421356;
            if (scale < 0.25) { // FIXME: Dont hardcode this value
                scale = 0.25;
            }
            this.worldController.SetScale(scale);
        });
        this.moreZoom.on("click", ()=> {
            let scale = this.worldController.scale * 1.41421356;
            if (scale > 8) { // FIXME: Dont hardcode this value
                scale = 8;
            }
            this.worldController.SetScale(scale);
            
        });

        this.controlbar.Init();

        this.ConnectToolbar();        
        
        this.ResizeCanvas();
        this.worldController.FocusKarel();
        this.ConnectConsole();

        RegisterEditorTextSetListener(()=> {
            this.karelController.Reset();
        })
    }

    private OnKarelControllerStateChange(sender: KarelController, state: ControllerState) {
        if (state === "running") {            
            freezeEditors(this.editor);
            this.worldController.Lock();
        }
        if (state === "finished") {
            if (this.karelController.EndedOnError()) {
                this.worldController.ErrorMode();
            }
            freezeEditors(this.editor);
            this.worldController.Lock();

        } else if (state === "unstarted") {  
            unfreezeEditors(this.editor);
            this.worldController.UnLock();
            this.worldController.NormalMode();
        } 
    }

    private ConnectToolbar() {      
        this.inputModeToolbar.alternate.on("click", ()=>this.SetAlternativeInput());
        this.inputModeToolbar.drag.on("click", ()=>this.SetDragInput());
    
        this.worldBar.Connect();

        this.focusControlBar.Connect()


        this.evaluateToolbar.evaluate.on("click", ()=> this.worldController.SetCellEvaluation(true));
        this.evaluateToolbar.ignore.on("click", ()=> this.worldController.SetCellEvaluation(false));


        
        this.historyToolbar.undo.on("click", ()=>this.worldController.Undo());
        this.historyToolbar.redo.on("click", ()=>this.worldController.Redo());
    }

    private SetAlternativeInput() {
        this.inputModeToolbar.indicator.removeClass("bi-mouse2")
        this.inputModeToolbar.indicator.addClass("bi-hand-index-thumb")
        this.worldController.SetClickMode("alternate");
    }  

    private SetDragInput() {
        this.inputModeToolbar.indicator.removeClass("bi-hand-index-thumb")
        this.inputModeToolbar.indicator.addClass("bi-mouse2")
        this.worldController.SetClickMode("normal");
    }    

    private ConnectConsole() {        
        this.karelController.RegisterMessageCallback(this.ConsoleMessage.bind(this));
    }
    

    public ConsoleMessage(message: string, type:"info"|"success"|"error"|"raw"|"warning" = "info") {
        let style="info";
        switch (type) {
            case "info":
                style = "info";
                break;
            case "success":
                style = "success";
                break;
            case  "error":
                style="danger";
                break;
            case  "warning":
                style="warning";
                break;
            case "raw":
                style="raw";
                break;
        }        
        this.console.SendMessageToConsole(message, style);
    }

    private HotKeys(e: JQuery.KeyDownEvent) {
        let tag = e.target.tagName.toLowerCase();
        let role = document.activeElement.getAttribute("role");
        if (role=="textbox" || tag=="input" || role === "dialog" ) {
            return;
        }
       
        const overrideShift = new Set<number>([37, 38, 39, 40]);
        type keyMod = "yes" | "no" | "optional";
        type hotkeyMod = {shift:keyMod, ctrl: keyMod, alt: keyMod};
        const basic:hotkeyMod = {shift:"optional", ctrl:"no", alt:"no"};
        const ctrl:hotkeyMod = {shift:"no", ctrl:"yes", alt:"no"};
        const shift:hotkeyMod = {shift:"yes", ctrl:"no", alt:"no"};
        const altBasic:hotkeyMod = {shift:"optional", ctrl:"no", alt:"yes"};
        const alt:hotkeyMod = {shift:"no", ctrl:"no", alt:"yes"};
        const beeper:hotkeyMod = {shift:"optional", ctrl:"no", alt:"optional"};

        let placeBeepers = (n: number) => {
            if (!e.altKey) {
                this.worldController.SetBeepers(n);
            } else {  
                this.worldController.AppendUnitToBeepers(n);
            }
        }

        let hotkeys = new Map<number, [hotkeyMod,()=>void][]>([
            [71,[[basic, ()=>{this.worldController.ToggleKarelPosition(true);}]]],
            [80,[[basic,()=>{this.worldController.ToggleKarelPosition(false);}]]],
            [82,[
                    [basic,()=>{this.worldController.SetRandomBeepers(AppVars.randomBeeperMinimum,AppVars.randomBeeperMaximum);}],
                    [alt,()=>{(bootstrap.Modal.getOrCreateInstance("#randomBeepersModal")).show()}],
                ]
            ],
            [81, [[basic,()=>{this.worldController.ChangeBeepers(-1);}]]],
            [69, [[basic, ()=>{this.worldController.ChangeBeepers(1);}]]],
            [48, [[beeper, ()=>{placeBeepers(0);}]]],
            [49, [[beeper, ()=>{placeBeepers(1);}]]],
            [50, [[beeper, ()=>{placeBeepers(2);}]]],
            [51, [[beeper, ()=>{placeBeepers(3);}]]],
            [52, [[beeper, ()=>{placeBeepers(4);}]]],
            [53, [[beeper, ()=>{placeBeepers(5);}]]],
            [54, [[beeper, ()=>{placeBeepers(6);}]]],
            [55, [[beeper, ()=>{placeBeepers(7);}]]],
            [56, [[beeper, ()=>{placeBeepers(8);}]]],
            [57, [[beeper, ()=>{placeBeepers(9);}]]],
            [96, [[beeper, ()=>{placeBeepers(0);}]]],
            [97, [[beeper, ()=>{placeBeepers(1);}]]],
            [98, [[beeper, ()=>{placeBeepers(2);}]]],
            [99, [[beeper, ()=>{placeBeepers(3);}]]],
            [100,[[beeper, ()=>{placeBeepers(4);}]]],
            [101,[[beeper, ()=>{placeBeepers(5);}]]],
            [102,[[beeper, ()=>{placeBeepers(6);}]]],
            [103,[[beeper, ()=>{placeBeepers(7);}]]],
            [104,[[beeper, ()=>{placeBeepers(8);}]]],
            [105,[[beeper, ()=>{placeBeepers(9);}]]],
            [67, [[basic, ()=>{$("#desktopSetAmmount").trigger("click")}]]], // FIXME
            [87, [[basic, ()=>{this.worldController.ToggleWall("north");}]]],
            [68, [[basic, ()=>{this.worldController.ToggleWall("east");}]]],
            [83, [[basic, ()=>{this.worldController.ToggleWall("south");}]]],
            [65, [[basic, ()=>{this.worldController.ToggleWall("west");}]]],
            [88, [[basic, ()=>{this.worldController.ToggleWall("outer");}]]],
            [37, [[basic, ()=>{this.worldController.MoveSelection(0,-1, e.shiftKey);}]] ],
            [38, [[basic, ()=>{this.worldController.MoveSelection(1, 0, e.shiftKey);}]]],
            [39, [[basic, ()=>{this.worldController.MoveSelection(0, 1, e.shiftKey);}]]],
            [40, [[basic, ()=>{this.worldController.MoveSelection(-1, 0, e.shiftKey);}]]],
            [84, [[basic, ()=>{this.worldController.SetBeepers(KarelNumbers.a_infinite);}]]],
            [86, [[basic, ()=>{this.worldController.SetCellEvaluation(false);}]]],
            [8,  [
                    [basic, ()=>{this.worldController.RemoveEverything();}],
                    [altBasic, () => {this.worldController.DivideBeepers(10); }]
                ]
            ],
            [46, [[basic, ()=>{this.worldController.RemoveEverything();}]]],
            [89, [[ctrl, ()=>{this.worldController.Redo();}]]],
            [90, [
                    [ctrl, ()=>{this.worldController.Undo();}],
                    [basic, ()=>{this.worldController.SetCellEvaluation(true);}]
            ]],
            
        ]);
        if (hotkeys.has(e.which) === false) {
            return;
        }     
        
        if (e.shiftKey && !overrideShift.has(e.which)) {

            let dummy: MouseEvent = new MouseEvent("", {
                clientX: e.clientX,
                clientY: e.clientY
            });
            this.worldController.ClickDown(dummy);
            this.worldController.ClickUp(dummy);
        }
        const options = hotkeys.get(e.which)
        for (let option of options) {
            if (option[0].ctrl === "yes" && !e.ctrlKey) continue;
            if (option[0].ctrl === "no" && e.ctrlKey) continue;
            
            if (option[0].alt === "yes" && !e.altKey) continue;
            if (option[0].alt === "no" && e.altKey) continue;
            
            if (option[0].shift === "yes" && !e.shiftKey) continue;
            if (option[0].shift === "no" && e.shiftKey) continue;
            option[1]();
            e.preventDefault();
            break;
        }
    }

    private calculateScroll() {
        let left = 0, top=1;
        const container = this.worldContainer[0];
        if (container.scrollWidth !== container.clientWidth) {
            left = container.scrollLeft / (container.scrollWidth - container.clientWidth);
        }
        if(container.scrollHeight !== container.clientHeight) {
            top = 
                1 - container.scrollTop
                / (container.scrollHeight - container.clientHeight);
        }
        this.worldController.UpdateScroll(left, top);
    }

    public ResizeCanvas() {
        this.worldCanvas[0].style.width = `${this.worldContainer[0].clientWidth}px`;    
        this.worldCanvas[0].style.height = `${this.worldContainer[0].clientHeight}px`;
        let scale = window.devicePixelRatio;
        this.worldCanvas.attr(
            "width", Math.floor(this.worldContainer[0].clientWidth * scale)
        );    
        this.worldCanvas.attr(
            "height", Math.floor(this.worldContainer[0].clientHeight * scale)
        );
        // this.worldController.RecalculateScale();

        this.worldController.Update();        
        this.calculateScroll();
    }
    
}


export {DesktopController};