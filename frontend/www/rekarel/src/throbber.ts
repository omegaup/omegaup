import bootstrap from "bootstrap";


const FRAME_RATE = 24;
const FRAME_LENGTH = 1000/ FRAME_RATE ;
class Throbber {
    modal:bootstrap.Modal
    interrupter: JQuery
    shouldBeVisible:boolean;
    visible:boolean;
    private interrupted:boolean;
    constructor(element:JQuery, interrupter:JQuery) {
        this.modal = bootstrap.Modal.getOrCreateInstance(element[0]);
        this.shouldBeVisible = false;
        this.visible= false;
        this.interrupted = false;
        this.hide();
        this.interrupter = interrupter;
        interrupter.on("click",()=>this.Interrupt());
        element.on("hide.bs.modal", ()=>this.Interrupt());
        element.on("shown.bs.modal", ()=>this._HideIfNeeded());
    }
    
    show() {
        this.modal.show();
        this.visible = true;
    }
    hide() {
        this.shouldBeVisible=false;
        this.visible= false;
        this.modal.hide();
    }

    showInSeconds(seconds:number) {
        this.shouldBeVisible = true;
        setTimeout(()=> {
            if (this.shouldBeVisible) {
                this.show();
            }
        },seconds*1000);
    }

    Interrupt() {
        this.interrupted = true;
    }


    async performTask<T>(task:()=>Generator<T>, interrupt?:()=>T) {
        this.interrupted = false;
        this.shouldBeVisible = true;
        const iter = task();
        let curr = iter.next();
        let last = curr.value;
        let startTime = Date.now();

        while (!curr.done && (Date.now() - startTime) < 1000) {
            last = curr.value;
            curr = iter.next();
        }
        if (curr.done) {
            return last;
        }
        this.show()
        const promise = new Promise<T>(

            (resolve,reject)=> setTimeout(
                ()=> {                    
                    const _step = () => {
                        if (curr.done) {                            
                            this.hide();
                            resolve(last)
                            return;
                        }
                        if (this.interrupted) {
                            this.hide();
                            resolve(interrupt?.() ?? last);
                            return;
                        }
                        startTime = Date.now();
                        while (!curr.done && Date.now() - startTime < FRAME_LENGTH) {
                            last = curr.value;
                            curr = iter.next();
                            
                        }
                        setTimeout(_step);
                    }
                    _step();
                }
            )            
        );        
        return promise;
    }

    private _HideIfNeeded() {        
        if (!this.shouldBeVisible) {
            this.hide();
        }
    }
}



export const throbber = new Throbber($("#throbber"), $("#throbberInterrupt"));