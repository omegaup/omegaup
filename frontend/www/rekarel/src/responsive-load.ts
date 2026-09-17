import { DesktopController } from "./desktop/desktop-ui";
import { getEditors } from "./editor/editorsInstances";


let mode:"mobile"|"desktop"|"responsive"="responsive";

function clearAllDisplayClasses(element: string) {    
    $(element).removeClass( "d-none" );
    $(element).removeClass( "d-lg-block");
    $(element).removeClass( "d-lg-flex");
    $(element).removeClass( "d-lg-none");
}
function hideElement(element: string) {    
    $(element).addClass( "d-none" );
}
function SetResponsiveness() {
    mode = "responsive";
    
    clearAllDisplayClasses("#desktopView");    
    clearAllDisplayClasses("#phoneView");

    $("#phoneView").addClass( "d-lg-none" );
    $("#desktopView").addClass( "d-none" );
    $("#desktopView").addClass( "d-lg-flex");
    setTimeout(()=>checkVisibility());
}

function SetDesktopView() {
    mode = "desktop";
    clearAllDisplayClasses("#phoneView");
    clearAllDisplayClasses("#desktopView");
    hideElement("#phoneView");
    MovePanels("desktop");
    previousResponsiveMode = "desktop";
    
}
function SetPhoneView() {
    mode = "mobile";
    clearAllDisplayClasses("#phoneView");
    clearAllDisplayClasses("#desktopView");
    hideElement("#desktopView");
    MovePanels("mobile");
    previousResponsiveMode = "mobile";

}

const statePanel = $("#stateConsole");
const worldPane = $("#worldPane");
const worldContainer = $("#worldContainer");

function MovePanels(target:"mobile"|"desktop") {
    const editor = getEditors()[0];
    const dom = $(editor.dom);
    dom.detach();
    statePanel.detach();
    worldPane.detach();

    if (target === "mobile") {
        $("#mobileCodePanel").append(dom);
        $("#mobileWorldPane").prepend(worldPane);
        $("#mobileStatePanel").append(statePanel);
    } else {
        $("#splitter-left-top-pane").append(dom);
        $("#splitter-left-bottom-pane").append(statePanel);
        $("#desktopWorldSlot").prepend(worldPane);
    }
    DesktopController.GetInstance().ResizeCanvas();
    DesktopController.GetInstance().worldController.FocusKarel();
}

let previousResponsiveMode:"desktop"|"mobile" = "desktop";
let phoneView =$("#phoneView");
let desktopView =$("#desktopView");
function checkVisibility() {
    if (mode !== "responsive") 
        return;
    if (previousResponsiveMode === "desktop") {
        if (phoneView.css("display")!=="none") {
            previousResponsiveMode = "mobile";
            MovePanels("mobile");
        }
        return;
    }
    
    if (previousResponsiveMode === "mobile") {
        if (desktopView.css("display")!=="none") {
            previousResponsiveMode = "desktop";
            MovePanels("desktop");
        }
        return;
    }

}
function responsiveEvent() {
    $(window).on('resize', checkVisibility);

    setTimeout(()=>checkVisibility());
}

export {responsiveEvent as responsiveHack, SetResponsiveness, SetDesktopView, SetPhoneView}