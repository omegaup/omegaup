import { EditorView } from "codemirror";

export type DownloadCodeModal = {
    modal:string,
    confirmBtn:string,
    inputField:string,
    wrongCodeWarning: string
}


const fileRegex = /^[a-zA-Z0-9._]+$/;
function setFileNameLink(modal: DownloadCodeModal, editor:EditorView, changeInput:boolean=false) {
    let newFilename: string = <string>$(modal.inputField).val(); 
    let extension:string = "txt";
    if (sessionStorage?.getItem("rekarel:lang") === "java") {
        extension = "kj";
    } else {
        extension = "kp";
    }
       
    if (!fileRegex.test(newFilename)) {
        newFilename=`code.${extension}`;
        if (changeInput) {
            $(modal.inputField).val(newFilename);
        } else {
            $(modal.wrongCodeWarning).removeAttr("hidden");
        }
    } else {
        
        $(modal.wrongCodeWarning).attr("hidden", "");
    }
    
    let blob = new Blob([editor.state.doc.toString()], { type: 'text/plain'});
    $(modal.confirmBtn).attr("href", window.URL.createObjectURL(blob));
    $(modal.confirmBtn).attr("download", newFilename);
}

function openModal(modal:DownloadCodeModal, editor:EditorView) {
    let extension:string = "txt";
    if (sessionStorage?.getItem("rekarel:lang") === "java") {
        extension = "kj";
    } else {
        extension = "kp";
    }
    let newFilename: string = <string>$(modal.inputField).val(); 
    if (newFilename.endsWith('.kj') || newFilename.endsWith('.kp')) {
        newFilename = newFilename.slice(0, -2) + extension;
    }
    $(modal.inputField).val(newFilename);


    setFileNameLink(modal, editor, true);
}

export function hookDownloadModel(modal:DownloadCodeModal, editor:EditorView) {
    $(modal.modal).on('show.bs.modal', ()=>openModal(modal, editor));
    $(modal.inputField).change(()=>setFileNameLink(modal, editor));
}