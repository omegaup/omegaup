import { KarelConsole } from "../desktop/console";
import { KarelController } from "../KarelController";

export type OpenWorldModal = {
    modal: string,
    fileBtn: string,
    worldText: string,
    useTextBtn: string
}


function parseWorld(xml: string) {
    // Parses the xml and returns a document object.
    return new DOMParser().parseFromString(xml, 'text/xml');
}



function getWorldIn(karelController: KarelController) {
    var file = document.createElement('input');
    file.type = 'file';
    file.accept = '.in';
    file.addEventListener('change', function (evt) {
        //@ts-ignore
        var files = evt.target.files; // FileList object

        // Loop through the FileList and render image files as thumbnails.
        for (var i = 0, f; (f = files[i]); i++) {
            // Only process text files.
            if (f.type && !f.type.match('text.*')) {
                continue;
            }

            var reader = new FileReader();

            // Closure to capture the file information.
            reader.onload = (function (theFile) {
                return function (e) {
                    const result = reader.result as string
                    karelController.LoadWorld(parseWorld(result));
                    karelController.Reset()
                };
            })(f);

            // Read in the file as a data URL.
            reader.readAsText(f);
        }
    });
    file.click();
}

function openWorldFromTextArea(modal:OpenWorldModal, controller:KarelController) {
    const source = <string>$(modal.worldText).val();
    try {
        controller.LoadWorld(parseWorld(source));
        
    } catch(err) {
        console.error(err);
    }
}

export function hookOpenWorldModel(modal: OpenWorldModal, controller:KarelController) {
    $(modal.fileBtn).on("click", () => getWorldIn(controller));
    $(modal.useTextBtn).on("click", () => openWorldFromTextArea(modal, controller));
}