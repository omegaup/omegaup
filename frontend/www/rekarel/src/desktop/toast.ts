import bootstrap from "bootstrap"
import { KarelController } from "../KarelController"


export type ToastUI = {
    compileError:JQuery
    compileSuccess:JQuery
    breakpoint:JQuery
    runtimeError:JQuery
    runtimeSuccess: JQuery
}

export class ToastController {
    compileSuccess : bootstrap.Toast
    compileError : bootstrap.Toast
    breakpoint : bootstrap.Toast
    runtimeError : bootstrap.Toast
    runtimeSuccess : bootstrap.Toast
    constructor(ui:ToastUI) {
        this.compileSuccess  = new bootstrap.Toast(ui.compileSuccess[0]);
        this.compileError = new bootstrap.Toast(ui.compileError[0]);
        this.breakpoint = new bootstrap.Toast(ui.breakpoint[0]);
        this.runtimeError = new bootstrap.Toast(ui.runtimeError[0]);
        this.runtimeSuccess = new bootstrap.Toast(ui.runtimeSuccess[0]);
        KarelController.GetInstance().RegisterCompileObserver((_, success, __)=> {
            try {
                if (success) {
                    this.compileSuccess.show();
                } else {
                    this.compileError.show();
                }
            } catch(e) {
                console.log(e);
            }
        });    
        KarelController.GetInstance().RegisterStateChangeObserver((_, state)=> {
            try {
                if (state !== "finished") {
                    return;
                }
                if (KarelController.GetInstance().EndedOnError()) {
                    this.runtimeError.show();
                } else {
                    this.runtimeSuccess.show();
                }
            } catch(e) {
                console.log(e);
            }
        });
    }

}