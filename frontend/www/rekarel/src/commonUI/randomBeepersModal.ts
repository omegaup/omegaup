import { AppVars } from "../volatileMemo"

// Fixme
export function HookRandomBeepersModal() {
    $("#randomBeepersModal").on("show.bs.modal", ()=>{
        $("#minimumRandomBeepers").val(AppVars.randomBeeperMinimum)
        $("#maximumRandomBeepers").val(AppVars.randomBeeperMaximum)
    })


    $("#randomBeepersForm").on("submit",(e) => {
        e.preventDefault();
        const minValue = parseInt(`${ $("#minimumRandomBeepers").val()}`);
        const maxValue =  parseInt(`${ $("#maximumRandomBeepers").val()}`);
        if (!isNaN(minValue)) {
            AppVars.randomBeeperMinimum =  Number(minValue);
        }
        if (!isNaN(maxValue)) {
            AppVars.randomBeeperMaximum = Number(maxValue);
        }

    })
}