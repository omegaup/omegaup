import bootstrap from "bootstrap";
import { ControllerState, KarelController } from "../KarelController";
import { WorldViewController } from "../worldViewController/worldViewController";
import { AppVars } from "../volatileMemo";
import { KarelNumbers } from "@rekarel/core";

const MAX_DELAY = 3000;

type ExecutionToolbar = {
    reset: JQuery,
    compile: JQuery,
    run: JQuery,
    step: JQuery,
    stepOut: JQuery,
    stepOver: JQuery,
    future: JQuery,
}

export type ControlBarData = {
    execution: ExecutionToolbar,
    beeperInput: JQuery,
    beeperCollapse:JQuery
    infiniteBeeperInput: JQuery,
    delayInput: JQuery,
    delayAdd: JQuery,
    delayRemove: JQuery,
}

export class ControlBar  {
    ui:ControlBarData
    worldController: WorldViewController;
    isControlInPlayMode:boolean;

    constructor (ui:ControlBarData, worldController:WorldViewController) {
        this.ui = ui;
        this.worldController = worldController;
        this.isControlInPlayMode = false;
        
    }

    Init() {
        KarelController.GetInstance().RegisterStateChangeObserver(this.OnKarelControllerStateChange.bind(this));
        WorldViewController.GetInstance().RegisterBeeperBagListener(()=>{
            this.UpdateBeeperBag();
        });
        AppVars.registerDelayChangeListener(this.OnDelayChange.bind(this));

        this.ConnectExecutionButtonGroup();

    }
    private ConnectExecutionButtonGroup() {
        const exec = this.ui.execution;
        const controller = KarelController.GetInstance();
        exec.compile.on("click", ()=>controller.Compile());
        exec.reset.on("click", ()=>this.ResetExecution());
        exec.step.on("click", ()=>this.Step());
        exec.stepOver.on("click", ()=>this.StepOver());
        exec.stepOut.on("click", ()=>this.StepOut());
        exec.future.on("click", ()=> this.RunTillEnd());
        exec.run.on("click", ()=> {
            if (!this.isControlInPlayMode) {
                this.AutoStep();
            } else {
                this.PauseStep();
            }            
        });
        this.ui.delayInput.on("change", () => {
            let delay:number = parseInt(this.ui.delayInput.val() as string);
            if (delay >= MAX_DELAY) {
                delay = MAX_DELAY;
            }
            AppVars.setDelay(delay);
            KarelController.GetInstance().ChangeAutoStepDelay(delay);
        });

        this.ui.delayAdd.on("click", ()=>{            
            let delay:number = parseInt(this.ui.delayInput.val() as string);
            delay += 50;
            this.ui.delayInput.val(delay);
            this.ui.delayInput.trigger("change");
        });
        this.ui.delayRemove.on("click", ()=>{            
            let delay:number = parseInt(this.ui.delayInput.val() as string);
            delay -= 50;
            delay = delay < 0 ? 0:delay;
            this.ui.delayInput.val(delay);            
            this.ui.delayInput.trigger("change");
        });
        this.ui.beeperInput.on("change", () => this.OnBeeperInputChange());
        this.ui.infiniteBeeperInput.on("click", () => this.ToggleInfiniteBeepers());
        KarelController.GetInstance().RegisterStepController((_ctr, _state)=> {this.UpdateBeeperBag()})
        KarelController.GetInstance().RegisterNewWorldObserver((_ctr, _state, _newInstance)=> {this.UpdateBeeperBag()})
    }

    private AutoStep() {
        let delay:number = AppVars.getDelay();
        if(KarelController.GetInstance().StartAutoStep(delay))
            this.SetPlayMode();
    }

    
    private PauseStep() {
        KarelController.GetInstance().Pause();
    }

    private RunTillEnd() {
        KarelController.GetInstance().RunTillEnd();        
        this.UpdateBeeperBag();
    }
    private ResetExecution() {
        KarelController.GetInstance().Reset();
        this.UpdateBeeperBag();
    }

    private Step() {
        KarelController.GetInstance().Step();
        this.UpdateBeeperBag();
    }
    private StepOver() {
        KarelController.GetInstance().StepOver();
        this.UpdateBeeperBag();
    }
    
    private StepOut() {
        KarelController.GetInstance().StepOut();
        this.UpdateBeeperBag();
    }

    private OnBeeperInputChange() {
        if (KarelController.GetInstance().GetState() !== "unstarted") {
            return;
        }
        let beeperAmmount = parseInt(this.ui.beeperInput.val() as string);
        this.worldController.SetBeepersInBag(beeperAmmount);
    }

    private ToggleInfiniteBeepers() {
        if (!KarelNumbers.isInfinite(this.worldController.GetBeepersInBag())) {
            this.ActivateInfiniteBeepers();
            this.worldController.SetBeepersInBag(KarelNumbers.a_infinite);
        } else {
            this.DeactivateInfiniteBeepers();
            this.worldController.SetBeepersInBag(0);
            this.UpdateBeeperBag();

        }
    }

    private UpdateBeeperBag() {
        const amount = this.worldController.GetBeepersInBag()
        
        this.ui.beeperInput.val(amount);
        if (KarelNumbers.isInfinite(amount)) {
            this.ActivateInfiniteBeepers();
        } else {
            this.DeactivateInfiniteBeepers();
        }
    }

    private ActivateInfiniteBeepers() {
        bootstrap.Collapse.getOrCreateInstance(this.ui.beeperCollapse[0], {toggle:true}).hide();
        this.ui.infiniteBeeperInput.removeClass("btn-body");
        this.ui.infiniteBeeperInput.addClass("btn-info");
    }

    
    private DeactivateInfiniteBeepers() {
        bootstrap.Collapse.getOrCreateInstance(this.ui.beeperCollapse[0], {toggle:false}).show();
        this.ui.infiniteBeeperInput.removeClass("btn-info");
        this.ui.infiniteBeeperInput.addClass("btn-body");
    }

    private SetPlayMode() {
        this.isControlInPlayMode = true;

        this.ui.execution.compile.attr("disabled", "");
        this.ui.execution.step.attr("disabled", "");
        this.ui.execution.stepOver.attr("disabled", "");
        this.ui.execution.stepOut.attr("disabled", "");
        this.ui.execution.future.attr("disabled", "");
        this.ui.beeperInput.attr("disabled", "");
        this.ui.infiniteBeeperInput.attr("disabled", "");

        
        this.ui.execution.run.html('<i class="bi bi-pause-fill"></i>');
    }

    
    private SetPauseMode() {
        this.isControlInPlayMode = false;

        this.ui.execution.compile.attr("disabled", "");
        this.ui.beeperInput.attr("disabled", "");
        this.ui.infiniteBeeperInput.attr("disabled", "");

        this.ui.execution.step.removeAttr("disabled");
        this.ui.execution.stepOver.removeAttr("disabled");
        this.ui.execution.stepOut.removeAttr("disabled");
        this.ui.execution.future.removeAttr("disabled");
        this.ui.execution.run.removeAttr("disabled");
        
        this.ui.execution.run.html('<i class="bi bi-play-fill"></i>');
    }

    private DisableControlBar() {
        this.ui.execution.compile.attr("disabled", "");
        this.ui.execution.run.attr("disabled", "");
        this.ui.execution.step.attr("disabled", "");
        this.ui.execution.stepOver.attr("disabled", "");
        this.ui.execution.stepOut.attr("disabled", "");
        this.ui.execution.future.attr("disabled", "");
        this.ui.beeperInput.attr("disabled", "");
        this.ui.infiniteBeeperInput.attr("disabled", "");
    }

    private EnableControlBar() {
        this.ui.execution.compile.removeAttr("disabled");
        this.ui.execution.run.removeAttr("disabled");
        this.ui.execution.step.removeAttr("disabled");
        this.ui.execution.stepOver.removeAttr("disabled");
        this.ui.execution.stepOut.removeAttr("disabled");
        this.ui.execution.future.removeAttr("disabled");
        this.ui.beeperInput.removeAttr("disabled");
        this.ui.infiniteBeeperInput.removeAttr("disabled");

        
        this.ui.execution.run.html('<i class="bi bi-play-fill"></i>');
    }

    private OnKarelControllerStateChange(sender: KarelController, state: ControllerState) {
        if (state === "running") {            
            this.SetPauseMode();
        }
        if (state === "finished") {
            this.isControlInPlayMode = false;
            this.DisableControlBar();
            

        } else if (state === "unstarted") {            
            this.isControlInPlayMode = false;

            this.EnableControlBar();

            this.worldController.NormalMode();
            this.UpdateBeeperBag();
        } else if (state === "paused") {
            this.SetPauseMode();
        }
    }

    private OnDelayChange(delay:number) {
        this.ui.delayInput.val(delay);
        if (delay >= MAX_DELAY) {
            this.ui.delayAdd.attr("disabled","");
            this.ui.delayRemove.removeAttr("disabled");
        } else if (delay === 0) {
            this.ui.delayAdd.removeAttr("disabled");
            this.ui.delayRemove.attr("disabled","");
        } else {
            this.ui.delayAdd.removeAttr("disabled");
            this.ui.delayRemove.removeAttr("disabled");
        }
    }



}