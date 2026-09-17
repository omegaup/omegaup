import { DumpTypes } from "@rekarel/core";
import { KarelController } from "../KarelController";


export type EvaluatorData = {
    modal: JQuery,
    form:JQuery,
    evaluatePosition: JQuery,
    evaluateOrientation: JQuery,
    evaluateBag: JQuery,
    evaluateUniverse: JQuery,

    
    countMoves: JQuery,
    countTurns: JQuery,
    countPicks: JQuery,
    countPuts: JQuery,

    maxInstructions:JQuery,
    maxStackSize:JQuery,
    stackMemory: JQuery,
    maxCallParam: JQuery,
    maxMove:JQuery,
    maxTurnLeft:JQuery,
    maxPickBuzzer:JQuery,
    maxLeaveBuzzer:JQuery,

    targetVersion: JQuery

}


function getData(ui:EvaluatorData) {
    const kcInstance = KarelController.GetInstance();
    ui.evaluatePosition.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_POSITION));
    ui.evaluateOrientation.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_ORIENTATION));
    ui.evaluateBag.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_BAG));
    ui.evaluateUniverse.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_ALL_BUZZERS));

    
    ui.countMoves.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_MOVE));
    ui.countTurns.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_LEFT));
    ui.countPicks.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_PICK_BUZZER));
    ui.countPuts.prop("checked", kcInstance.world.getDumps(DumpTypes.DUMP_LEAVE_BUZZER));

    ui.maxInstructions.val(kcInstance.world.maxInstructions);
    ui.maxStackSize.val(kcInstance.world.maxStackSize);
    ui.stackMemory.val(kcInstance.world.maxStackMemory);
    ui.maxCallParam.val(kcInstance.world.maxCallSize);
    ui.maxMove.val(kcInstance.world.maxMove);
    ui.maxTurnLeft.val(kcInstance.world.maxTurnLeft);
    ui.maxPickBuzzer.val(kcInstance.world.maxPickBuzzer);
    ui.maxLeaveBuzzer.val(kcInstance.world.maxLeaveBuzzer);

    ui.targetVersion.val(kcInstance.world.targetVersion);    
}

function setData(ui: EvaluatorData) {
    const kcInstance = KarelController.GetInstance();
    kcInstance.world.setDumps(DumpTypes.DUMP_POSITION, ui.evaluatePosition.prop("checked"));
    kcInstance.world.setDumps(DumpTypes.DUMP_ORIENTATION, ui.evaluateOrientation.prop("checked"));
    kcInstance.world.setDumps(DumpTypes.DUMP_BAG, ui.evaluateBag.prop("checked"));
    kcInstance.world.setDumps(DumpTypes.DUMP_ALL_BUZZERS, ui.evaluateUniverse.prop("checked"));

    kcInstance.world.setDumps(DumpTypes.DUMP_MOVE, ui.countMoves.prop("checked"));
    kcInstance.world.setDumps(DumpTypes.DUMP_LEFT, ui.countTurns.prop("checked"));
    kcInstance.world.setDumps(DumpTypes.DUMP_PICK_BUZZER, ui.countPicks.prop("checked"));
    kcInstance.world.setDumps(DumpTypes.DUMP_LEAVE_BUZZER, ui.countPuts.prop("checked"));

    
    function validateMax(inputVal:any, min:number=1) {
        const isInteger = Number.isInteger(Number(inputVal));
        const isValid = isInteger && Number(inputVal) >= min;
        return isValid;
    }
    const maxInstructions = parseInt(`${ui.maxInstructions.val()}`);
    const maxStackSize = parseInt(`${ui.maxStackSize.val()}`);
    const maxMove = parseInt(`${ui.maxMove.val()}`);
    const maxTurnLeft = parseInt(`${ui.maxTurnLeft.val()}`);
    const maxPickBuzzer = parseInt(`${ui.maxPickBuzzer.val()}`);
    const maxLeaveBuzzer = parseInt(`${ui.maxLeaveBuzzer.val()}`);
    const stackMemory = parseInt(`${ui.stackMemory.val()}`);
    const maxCallParam = parseInt(`${ui.maxCallParam.val()}`);
    if (validateMax(maxInstructions))
        kcInstance.world.maxInstructions = maxInstructions as number;    
    if (validateMax(maxStackSize))
        kcInstance.world.maxStackSize = maxStackSize as number;
    if (validateMax(stackMemory))
        kcInstance.world.maxStackMemory = stackMemory as number;    
    if (validateMax(maxCallParam))
        kcInstance.world.maxCallSize = maxCallParam as number;
    if (validateMax(maxMove,-1))
        kcInstance.world.maxMove = maxMove as number;    
    if (validateMax(maxTurnLeft,-1))
        kcInstance.world.maxTurnLeft = maxTurnLeft as number;
    if (validateMax(maxPickBuzzer,-1))
        kcInstance.world.maxPickBuzzer = maxPickBuzzer as number;    
    if (validateMax(maxLeaveBuzzer,-1))
        kcInstance.world.maxLeaveBuzzer = maxLeaveBuzzer as number;
    console.log(ui.targetVersion.val())
    kcInstance.world.targetVersion = ui.targetVersion.val() as "1.0"|"1.1";
}


export function HookEvaluatorModal(ui:EvaluatorData) {
    ui.modal.on("show.bs.modal",()=>getData(ui)); 
    ui.form.on("submit",(e)=>{
        e.preventDefault();
        setData(ui);
    }); 
}