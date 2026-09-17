import { DefaultWRStyle } from "./KarelStyles";
import { DesktopController } from "./desktop/desktop-ui";
import { responsiveHack, SetResponsiveness, SetDesktopView, SetPhoneView } from "./responsive-load";
import { SetDarkTheme, SetLightTheme, SetSystemTheme } from "./appTheme";
import {  WRStyle } from "./worldRenderer";
import { DarkEditorThemes } from "./editor/themes/themeManager";
import { KarelController } from "./KarelController";

export const APP_SETTING = 'appSettings';
export const SETTINGS_VERSION = "0.7.0";

export type fontSizes = number;
export type responsiveInterfaces = "auto" | "desktop" | "mobile";
export type themeSettings = "system" | "light" | "dark";

export type AppSettings = {
    version:string,
    interface: responsiveInterfaces,
    autoInputMode: boolean
    theme: themeSettings
    editorTheme:string
    editorFontSize: fontSizes,
    /**
     * Sets if the brackets are automatically closed when typed
     */
    editorCloseBrackets:boolean,
    worldRendererStyle: WRStyle,
    slowExecutionLimit:number,
    
}

export const defaultSettings: AppSettings = {
    version:SETTINGS_VERSION,
    interface: "desktop",
    autoInputMode: true,
    editorTheme: "classic",
    editorFontSize: 12,
    editorCloseBrackets:true,
    theme: "system",
    worldRendererStyle: DefaultWRStyle,
    slowExecutionLimit:200000
}
let appSettings: AppSettings = {...defaultSettings}

/**
 * Upgrades old settings to the current format, adding new settings
 * @param oldSettings 
 */
export function upgradeSettings(oldSettings:Partial<AppSettings>):AppSettings {
    return {
        ...defaultSettings,
        ...oldSettings,
    };
}

export function SetSettings(settings:AppSettings) {
    appSettings = settings;
}

export function GetCurrentSetting(){ return appSettings;}