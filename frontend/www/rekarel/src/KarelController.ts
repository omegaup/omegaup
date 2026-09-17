import { DebugData, DumpTypes, World, compile, detectLanguage } from "@rekarel/core";
// import { WorldViewController } from "./worldViewController";
import { EditorView } from "codemirror";
import { decodeRuntimeError } from "./errorCodes";
import { setLanguage } from "./editor/editor";
import { GetCurrentSetting } from "./settings";
import { throbber } from "./throbber";
import { getEditors } from "./editor/editorsInstances";
import { Callbacks } from "jquery";
import { CheckForBreakPointOnLine } from "./editor/editor.breakpoint";
import { clearUnderlineError, underlineError } from "./editor/editor.parseErrorUnderline";
import { testSkipFlag } from "./editor/editor.skippable";
import { KarelHistory } from "./worldViewController/commit";
import { decodeError } from "./compilationError";

type messageType = "info"|"success"|"error"|"raw"|"warning";
type MessageCallback = (message:string, type:messageType)=>void;
type ControllerState = "unstarted"| "running" | "finished" | "paused" | "autoStepping" | "fastStepping";
type StateChangeCallback = (caller:KarelController, newState:ControllerState)=>void;
type StepCallback = (caller:KarelController, newState:ControllerState)=>void;
type ResetCallback = (caller:KarelController)=>void;
type NewWorldCallback = (caller:KarelController, world:World, newInstance:boolean)=>void;
type CompileCallback = (caller:KarelController, success:boolean, language:string)=>void;
type SlowModeCallback = (caller:KarelController, limit:number)=>void;
class KarelController {
    
    private static instance:KarelController


    world: World;
    // desktopController: WorldViewController;
    running: boolean;
    private onMessage: MessageCallback[];
    private onStateChange: StateChangeCallback[];
    private onStep: StepCallback[];
    private onReset: ResetCallback[];
    private onNewWorld: NewWorldCallback[];
    private onCompile: CompileCallback[];
    private onSlowMode: SlowModeCallback[];
    private state : ControllerState;
    private endedOnError:boolean;
    private autoStepInterval:number;
    private drawFrameRequest : number;
    private autoStepping: boolean;
    private fastStepping:boolean; 
    private history: KarelHistory;
    private debugData: DebugData 

    constructor(world: World) {
        this.world = world;
        this.running = false;
        this.onMessage = [];
        this.onStateChange = [];
        this.onStep = [];
        this.onReset= [];
        this.onNewWorld= [];
        this.onCompile= [];
        this.onSlowMode = [];
        this.state = "unstarted";
        this.endedOnError = false;
        this.autoStepInterval = 0;
        this.autoStepping = false;
        this.fastStepping = false;

        KarelController.instance = this;
        this.history = new KarelHistory();
    }

    static GetInstance() {
        return KarelController.instance;
    }

    GetHistory() {
        return this.history;
    }
    
    GetDebugData() {
        return this.debugData;
    }
    // SetDesktopController(desktopController: WorldViewController) {
    //     this.desktopController = desktopController;
    //     this.desktopController.SetWorld(this.world);
        
    //     this.OnStackChanges();
    // }

    Compile(notifyOnSuccess:boolean = true) {
        const mainEditor = getEditors()[0];
        let code = mainEditor.state.doc.toString();

        // let language: string = detectLanguage(code);
        let language = detectLanguage(code) as "java" | "pascal" | "ruby" | "none";
            
        if (language === "java" || language === "pascal") {
            setLanguage(mainEditor, language);
        }
        let response = null;
        try {
            clearUnderlineError(mainEditor)
            response = compile(code, true);
            //TODO: expand message       
            if (notifyOnSuccess)     
                this.SendMessage("Programa compilado correctamente", "info");
            this.NotifyCompile(true, language);
        } catch (e) {            
            //TODO: Expand error
            this.SendMessage(decodeError(e, language), "error");
            if (e.hash.loc) {
                const status = e.hash;
                underlineError(mainEditor, status.loc.first_line, status.loc.first_column, status.loc.last_column);
            }
            this.NotifyCompile(false, language);
            return null;
        }
        this.debugData = response[1]
        return response[0];
    }

    // FIXME This is code from karel.js that I'm not even sure if it's ever executed by the web app.
    validatorCallbacks(message) {
        console.log("validator said this: ", message);
    }
    
    IsAutoStepping(): boolean {
        return this.autoStepping;
    }

    GetState(): ControllerState {
        return this.state;
    }

    Reset() {        
        this.endedOnError = false;
        this.world.reset();
        this.running = false;
        this.ChangeState("unstarted");
        this.NotifyReset();
    }

    GetRuntime() {
        return this.world.runtime;
    }

    StartRun(): boolean {        
        this.endedOnError = false;
        this.SendMessage("<hr>", "raw");
        let compiled = this.Compile();
        if (compiled == null) {
            return false;
        }
        this.Reset();
        let runtime = this.GetRuntime();        
        runtime.load(compiled);
        runtime.disableStackEvents = false;
        this.running = true;
        this.ChangeState("running");
        return true;        
    }

    Pause() {
        if (this.state !== "running") return;
        this.StopAutoStep();
        this.ChangeState("paused")
    }

    CheckForBreakPointOnCurrentLine(notifyOnTrue:boolean = true):boolean {
        let runtime= this.GetRuntime();
        if (runtime.state.line >= 0) {            
            const mainEditor = getEditors()[0];
            let hasBreakpoint = CheckForBreakPointOnLine(mainEditor, runtime.state.line+1)       
            if (hasBreakpoint && notifyOnTrue) {                    
                this.BreakPointMessage(runtime.state.line +1);
            }
            return hasBreakpoint;
           
        }
        return false;
      }
    


    Step() {
        if (!this.StartStep()) return;
        
        let runtime = this.GetRuntime();
        runtime.step();
        const mainEditor = getEditors()[0];

        if (testSkipFlag(mainEditor, runtime.state.line !== 0?runtime.state.line:1)) {
            this.StepOut();
        } else { 
            this.EndStep();
        }
    }

    StepOver() {
        if (!this.StartStep()) return;
        
        const runtime = this.GetRuntime();
        const startWStackSize = runtime.state.stackSize;
        runtime.step();
        if (runtime.state.stackSize > startWStackSize) {
            throbber.performTask(
                function * (this:KarelController) {
                    while (this.PerformFastStep() && runtime.state.stackSize > startWStackSize) yield;
                    if (!this.CheckForBreakPointOnCurrentLine(false))
                        runtime.step();
                }.bind(this)
            )
            .then(()=> this.EndStep())
        } else {
            this.EndStep();
        }
    }

    StepOut() {
        if (!this.StartStep()) return;
        const runtime = this.GetRuntime();
        const startWStackSize = runtime.state.stackSize;
        if (startWStackSize === 0) {
            this.RunTillEnd();
            return;
        }
        this.fastStepping = true;
        throbber.performTask(
            function * (this:KarelController) {
                while (this.PerformFastStep() && runtime.state.stackSize >= startWStackSize) yield;
                this.fastStepping = false;
            }.bind(this),
            ()=> {
                this.fastStepping = false;
                this.Pause();
            }
        ).then(_ => this.EndStep());
    }

    StartAutoStep(delay:number) {        
        this.StopAutoStep(); //Avoid thread leak
        if (this.state === "finished") {
            return false;
        }
        this.autoStepping = true;
        if (!this.running) {
            if (!this.StartRun()) {
                //Code Failed
                return false;
            }
        }
        if (this.state !== "running") {
            this.ChangeState("running");
        }
        this.autoStepInterval = window.setInterval(
            ()=>{
                if (!this.running) {
                    this.StopAutoStep();
                    return;
                }
                if (this.fastStepping) {
                    //Is fastStepping, wait for it to end.
                    return;
                }
                this.Step();
            }, 
            delay
        );
        return true;
    }


    ChangeAutoStepDelay(delay:number) {
        if (!this.IsAutoStepping()) {
            return;
        }
        this.StartAutoStep(delay);
    }

    StopAutoStep() {
        this.autoStepping = false;
        if (this.autoStepInterval !== 0) {
            clearInterval(this.autoStepInterval);
            this.autoStepInterval = 0;
        }
    }


    EndedOnError() {
        return this.endedOnError;
    }
    async RunTillEnd(ignoreBreakpoints:boolean = false) {
        if (this.state === "finished") {
            return;
        }
        if (!this.running) {
            if (!this.StartRun()) {
                return;
            }
        }
        this.fastStepping = true;
        let runtime = this.GetRuntime();
        // runtime.disableStackEvents= false; // FIXME: This should only be done when no breakpoints
        // runtime.disableStackEvents= true; // FIXME: This should only be done when no breakpoints
        await throbber.performTask(
            function * (this:KarelController) {
                while (this.PerformFastStep(ignoreBreakpoints)) yield;
            }.bind(this)
        ).then(()=> {
            this.EndStep()
        });
    }

    RegisterMessageCallback(callback: MessageCallback) {
        this.onMessage.push(callback);
    }

    RegisterStateChangeObserver(callback: StateChangeCallback) {
        this.onStateChange.push(callback);
    }

    RegisterResetObserver(callback: ResetCallback) {
        this.onReset.push(callback);
    }

    RegisterNewWorldObserver(callback: NewWorldCallback) {
        this.onNewWorld.push(callback);
    }

    RegisterStepController(callback: StepCallback) {
        this.onStep.push(callback);
    }

    RegisterCompileObserver(callback: CompileCallback) {
        this.onCompile.push(callback);
    }

    RegisterSlowModeObserver(callback: SlowModeCallback) {
        this.onSlowMode.push(callback);
    }

    Resize(w:number, h:number) {
        this.Reset();
        this.world.resize(w, h);    
        this.NotifyNewWorld(false);
    }

    NewWorld() {
        this.Reset();
        const w = this.world.w;
        const h = this.world.h;
        this.world = new World(w, h);
        this.NotifyNewWorld(true);
        // this.OnStackChanges(); // This causes a bug where the pile is double
    }

    LoadWorld(world: Document) {
        this.world.load(world);
        this.NotifyNewWorld(false);
    }

    private StartStep() {
        if (this.state === "finished") {
            //Ignore if the code already finished running
            return false;
        }
        if (!this.running) {
            if (!this.StartRun()) {
                // Code Failed
                return false;
            }
        }
        return true;
    }

    private EndStep() {
        this.fastStepping = false;
        if (!this.GetRuntime().state.running) {            
            this.EndMessage();
            this.ChangeState("finished");
        }
        if (this.CheckForBreakPointOnCurrentLine()) {
            this.Pause();
            this.NotifyStep();
            return;
        }

        this.NotifyStep();
    }

    private PerformFastStep(ignoreBreakpoints:boolean = false) {
        const runtime = this.GetRuntime();
        const result = runtime.step();
        const slowLimit = GetCurrentSetting().slowExecutionLimit; 
        if (runtime.state.ic >=  slowLimit&& !runtime.disableStackEvents) {
            runtime.disableStackEvents = true;
            this.SendMessage(`Karel alcanzó las ${slowLimit.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")} instrucciones, Karel cambiará al modo rápido de ejecución, la pila de llamadas dejará de actualizarse`, "warning");
            this.NotifySlowMode(slowLimit);
        }
        
        
        return result && (ignoreBreakpoints || !this.CheckForBreakPointOnCurrentLine(false));
    }

    private SendMessage(message: string, type: messageType) {
        this.onMessage.forEach((callback) => callback(message, type));
    }

    private NotifyStateChange() {
        this.onStateChange.forEach((callback) => callback(this, this.state));
    }

    private NotifyReset() {
        this.onReset.forEach((callback) => callback(this));
    }

    private NotifyNewWorld(usedNewInstance:boolean ) {
        this.onNewWorld.forEach((callback) => callback(this, this.world, usedNewInstance));
    }

    private NotifyStep() {
        this.onStep.forEach((callback) => callback(this, this.state));
    }
    private NotifyCompile(success:boolean, language:string) {
        this.onCompile.forEach((callback) => callback(this, success, language));
    }

    private NotifySlowMode(limit:number) {
        this.onSlowMode.forEach((callback)=>callback(this, limit));
    }

    
    private ChangeState(nextState: ControllerState) {
        this.state = nextState;
        this.NotifyStateChange();
    }

    private EndMessage() {
        let state = this.GetRuntime().state;
        if (state.error) {
            this.SendMessage(
                decodeRuntimeError(
                    state.error, {
                        maxInstructions:{
                            general: this.world.maxInstructions,
                            left: this.world.maxTurnLeft,
                            forward: this.world.maxMove,
                            pick: this.world.maxPickBuzzer,
                            leave: this.world.maxLeaveBuzzer,
                        }, 
                        stackSize: this.world.maxStackSize,
                        callMaxParam: this.world.maxCallSize,
                        stackMemory: this.world.maxStackMemory
                    }), 
                "error"
            );            
            this.endedOnError = true;
            return;
        }
        this.SendMessage("Ejecucion terminada exitosamente!", "success");
        let inner = "";
        if (this.world.getDumps(DumpTypes.DUMP_MOVE)){
            inner += `<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto">Avanza </div><span class="badge bg-primary rounded-pill">${state.moveCount}</span></li>`
        }
        if (this.world.getDumps(DumpTypes.DUMP_LEFT)) {
            inner += `<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto">Gira izquierda </div><span class="badge bg-primary rounded-pill">${state.turnLeftCount}</span></li>`
        }
        if (this.world.getDumps(DumpTypes.DUMP_PICK_BUZZER)) {
            inner += `<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto">Coge zumbador </div><span class="badge bg-primary rounded-pill">${state.pickBuzzerCount}</span></li>`
        }
        if (this.world.getDumps(DumpTypes.DUMP_LEAVE_BUZZER)) {
            inner += `<li class="list-group-item d-flex justify-content-between align-items-start"><div class="ms-2 me-auto">Deja zumbador</div> <span class="badge bg-primary rounded-pill">${state.leaveBuzzerCount}</span></li>`
        }
        if (inner !== "") {
             this.SendMessage(
                '<ul class="list-group list-group-flush">'+inner+ '</ul>', 
                "raw"
            )
        }
    }



    private BreakPointMessage(line:number) {
        this.SendMessage(`🔴 ${line}) Breakpoint `, "info");
    }
    


    
}



export {KarelController, ControllerState};