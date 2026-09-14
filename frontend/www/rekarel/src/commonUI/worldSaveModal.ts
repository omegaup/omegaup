import { KarelController } from "../KarelController";

export type WorldSaveModal = {
    worldDataIn:string,
    worldDataOut:string,
    nameField:string,
    modal:string,
    wrongNameWaring:string
    inputBtn:string, 
    outputBtn:string, 
} 

let defaultFileName = "mundo"

function setWorldData(data:string, textBox:string, btn: string, success:boolean) {
    const targetText = $(textBox);
    targetText.val(data);
    let blob = new Blob([data], { type: 'text/plain'});
    const targetBtn = $(btn);
    targetBtn.attr("href", window.URL.createObjectURL(blob));
    if (!success) {
        targetText.addClass("bg-danger-subtle");
        targetBtn.attr("aria-disabled", "true")
            .addClass("btn-secondary") 
            .removeClass("btn-primary") 
            .addClass("disabled");
    }else {        
        targetText.removeClass("bg-danger-subtle");
        targetBtn.removeAttr("aria-disabled")
            .removeClass("btn-secondary") 
            .addClass("btn-primary") 
            .removeClass("disabled");
    }

}

function setInputWorld(modal:WorldSaveModal, karelController: KarelController) {
    const input = karelController.world.save("start");
    setWorldData(input, modal.worldDataIn, modal.inputBtn, true);
}


function setOutputWorld(modal:WorldSaveModal, karelController: KarelController) {
    const filename = $(modal.nameField).val() as string;
    let result = KarelController.GetInstance().Compile(false);
    let output;
    let success=false;
    $(modal.worldDataOut).val("Procesando...");
    if (result == null) {
        output = "ERROR DE COMPILACION";
        setWorldData(output, modal.worldDataOut, modal.outputBtn, success);
    } else {
        
        KarelController.GetInstance().RunTillEnd(false).then(()=> {
            if (KarelController.GetInstance().EndedOnError()) {
                output = "ERROR DE EJECUCION"
            } else {
                output = karelController.world.output();
                success = true;
            }
            setWorldData(output, modal.worldDataOut, modal.outputBtn, success);
        })
    }
}

const fileRegex = /^[a-zA-Z0-9._]+$/;
function setFileNameLink(modal: WorldSaveModal) {
    let newFilename: string = <string>$(modal.nameField).val();    
    if (!fileRegex.test(newFilename)) {
        $(modal.wrongNameWaring).removeAttr("hidden");
        newFilename=defaultFileName;
    } else {
        $(modal.wrongNameWaring).attr("hidden", "");
    }
    
    $(modal.inputBtn)
        .attr("download", newFilename+".in")
        .text("Descargar "+newFilename+".in");
    $(modal.outputBtn)
        .attr("download", newFilename+".out")
        .text("Descargar "+newFilename+".out");
}

export function HookWorldSaveModal(modal:WorldSaveModal, karelController:KarelController) {
    $(modal.modal).on("show.bs.modal", ()=>{
        //Disable buttons until they're enabled after processing the worlds
        $(modal.outputBtn)
            .addClass("disabled") 
            .addClass("btn-secondary") 
            .removeClass("btn-primary") 
            .attr("aria-disabled","true");
        $(modal.inputBtn)
            .addClass("disabled") 
            .addClass("btn-secondary") 
            .removeClass("btn-primary") 
            .attr("aria-disabled","true");
        setFileNameLink(modal);
        setInputWorld(modal,karelController);
        setOutputWorld(modal,karelController);
        
    });
    

    $(modal.nameField).on("change", ()=>{setFileNameLink(modal)});
}