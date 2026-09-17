import { SetDarkTheme, SetLightTheme, SetSystemTheme } from "./appTheme";
import { DesktopController } from "./desktop/desktop-ui";
import { SetAutoCloseBracket } from "./editor/editor";
import { DarkEditorThemes } from "./editor/themes/themeManager";
import { SetDesktopView, SetPhoneView, SetResponsiveness } from "./responsive-load";
import { APP_SETTING, AppSettings, fontSizes, GetCurrentSetting, responsiveInterfaces, SetSettings, SETTINGS_VERSION, themeSettings, upgradeSettings } from "./settings";
import { WRStyle } from "./worldRenderer";

function isFontSize(str: number): str is fontSizes {
    return 6 < str && str < 31;
}
function isResponsiveInterfaces(str: string): str is responsiveInterfaces {
    return ["auto", "desktop", "mobile"].indexOf(str) > -1;
}
function isTheme(str: string): str is themeSettings {
    return ["system", "light", "dark"].indexOf(str) > -1;
}
let DesktopUI: DesktopController

function applySettings(settings: AppSettings, desktopUI:DesktopController) {
    SetSettings(settings);
    switch (settings.interface) {
        case "auto":
            SetResponsiveness();
            break;
        case "desktop":
            SetDesktopView();
            break;
        case "mobile":
            SetPhoneView();
            break;
        default:
            SetDesktopView();
            break;
    }
    if (!(settings.editorTheme in DarkEditorThemes)) settings.editorTheme = "classic";
    switch (settings.theme) {
        case "system":
            SetSystemTheme(settings.editorTheme);
            break;
        case "light":
            SetLightTheme(settings.editorTheme);
            break;
        
        case "dark":
            SetDarkTheme(settings.editorTheme);
            break;
        default:            
            SetDarkTheme(settings.editorTheme);
    }
    const root = $(":root")[0];
    root.style.setProperty("--editor-font-size", `${settings.editorFontSize}pt`);
    root.style.setProperty("--waffle-color", `${settings.worldRendererStyle.waffleColor}`);
    if (settings.interface == "desktop")
        desktopUI.ResizeCanvas();
    desktopUI.worldController.renderer.style = settings.worldRendererStyle;
    desktopUI.worldController.Update();
    SetAutoCloseBracket(settings.editorCloseBrackets, desktopUI.editor);
    if (localStorage)
        localStorage.setItem(APP_SETTING, JSON.stringify(settings))

    
}

function setSettings(event:  JQuery.SubmitEvent<HTMLElement, undefined, HTMLElement, HTMLElement>, desktopUI:DesktopController) {
    const appSettings = GetCurrentSetting();
    let interfaceType = <string>$("#settingsForm select[name=interface]").val();
    let fontSize = <number>$("#settingsForm input[name=fontSize]").val();
    let slowModeLimit = <number>$("#settingsSlowModeLimit").val();
    let theme = <string>$("#settingsForm select[name=theme]").val();
    let style = <string>$("#settingsForm select[name=editorStyle]").val();
    let autoInput =<boolean>($("#settingsAutoInputMode").prop("checked") ?? true);
    let autoCloseBracket =<boolean>($("#settingsAutoCloseBracket").prop("checked") ?? true);
    if (isResponsiveInterfaces(interfaceType)) {
        appSettings.interface = interfaceType;
    }
    if (isFontSize(fontSize)) {
        appSettings.editorFontSize = fontSize;
    }
    if (isTheme(theme)) {
        appSettings.theme = theme;
    }
    appSettings.slowExecutionLimit = slowModeLimit;
    appSettings.editorTheme = style;
    appSettings.autoInputMode = autoInput;
    appSettings.editorCloseBrackets = autoCloseBracket;
    applySettings(appSettings, desktopUI);


    event.preventDefault();
    return false;
}

function loadSettingsFromMemory() {
    const jsonString = localStorage?.getItem(APP_SETTING);
    if (jsonString) {
        let memorySettings:AppSettings = JSON.parse(jsonString);
        memorySettings = upgradeSettings(memorySettings);
        memorySettings = fixSettings(memorySettings);
        SetSettings(memorySettings);
    } 
}

function fixSettings(previousSettings:AppSettings): AppSettings {
    const defaultSettings = GetCurrentSetting();
    const updatedSettings: AppSettings = { ... defaultSettings };

    for (const key in defaultSettings) {
        updatedSettings[key] = previousSettings.hasOwnProperty(key)
            ? previousSettings[key]
            : defaultSettings[key];
    }
    updatedSettings.version = defaultSettings.version;
    return updatedSettings;    
}

function loadSettingsToModal() {
    const appSettings = GetCurrentSetting();
    $("#settingsInterface").val(appSettings.interface );
    $("#settingsFontSize").val(appSettings.editorFontSize);
    $("#settingsTheme").val(appSettings.theme);
    $("#settingsStyle").val(appSettings.editorTheme);
    $("#settingsSlowModeLimit").val(appSettings.slowExecutionLimit);
    $("#settingsAutoInputMode").prop("checked",appSettings.autoInputMode);
    $("#settingsAutoCloseBracket").prop("checked",appSettings.editorCloseBrackets);
    showOrHideSlowExecutionLimit();
}

export function InitSettings(desktopUI:DesktopController) {    
    DesktopUI = desktopUI;
    loadSettingsFromMemory();
    $("#settingsModal").on("show.bs.modal", (e)=> {
        loadSettingsToModal();
    });

    $("#settingsForm").on("submit", (e)=> {
        setSettings(e , desktopUI)
    });
    
    
}

function showOrHideSlowExecutionLimit () {
    
    if ($("#settingsSlowModeLimit").val() as number > 200000) {
        $("#slowModeWarning").show();
    } else {
        $("#slowModeWarning").hide();
    }

}

export function StartSettings(desktopUI:DesktopController) {
    const appSettings = GetCurrentSetting();
    applySettings(appSettings, desktopUI);
        
    $(document).on("keydown", (e) => {
        if (e.ctrlKey && e.which === 75) {
            let fontSize = appSettings.editorFontSize;
            fontSize--;
            if (fontSize < 7) fontSize = 7;
            appSettings.editorFontSize = fontSize;
            applySettings(appSettings, desktopUI);
            e.preventDefault();
            return false;
        }
        if (e.ctrlKey && e.which === 76) {
            let fontSize = appSettings.editorFontSize;
            fontSize++;
            if (fontSize > 30) fontSize = 30;
            appSettings.editorFontSize = fontSize;
            applySettings(appSettings, desktopUI);
            e.preventDefault();
            return false;
        }

    });
    $("#settingsSlowModeLimit").on("change", ()=> showOrHideSlowExecutionLimit());
}


export function SetWorldRendererStyle(style :WRStyle) {
    const appSettings = GetCurrentSetting();
    appSettings.worldRendererStyle = style;
    applySettings(appSettings, DesktopUI);
}
