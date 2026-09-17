import { classicHighlight } from "./classicHighlight";
import { DarkCodeTheme, LightCodeTheme } from "./codeTheme";
import { darkClassicHighlight } from "./darkClassicHighlight";
import { OMIHighlight } from "./omiTheme";
import { LightReKarelHighlight, ReKarelHighlight } from "./reKarelTheme";
import { SepiaTheme } from "./sepiaTheme";

export const DarkEditorThemes = {
    'classic': darkClassicHighlight,
    'rekarel': ReKarelHighlight,
    'sepia': SepiaTheme,
    'omi': OMIHighlight,
    'code': DarkCodeTheme,
}
export const LightEditorThemes = {
    'classic': classicHighlight,
    'rekarel': LightReKarelHighlight,
    'sepia': SepiaTheme,
    'omi': OMIHighlight,
    'code': LightCodeTheme, 

}