import { WorldViewController } from "../worldViewController/worldViewController";

export type AmountModal = {
    modal:string,
    confirmBtn:string,
    inputField:string,
}




export function HookAmountModal(modal: AmountModal, worldController:WorldViewController) {
    $(modal.modal).on("shown.bs.modal", () => {
        $(modal.inputField)
            .trigger("focus")
            .val("");
    })
    $(modal.inputField).on("keydown", (event) => {
        if (event.key === "Enter") {
            $(modal.confirmBtn).trigger("click");
        }
    })
    $(modal.confirmBtn).on("click", ()=> {
        const inputValue = $(modal.inputField).val();
        if (inputValue === "") {
            return;
        }
        const amount = parseFloat(inputValue as string);
        if (amount < 0) {
            return;
        }
        worldController.SetBeepers(amount);
    })
}

