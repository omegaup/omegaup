import bootstrap from 'bootstrap'

import type { UiData} from "../common-ui"

export type ConfirmModal = {
    modal: string,
    confirmBtn: string,
    rejectBtn: string,
    titleField: string,
    messageField: string
}

export type ConfirmData = {
    title:string,
    message: string,
    accept: ()=>void,
    reject: ()=>void,
}

export interface ConfirmPromptArgs {
    modalData: ConfirmData,
    uiData: UiData,
}


export type ConfirmModalBtn = {
    button: string,
    data: ConfirmData,
}
export function confirmPrompt(evenet) {
    let data: ConfirmPromptArgs = evenet.data
    
    $(data.uiData.confirmModal.titleField).html(data.modalData.title);
    $(data.uiData.confirmModal.messageField).html(data.modalData.message);

    $(data.uiData.confirmModal.confirmBtn).on(
        "click",
        data.modalData.accept
    );
    $(data.uiData.confirmModal.rejectBtn).on(
        "click",
        data.modalData.reject
    );

    let modal = bootstrap.Modal.getOrCreateInstance($(data.uiData.confirmModal.modal).get(0)); 
    modal.show();
}


export function confirmPromptEnd(event) {
    let data: ConfirmPromptArgs = event.data

    $(data.uiData.confirmModal.confirmBtn).off(
        "click",
        data.modalData.accept
    )

    $(data.uiData.confirmModal.rejectBtn).off(
        "click",
        data.modalData.reject
    )
}

