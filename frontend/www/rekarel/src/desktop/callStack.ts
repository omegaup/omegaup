import { KarelNumbers } from "@rekarel/core";
import { KarelController } from "../KarelController";


export type CallStackUI = {
    panel:JQuery
    lastReturn: JQuery
}

let MAX_STACK_SIZE = 650;

export class CallStack {
    panel:JQuery
    lastReturn: JQuery
    constructor(data:CallStackUI ) {
        this.panel = data.panel;
        this.lastReturn = data.lastReturn;

        KarelController.GetInstance().RegisterNewWorldObserver((a,_, newInstance)=> {if (newInstance) this.OnStackChanges();});
        this.OnStackChanges();
        KarelController.GetInstance().RegisterResetObserver((_)=>this.clearStack());
        KarelController.GetInstance().RegisterSlowModeObserver((_,limit)=>this.slowMode(limit));
    }

    private getCollapsedHTML(evt) {
      const karelController = KarelController.GetInstance();

      let runtime = karelController.GetRuntime();
      
      let msg = this.getCallInfo(evt);
      if (runtime.state.stackSize === MAX_STACK_SIZE +1) {
        return msg;
      }
      msg += `<br><span class="text-primary">${MAX_STACK_SIZE+1} - ${runtime.state.stackSize-1}</span>` +
          '<span> Funciones ocultas </span><br/><span>Hay demasiadas funciones en la pila, las más recientes no se muestran en la interfaz, pero estan ahí </span>';
      return msg
    }

    private toKarelString(value:number) :string{
      if (KarelNumbers.isInfinite(value)) {
        return '∞'
      }
      return `${value}`;
    }

    private getCallInfo(evt) {
      const karelController = KarelController.GetInstance();

      let runtime = karelController.GetRuntime();
      
      const onclick = `karel.MoveEditorCursorToLine(${evt.details.line +1})`;

      let params = "";
      if (evt.details.params.length > 0) {
        const len = evt.details.params.length;
        params+= this.toKarelString(evt.details.params[0]);
        for (let i =  1; i < len; i++) {
          params += `, ${this.toKarelString(evt.details.params[i])}`;
        }
      }
      return `<span class="text-info">${runtime.state.stackSize}</span> - ` +
                evt.details.function +
                ' (' +
                `<span class="text-primary"><b>${params}</b></span>` +
                `) <a href="#" class="badge bg-primary text-decoration-none" onclick="${onclick}"> Desde línea ` +
                (evt.details.line + 1) +
                '</a>';
    }
    private OnStackChanges() {
        //FIXME: Don't hardcode the id. #pilaTab
        const karelController = KarelController.GetInstance();
        let runtime = karelController.GetRuntime();
        runtime.eventController.addEventListener('call', evt=> {   
            if (runtime.state.stackSize == MAX_STACK_SIZE+1) {
                this.panel.prepend(
                    '<div class="alert alert-warning">' +
                    this.getCollapsedHTML(evt)+                      
                      '</div>',
                  );
                return;
            } 
            if (runtime.state.stackSize > MAX_STACK_SIZE) {
                this.panel.find('>:first-child').html(this.getCollapsedHTML(evt));
                return;
            }

            this.panel.prepend(
              '<div class="well well-small">' + this.getCallInfo(evt)+'</div>',
            );
          });
          runtime.eventController.addEventListener('return', evt=> {            
            if (evt.details.type === "return") {
              const returnType = karelController
                .GetDebugData()
                .definitions
                .getFunction(evt.details.fromFunction)
                .returnType;
              if (returnType === "VOID") {
                this.lastReturn.text("La última función no regresa ningún valor.");
              } else if (returnType === "INT") {
                this.lastReturn.text(`Último valor retornado: ${evt.details.returnValue}`);
              } else if (returnType === "BOOL") {
                this.lastReturn.text(`Último valor retornado: ${evt.details.returnValue === 0? "falso":"verdadero"}`);
              } else {
                this.lastReturn.text(`Último valor retornado: (tipo desconocido) ${evt.details.returnValue}`);

              }
            } else {
              this.lastReturn.text(`(ERROR INTERNO) Último valor retornado: ${runtime.state.ret}`);
            }
            if (runtime.state.stackSize > MAX_STACK_SIZE) {
              this.panel.find('>:first-child').html(this.getCollapsedHTML(evt));
              return;
            }
            var arreglo = this.panel.find('>:first-child').remove();
          });
          runtime.eventController.addEventListener('start', evt=> {
            this.clearStack();
          });
    }
    private clearStack() {
      this.lastReturn.text("");
        var arreglo = this.panel.empty();
    }

    private slowMode(limit:number) {
      const txt = limit.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
      this.panel.prepend(
        '<div class="alert alert-danger">'+

        `<span> <i class="bi bi-exclamation-triangle-fill"></i> Se ejecutaron más de ${txt} instrucciones,`+
        `por lo que se activo el modo de ejecución rápido, así que la pila muestra el`+
        ` estado en el que se encontraba hasta la instrucción ${txt} </span>`+
        `<br>`+
        "<span>Esto se puede cambiar en la configuración de ReKarel</span>"+
        `</div>`
      );
    }

}