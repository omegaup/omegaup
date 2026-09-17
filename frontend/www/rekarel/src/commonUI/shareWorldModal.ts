import { KarelBinarySerializer } from "@rekarel/binary";
import { KarelController } from "../KarelController";
import { arrayBufferToBase64 } from "../base64";

export interface ShareWorldModal {
    modal: string,
    toClipboard: string
    field: string
    clipboardNotice: string
    tooLarge:string
}


export function HookShareWorldModal(data: ShareWorldModal, controller:KarelController) {
    $(data.modal).on("show.bs.modal", () => {        
        $(data.clipboardNotice).attr("hidden","")
        $(data.tooLarge).attr("hidden","")

        const world = controller.world;
        const serializer = new KarelBinarySerializer();
        const binary = serializer.serialize(world);
        const base64 = arrayBufferToBase64(binary);
        const baseUrl = window.location.origin + window.location.pathname;
        const fullUrl = `${baseUrl}#w=${base64}`;
        $(data.field).val(fullUrl);
        if (fullUrl.length > 1995) {
            $(data.tooLarge).removeAttr("hidden")

        }

    })

    $(data.toClipboard).on("click", () => {
        // Get the value from the input or field
        const textToCopy = `${$(data.field).val()}`;
    
        // Use the Clipboard API to copy the text
        navigator.clipboard.writeText(textToCopy)
            .then(() => {
                $(data.clipboardNotice).removeAttr("hidden")
            })
            .catch(err => {
                console.error("Failed to copy text to clipboard:", err);
            });
    });
}


