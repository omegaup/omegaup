import bootstrap from 'bootstrap'
import {EditorView} from "@codemirror/view"
import { ConfirmData, ConfirmModal, ConfirmPromptArgs, confirmPrompt, confirmPromptEnd, ConfirmModalBtn } from './commonUI/confirmPrompt'
import { DownloadCodeModal,hookDownloadModel } from './commonUI/downloadCodeModel'
import { HookResizeModal, ResizeModal } from './commonUI/resizeModel'
import { KarelController } from './KarelController'
import { AmountModal, HookAmountModal } from './commonUI/amountModal'
import { WorldViewController } from './worldViewController/worldViewController'
import { HookWorldSaveModal, WorldSaveModal } from './commonUI/worldSaveModal'
import { HookNavbar, NavbarData } from './commonUI/navbar'
import { HookStyleModal } from './commonUI/karelStyleModal'
import { EvaluatorData, HookEvaluatorModal } from './commonUI/evaluatorModal'
import { HookRandomBeepersModal } from './commonUI/randomBeepersModal'
import { hookOpenWorldModel, OpenWorldModal } from './commonUI/openWorldModal'
import { HookShareWorldModal, ShareWorldModal } from './commonUI/shareWorldModal'
import { HookMdoModal, MdoModal } from './commonUI/mdoModal'



interface UiData {
    editor: EditorView,
    downloadCodeModal: DownloadCodeModal,
    resizeModal:ResizeModal,
    confirmModal: ConfirmModal,
    wordSaveModal:WorldSaveModal,
    openWorldModal: OpenWorldModal,
    amountModal: AmountModal,
    evaluatorModal:EvaluatorData
    confirmCallers: Array<ConfirmModalBtn>,
    karelController:KarelController,
    worldController: WorldViewController,
    worldShareModal: ShareWorldModal,
    mdoModal: MdoModal,
    navbar:NavbarData
    
}



function HookUpCommonUI(uiData: UiData) {
    hookOpenWorldModel(uiData.openWorldModal, uiData.karelController);
    hookDownloadModel(uiData.downloadCodeModal, uiData.editor);
    HookAmountModal(uiData.amountModal, uiData.worldController);
    HookWorldSaveModal(uiData.wordSaveModal, uiData.karelController);
    HookNavbar(uiData.navbar, uiData.editor, uiData.karelController);
    HookStyleModal(uiData.worldController);
    HookEvaluatorModal(uiData.evaluatorModal);
    HookRandomBeepersModal();
    //Hook ConfirmCallers
    uiData.confirmCallers.forEach((confirmCaller)=> {
        let confirmArgs: ConfirmPromptArgs = {            
                uiData: uiData,
                modalData: confirmCaller.data,            
        };
        $(confirmCaller.button).on(
            'click',
            null,
            confirmArgs,
            confirmPrompt,
            
            );        
        $(uiData.confirmModal.modal).on(
            'hidden.bs.modal',
            null,
            confirmArgs,
            confirmPromptEnd,
            )
    });

    HookResizeModal(uiData.resizeModal, uiData.karelController);
    HookShareWorldModal(uiData.worldShareModal, uiData.karelController)
    HookMdoModal(uiData.mdoModal, uiData.karelController);
}



export {HookUpCommonUI, UiData};