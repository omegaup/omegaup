import { deserializeMdoKec } from "@rekarel/binary";
import { KarelController } from "../KarelController";
import bootstrap from "bootstrap";

export interface MdoModal {
    modal: string,
    mdoFile: string,
    kecFile: string,
    mdoMissing: string,
    mdoError: string,
    importBtn:string
}


export function HookMdoModal(data: MdoModal, controller: KarelController) {
    $(data.modal).on("show.bs.modal", ()=> {
        $(data.mdoMissing).attr("hidden", "")
        $(data.mdoError).attr("hidden", "")
    });

    $(data.importBtn).on("click", async () => {
        const mdoMissing = $(data.mdoMissing)
        const mdoError = $(data.mdoError)
        const mdoFileInput = $(data.mdoFile).prop("files") as FileList;
        const kecFileInput = $(data.kecFile).prop("files") as FileList;
        
        if (!mdoFileInput || mdoFileInput.length === 0) {
            mdoMissing.removeAttr("hidden")
            return;
        }
        mdoMissing.attr("hidden", "")

        const mdoRead = readFileAsUint16Array(mdoFileInput[0])
        const kecRead = kecFileInput && kecFileInput.length > 0 
                        ? readFileAsUint16Array(kecFileInput[0]): Promise.resolve(undefined);
        Promise.all([mdoRead, kecRead]).then(([mdo, kec])=>{            
            const world = controller.world;
            controller.Reset();
            deserializeMdoKec(world, mdo, kec);
            controller.Reset();
            bootstrap.Modal.getOrCreateInstance($(data.modal)[0]).hide();                
            mdoError.attr("hidden", "")
        }).catch((e)=>{
            console.error(e);
            mdoError.removeAttr("hidden")
            //TODO: Show this
        });

    });
}

// Helper function to read a file as Uint16Array
function readFileAsUint16Array(file: File): Promise<Uint16Array> {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => {
            if (reader.result) {
                const arrayBuffer = reader.result as ArrayBuffer;
                if ((arrayBuffer.byteLength&1)!==0) {
                    reject(new Error("File byte length is odd when it needs to be even"))
                } else {
                    resolve(new Uint16Array(arrayBuffer));
                }
            } else {
                reject(new Error("Failed to read file"));
            }
        };
        reader.onerror = () => reject(reader.error);
        reader.readAsArrayBuffer(file);
    });
}

