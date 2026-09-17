export type ConsoleTab = {
    console:JQuery,
    clear: JQuery,
    parent: JQuery,
    consoleMessageCount: JQuery,
    tab: JQuery,
}


export class KarelConsole {
    consoleTab:ConsoleTab
    private unreadMessages;
    private static instance:KarelConsole

    constructor (data:ConsoleTab) {
        this.consoleTab = data;
        this.consoleTab.clear.on("click", ()=> this.ClearConsole());
        this.unreadMessages = 0;
        this.consoleTab.tab.on("show.bs.tab", ()=> {
            this.unreadMessages = 0;
            this.UpdateUnreadPill();
        })
        KarelConsole.instance = this;

    }

    SendMessageToConsole(message: string, style:string) {
        if (!this.IsShown()) {
            this.unreadMessages++;
        } else {
            this.unreadMessages = 0;
        }

        this.UpdateUnreadPill();

        if (style !== "raw") {
            const currentDate = new Date();
            const hour = currentDate.getHours() % 12 || 12;
            const minute = currentDate.getMinutes();
            const second = currentDate.getSeconds();
            const amOrPm = currentDate.getHours() < 12 ? "AM" : "PM";
            
            const html = `<div style="text-wrap: wrap;"><span class="text-${style}">[${hour}:${minute}:${second} ${amOrPm}]</span> ${message}</div>`;
            this.consoleTab.console.prepend(html);
            this.ScrollToTop();
            return;
        }
        const html = `<div>${message}</div>`;
        this.consoleTab.console.prepend(html);
        this.ScrollToTop();
        
    }

    
    ClearConsole() {
        this.unreadMessages = 0;
        this.UpdateUnreadPill();
        this.consoleTab.console.empty();
    } 

    private UpdateUnreadPill() {
        this.consoleTab.consoleMessageCount.text(this.unreadMessages);
    }

    private IsShown() {
        return this.consoleTab.console.is(":hidden") === false;
    }
    private ScrollToTop() {
        if (this.IsShown()) {
            this.consoleTab.parent.scrollTop(0);
        }
    }

    
    static GetInstance() {
        return KarelConsole.instance;
    }

}