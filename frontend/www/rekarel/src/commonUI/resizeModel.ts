import { KarelController } from "../KarelController"

export type ResizeModal = {
    modal: string,
    confirmBtn: string,
    rowField: string,
    columnField: string,
    errorField: string,
}


export function HookResizeModal(resizeModel: ResizeModal, karelController: KarelController) {
    $(resizeModel.modal).on('show.bs.modal', ()=> {
        $(resizeModel.rowField).val(karelController.world.h)
        $(resizeModel.columnField).val(karelController.world.w)
        $(resizeModel.errorField).attr("hidden", "")
    })

    $(resizeModel.confirmBtn).on('click', () => {
        let w = parseInt($(resizeModel.columnField).val() as string);
        let h = parseInt($(resizeModel.rowField).val() as string);
        if (Number.isInteger(w) && Number.isInteger(h) && w > 0 && h > 0 && w <= 4e6 && h <=4e6 && w*h <= 4e6) {
            karelController.Resize(w, h);
            karelController.Reset();
        } else {
            $(resizeModel.errorField).removeAttr("hidden")
        }
        
    })

    $(`${resizeModel.columnField},${resizeModel.rowField}`).on("click",()=> {        
        let w = parseInt($(resizeModel.columnField).val() as string);
        let h = parseInt($(resizeModel.rowField).val() as string);
        if (w > 0 && h > 0 && w <= 4e6 && h <=4e6 && w*h <= 4e6) {
            $(resizeModel.errorField).attr("hidden", "");
        } else {
            $(resizeModel.errorField).removeAttr("hidden");

        }
    })
}