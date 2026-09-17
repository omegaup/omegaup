import { applyTheme } from "./editor/themes/editorTheme";
import { DarkEditorThemes, LightEditorThemes } from "./editor/themes/themeManager";


export function SetLightTheme (theme:string) {
    $(":root").attr("data-bs-theme", "light");    
    applyTheme(LightEditorThemes[theme]);
}

export function SetDarkTheme (theme:string) {
    $(":root").attr("data-bs-theme", "dark");    
    applyTheme(DarkEditorThemes[theme]);
}

export function SetSystemTheme (theme:string) {
    if (window.matchMedia) {
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            SetDarkTheme(theme);
        } else {
            SetLightTheme(theme);
        }
        return;
    }
    SetLightTheme(theme); //Default light theme
}