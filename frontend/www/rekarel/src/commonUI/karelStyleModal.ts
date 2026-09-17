import { DefaultWRStyle, WR_CLEAN, WR_CONTRAST, WR_DARK } from "../KarelStyles";
import { GetCurrentSetting } from "../settings";
import { SetWorldRendererStyle } from "../settingsLoader";
import { WRStyle, isWRStyle } from "../worldRenderer";
import { WorldViewController } from "../worldViewController/worldViewController";

function parseFormData(): WRStyle {
    return {
        disabled: $('#disabledColor').val() as string,
        exportCellBackground: $('#exportCellBackgroundColor').val() as string,
        karelColor: $('#karelColor').val() as string,
        gridBackgroundColor: $('#gridBackgroundColor').val() as string,
        errorGridBackgroundColor: $('#errorGridBackgroundColor').val() as string,
        gridBorderColor: $('#gridBorderColor').val() as string,
        errorGridBorderColor: $('#errorGridBorderColor').val() as string,
        gutterBackgroundColor: $('#gutterBackgroundColor').val() as string,
        gutterColor: $('#gutterColor').val() as string,
        beeperBackgroundColor: $('#beeperBackgroundColor').val() as string,
        beeperColor: $('#beeperColor').val() as string,
        wallColor: $('#wallColor').val() as string,
        waffleColor: $('#waffleColor').val() as string,
        gutterSelectionBackgroundColor: $('#gutterSelectionBackgroundColor').val() as string,
        gutterSelectionColor: $('#gutterSelectionColor').val() as string,
    };
}

function setFormData(style: WRStyle): void {
    $('#disabledColor').val(style.disabled);
    $('#exportCellBackgroundColor').val(style.exportCellBackground);
    $('#karelColor').val(style.karelColor);
    $('#gridBackgroundColor').val(style.gridBackgroundColor);
    $('#errorGridBackgroundColor').val(style.errorGridBackgroundColor);
    $('#gridBorderColor').val(style.gridBorderColor);
    $('#errorGridBorderColor').val(style.errorGridBorderColor);
    $('#gutterBackgroundColor').val(style.gutterBackgroundColor);
    $('#gutterColor').val(style.gutterColor);
    $('#beeperBackgroundColor').val(style.beeperBackgroundColor);
    $('#beeperColor').val(style.beeperColor);
    $('#wallColor').val(style.wallColor);
    $('#waffleColor').val(style.waffleColor);
    $('#gutterSelectionBackgroundColor').val(style.gutterSelectionBackgroundColor);
    $('#gutterSelectionColor').val(style.gutterSelectionColor);
}


function loadPreset() {
    const val = $("#karelStylePreset").val();
    if (val==="current") {
        setFormData(GetCurrentSetting().worldRendererStyle);
        return;
    }
    if (val==="default") {
        setFormData(DefaultWRStyle);
        return;
    }
    if (val==="clean") {
        setFormData(WR_CLEAN);
        return;
    }
    if (val==="dark") {
        setFormData(WR_DARK);
        return;
    }
    if (val==="contrast") {
        setFormData(WR_CONTRAST);
        return;
    }
    
}

function exportStyle() {
    const style = GetCurrentSetting().worldRendererStyle;
    const val = JSON.stringify(style);
    $("#karelStyleJSON").val(val);
}


function importStyle() {
    const val = $("#karelStyleJSON").val() as string;
    try {
        const style = JSON.parse(val);
    
        if (!isWRStyle(style)) {
           return;
        }

        setFormData(style);

    } catch (error) {
        return;
    }

}


export function HookStyleModal(view:WorldViewController) {

    $("#setKarelStyleBtn").on("click",()=> {
        const style = parseFormData();
        console.log(style);
        view.renderer.style = style;
        view.Update();
        SetWorldRendererStyle(style);
    })
    $("#karelStyleModal").on("show.bs.modal",()=> {
        setFormData(view.renderer.style);
    })
    $("#karelStyleLoadPresetBtn").on("click",()=> {
        loadPreset();
    })
    $("#karelStyleExport").on("click",()=> {
        exportStyle();
    });
    $("#karelStyleImport").on("click",()=> {
        importStyle();
    });
}