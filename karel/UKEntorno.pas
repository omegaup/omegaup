unit UKEntorno;

interface

uses
    Classes, SysUtils,
    NativeXML,
    UKMundo, UKProgramaCompilado;

const
     BD_KAREL                    = $03;
     BD_POSICIONKAREL            = $01;
     BD_DIRECCIONKAREL           = $02;


     BD_STACK                    = $04;
     BD_CALLSTACK                = $08;
     BD_PC                       = $10;
     BD_CUENTAINSTRUCCIONES      = $20;
     BD_HEAP                     = $40;

     RESEJE_ERROR                = -1;
     RESEJE_OK                   = 0;
     RESEJE_FINPROGRAMA          = 1;
     RESEJE_MOVIMIENTOINVALIDO   = 2;
     RESEJE_ZUMBADORINVALIDO     = 3;
     RESEJE_STACKOVERFLOW        = 4;
     RESEJE_STACKUNDERFLOW       = 5;
     RESEJE_HEAPUNDERFLOW        = 6;
     RESEJE_HEAPOVERFLOW         = 7;
     RESEJE_COUNTUNDERFLOW       = 8;
     RESEJE_COUNTOVERFLOW        = 9;
     RESEJE_LIMITEINSTRUCCIONES  = 10;

type
    TKEntornoEjecucion = class
    private
      _programa : TKProgramaCompilado;
      _mundo : TKMundo;

      _tamStack : integer;
      _stack : array of integer;
      _heap : array of integer;
      _count : array of integer;

      _xKarel, _yKarel, _dKarel, _mKarel : integer;
      _stackPointer, _programCounter, _heapPointer, _countPointer : integer;
      _registro : integer;

      _callStack : TStringList;

      _cuentaInstrucciones : array [MINCMD..MAXCMD] of integer;
      _cuentaTotalInstrucciones: integer;
      _maxInstrucciones : integer;
      _maxEjecucionesComando : array [MINCMD..MAXCMD] of integer;

      _breakPoint : boolean;
      _ultimaLineaDepuracion : integer;
      _resUltimaEjecucion : integer;

    public
      constructor Create(unPrograma : TKProgramaCompilado; unMundo : TKMundo;
                         xInicioKarel, yInicioKarel, dirInicioKarel, mochilaInicioKarel : integer;
                         unTamanoStack : integer; maxInstrucciones : integer; maxEjecucionesComando : array of integer);
      destructor Destroy; override;

      function dumpEstadoEntorno(unNodo : TXMLNode; banderas : integer) : integer;
      function ejecutaComando : integer;

      property programa : TKProgramaCompilado read _programa write _programa;
      property mundo : TKMundo read _mundo write _mundo;
      property xKarel : integer read _xKarel write _xKarel;
      property yKarel : integer read _yKarel write _yKarel;
      property direccionKarel : integer read _dKarel write _dKarel;
      property mochilaKarel : integer read _mKarel write _mKarel;
      property SP : integer read _stackPointer write _stackPointer;
      property HP : integer read _heapPointer write _heapPointer;
      property PC : integer read _programCounter write _programCounter;
      property CSP : integer read _countPointer write _countPointer;
      property registro : integer read _registro write _registro;
      property breakPoint : boolean read _breakPoint write _breakPoint;
      property ultimaLineaDepuracion : integer read _ultimaLineaDepuracion write _ultimaLineaDepuracion;
      property resultadoUltimaEjecucion : integer read _resUltimaEjecucion write _resUltimaEjecucion;
    end;

function descEjecucion(resultado : integer) : string;
function descDireccion(direccion : integer) : string;

implementation

function descEjecucion(resultado : integer) : string;
begin
     case resultado of
          RESEJE_ERROR                : result:='ERROR';
          RESEJE_OK                   : result:='OK';
          RESEJE_FINPROGRAMA          : result:='FIN PROGRAMA';
          RESEJE_MOVIMIENTOINVALIDO   : result:='MOVIMIENTO INVALIDO';
          RESEJE_ZUMBADORINVALIDO     : result:='ZUMBADOR INVALIDO';
          RESEJE_STACKOVERFLOW        : result:='STACK OVERFLOW';
          RESEJE_STACKUNDERFLOW       : result:='STACK UNDERFLOW';
          RESEJE_HEAPUNDERFLOW        : result:='HEAP UNDERFLOW';
          RESEJE_HEAPOVERFLOW         : result:='HEAP OVERFLOW';
          RESEJE_LIMITEINSTRUCCIONES  : result:='LIMITE DE INSTRUCCIONES';
     end;
end;

function descDireccion(direccion : integer) : string;
begin
     case direccion of
          DIR_NORTE : result:='NORTE';
          DIR_ESTE : result:='ESTE';
          DIR_SUR : result:='SUR';
          DIR_OESTE : result:='OESTE';
     end;
end;

{ TKEntornoEjecucion }

constructor TKEntornoEjecucion.Create(unPrograma : TKProgramaCompilado; unMundo : TKMundo;
                         xInicioKarel, yInicioKarel, dirInicioKarel, mochilaInicioKarel : integer;
                         unTamanoStack : integer; maxInstrucciones : integer; maxEjecucionesComando : array of integer);
var
   i : integer;
begin
     _programa:=unPrograma;
     _mundo:=unMundo;

     _xKarel:=xInicioKarel;
     _yKarel:=yInicioKarel;
     _dKarel:=dirInicioKarel;
     _mKarel:=mochilaInicioKarel;

     _tamStack:=unTamanoStack;
     SetLength(_stack,_tamStack);
     SetLength(_heap,_tamStack);
     SetLength(_count,_tamStack);

     _programCounter:=0;
     _stackPointer:=0;
     _heapPointer:=0;
     _countPointer:=0;
     _registro:=0;

     _callStack:=TStringList.Create;

     _cuentaTotalInstrucciones:=0;
     _maxInstrucciones:=maxInstrucciones;
     for i:=MINCMD to MAXCMD do begin
         _cuentaInstrucciones[i]:=0;
         _maxEjecucionesComando[i]:=maxEjecucionesComando[i-MINCMD];
     end;
     
end;

destructor TKEntornoEjecucion.Destroy;
begin
     _callStack.Free;
     inherited;
end;

function TKEntornoEjecucion.dumpEstadoEntorno(unNodo : TXMLNode;
  banderas: integer): integer;
var
   i : integer;
   nodo, nodoi : TXMLNode;
begin
     unNodo.WriteAttributeString('resultadoEjecucion',DescEjecucion(resultadoUltimaEjecucion));

     if (banderas and BD_KAREL) <> 0 then begin
        nodo:=unNodo.NodeNew('karel');

        if (banderas and BD_POSICIONKAREL) <> 0 then begin
           nodo.WriteAttributeInteger('x',xKarel);
           nodo.WriteAttributeInteger('y',yKarel);
        end;

        if (banderas and BD_DIRECCIONKAREL) <> 0 then begin
           nodo.WriteAttributeString('direccion',DescDireccion(direccionKarel));
        end;
     end;

     if (banderas and BD_STACK) <> 0 then begin
        nodo:=unNodo.nodeNew('stackPointer');
        nodo.WriteAttributeInteger('valor',SP);
        {TODO : Debe de guardar de preferencia tambien los valores que estan en el stack y todos los valores posibles}
     end;

     if (banderas and BD_CALLSTACK) <> 0 then begin
        nodo:=unNodo.nodeNew('callStack');
        for i:=0 to _callStack.Count - 1 do begin
            nodoi:=nodo.NodeNew('llamada');
            nodoi.Value:=_callStack.Strings[i];
        end;
     end;

     if (banderas and BD_PC) <> 0 then begin
        nodo:=unNodo.nodeNew('programCounter');
        nodo.WriteAttributeInteger('valor',PC);
     end;

     if (banderas and BD_CUENTAINSTRUCCIONES) <> 0 then begin
        nodo:=unNodo.NodeNew('instrucciones');
        for i:=MINCMD to MAXCMD do begin
            nodoi:=nodo.NodeNew('comando');
            nodoi.WriteAttributeString('nombre',CMD_NOMBRES[i]);
            nodoi.WriteAttributeInteger('cuenta',_cuentaInstrucciones[i]);
        end;
     end;

     if (banderas and BD_HEAP) <> 0 then begin
        nodo:=unNodo.nodeNew('heap');
        {TODO : Debe de guardar de preferencia tambien los valores que estan en el heap}
     end;

end;

function TKEntornoEjecucion.ejecutaComando: integer;
var
   comando : PComando;
   vInt : integer;
   procName : string;
begin
     result:=RESEJE_OK;

     comando:=programa.comando[PC];
     _cuentaInstrucciones[comando^.Comando]:=_cuentaInstrucciones[comando^.Comando] + 1;
     _cuentaTotalInstrucciones:=_cuentaTotalInstrucciones + 1;
     
     if (_cuentaTotalInstrucciones > _maxInstrucciones) or (_cuentaInstrucciones[comando^.Comando] > _maxEjecucionesComando[comando^.Comando]) then begin
         result:=RESEJE_LIMITEINSTRUCCIONES;
     end
     else begin
     case comando^.Comando of
          CMD_EOP,
          CMD_APAGATE : begin   // EL APAGATE ES EQUIVALENTE AL FIN DE PROGRAMA
            result:=RESEJE_FINPROGRAMA;
          end;
          CMD_POP,
          CMD_POPREG : begin
            if SP > 0 then begin
               SP:=SP - 1;
               registro:=_stack[SP];
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_STACKUNDERFLOW;
            end;
          end;
          CMD_PUSH,
          CMD_PUSHREG : begin
            if SP < _tamStack then begin
               _stack[SP]:=registro;
               SP:=SP + 1;
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_STACKOVERFLOW;
            end;
          end;
          CMD_RET : begin
            if SP > 0 then begin
               SP:=SP - 1;
               PC:=_stack[SP] + 1;
               if _callStack.Count > 0 then begin
                  _callStack.Delete(_callStack.Count - 1);
               end;
            end
            else begin
                 result:=RESEJE_STACKUNDERFLOW;
            end;
          end;
          CMD_DEBUG : begin
            breakPoint:=programa.hayBreakPoint(programa.comando[PC]^.P1);
            ultimaLineaDepuracion:=programa.comando[PC]^.P1;
            PC:=PC + 1;
          end;
          CMD_AVANZA : begin
            if mundo.avanceValido(xKarel,yKarel,direccionKarel) then begin
               case direccionKarel of
                    DIR_NORTE : begin
                      yKarel:=yKarel + 1;
                    end;
                    DIR_OESTE : begin
                      xKarel:=xKarel - 1;
                    end;
                    DIR_SUR : begin
                      yKarel:=yKarel - 1;
                    end;
                    DIR_ESTE : begin
                      xKarel:=xKarel + 1;
                    end;
               end;
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_MOVIMIENTOINVALIDO;
            end;
          end;
          CMD_GIRAIZQ : begin
            case direccionKarel of
                 DIR_NORTE : direccionKarel:=DIR_OESTE;
                 DIR_OESTE : direccionKarel:=DIR_SUR;
                 DIR_SUR : direccionKarel:=DIR_ESTE;
                 DIR_ESTE : direccionKarel:=DIR_NORTE;
            end;
            PC:=PC + 1;
          end;
          CMD_COGEZUM : begin
            if mundo.cogeZumbador(xKarel,yKarel) then begin
               if mochilaKarel <> $FFFF then
                  mochilaKarel:=mochilaKarel + 1;
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_ZUMBADORINVALIDO;
            end;
          end;
          CMD_DEJAZUM : begin
            if (mochilaKarel > 0) and (mundo.dejaZumbador(xKarel,yKarel)) then begin
               if mochilaKarel <> $FFFF then
                  mochilaKarel:=mochilaKarel - 1;
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_ZUMBADORINVALIDO;
            end;
          end;
          CMD_JMPREGFALSE : begin
            if registro = 0 then begin
               PC:=programa.comando[PC]^.P1;
            end
            else begin
                 PC:=PC + 1;
            end;
          end;
          CMD_JMP : begin
            PC:=programa.comando[PC]^.P1;
          end;
          CMD_POPANDREG : begin
            if SP > 0 then begin
               SP:=SP - 1;
               vInt:=_stack[SP];
               registro:=registro and vInt;
            end
            else begin
                 result:=RESEJE_STACKUNDERFLOW;
            end;
            PC:=PC + 1;
          end;
          CMD_POPORREG : begin
            if SP > 0 then begin
               SP:=SP - 1;
               vInt:=_stack[SP];
               registro:=registro or vInt;
            end
            else begin
                 result:=RESEJE_STACKUNDERFLOW;
            end;
            PC:=PC + 1;
          end;
          CMD_NOTREG,
          CMD_SIESCERO : begin
            if registro = 0 then
               registro:=1
            else
                registro:=0;

            PC:=PC + 1;
          end;
          CMD_LOADREG : begin
            registro:=programa.comando[PC]^.P1;
            PC:=PC + 1;
          end;
          CMD_PRECEDE : begin
            registro:=registro - 1;
            PC:=PC + 1;
          end;
          CMD_SUCEDE : begin
            registro:=registro + 1;
            PC:=PC + 1;
          end;
          CMD_LOADREG_FROMHEAP : begin
            registro:=_heap[HP - programa.comando[PC]^.P1];
            PC:=PC + 1;
          end;
          CMD_B_FL : begin
            if mundo.avanceValido(xKarel,yKarel,direccionKarel) then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_FB : begin
            if mundo.avanceValido(xKarel,yKarel,direccionKarel) then
               registro:=0
            else
                registro:=1;
            PC:=PC + 1;
          end;
          CMD_B_DL : begin
            case direccionKarel of
                 DIR_NORTE : vInt:=DIR_ESTE;
                 DIR_OESTE : vInt:=DIR_NORTE;
                 DIR_SUR : vInt:=DIR_OESTE;
                 DIR_ESTE : vInt:=DIR_SUR;
            end;
            if mundo.avanceValido(xKarel,yKarel,vInt) then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_DB : begin
            case direccionKarel of
                 DIR_NORTE : vInt:=DIR_ESTE;
                 DIR_OESTE : vInt:=DIR_NORTE;
                 DIR_SUR : vInt:=DIR_OESTE;
                 DIR_ESTE : vInt:=DIR_SUR;
            end;
            if mundo.avanceValido(xKarel,yKarel,vInt) then
               registro:=0
            else
                registro:=1;
            PC:=PC + 1;
          end;
          CMD_B_IL : begin
            case direccionKarel of
                 DIR_NORTE : vInt:=DIR_OESTE;
                 DIR_OESTE : vInt:=DIR_SUR;
                 DIR_SUR : vInt:=DIR_ESTE;
                 DIR_ESTE : vInt:=DIR_NORTE;
            end;
            if mundo.avanceValido(xKarel,yKarel,vInt) then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_IB : begin
            case direccionKarel of
                 DIR_NORTE : vInt:=DIR_OESTE;
                 DIR_OESTE : vInt:=DIR_SUR;
                 DIR_SUR : vInt:=DIR_ESTE;
                 DIR_ESTE : vInt:=DIR_NORTE;
            end;
            if mundo.avanceValido(xKarel,yKarel,vInt) then
               registro:=0
            else
                registro:=1;
            PC:=PC + 1;
          end;
          CMD_B_JAZ : begin
            if mundo.cogeZumbador(xKarel,yKarel,1,false) then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_NJAZ : begin
            if mundo.cogeZumbador(xKarel,yKarel,1,false) then
               registro:=0
            else
                registro:=1;
            PC:=PC + 1;
          end;
          CMD_B_AZELM : begin
            if mochilaKarel > 0 then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_NZELM : begin
            if mochilaKarel > 0 then
               registro:=0
            else
                registro:=1;
            PC:=PC + 1;
          end;
          CMD_B_OAN : begin
            if direccionKarel = DIR_NORTE then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_NOAN : begin
            if direccionKarel <> DIR_NORTE then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_OAE : begin
            if direccionKarel = DIR_ESTE then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_NOAE : begin
            if direccionKarel <> DIR_ESTE then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_OAS : begin
            if direccionKarel = DIR_SUR then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_NOAS : begin
            if direccionKarel <> DIR_SUR then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_OAO : begin
            if direccionKarel = DIR_OESTE then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_B_NOAO : begin
            if direccionKarel <> DIR_OESTE then
               registro:=1
            else
                registro:=0;
            PC:=PC + 1;
          end;
          CMD_PUSHPC : begin
            if SP < _tamStack then begin
               _stack[SP]:=PC + programa.comando[PC]^.P1;
               SP:=SP + 1;
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_STACKOVERFLOW;
            end;
          end;
          CMD_POPPC : begin
            if SP > 0 then begin
               SP:=SP - 1;
               PC:=_stack[SP];
            end
            else begin
                 result:=RESEJE_STACKUNDERFLOW;
            end;
          end;
          CMD_CALL : begin
            if programa.comando[PC]^.P2 <> $FFFF then begin
               procName:=programa.constante[programa.comando[PC]^.P3];
               if (procName <> '') and (procName[1] = '*') then
                  _callStack.Add(procName + '(' + IntToStr(registro) + ') -- desde linea ' + IntToStr(self._ultimaLineaDepuracion))
               else
                   _callStack.Add(procName + '() -- desde linea ' + IntToStr(self._ultimaLineaDepuracion))
            end;
            PC:=programa.comando[PC]^.P1;
          end;
          CMD_POP_FROMHEAP : begin
            if HP > 0 then begin
               HP:=HP - 1;
               registro:=_heap[HP];
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_HEAPUNDERFLOW;
            end;
          end;
          CMD_PUSHREG_TOHEAP : begin
            if HP < _tamStack then begin
               _heap[HP]:=registro;
               HP:=HP + 1;
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_HEAPOVERFLOW;
            end;
          end;

          CMD_POP_FROMCOUNT : begin
            if CSP > 0 then begin
               CSP:=CSP - 1;
               registro:=_count[CSP];
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_COUNTUNDERFLOW;
            end;
          end;
          CMD_PUSHREG_TOCOUNT : begin
            if CSP < _tamStack then begin
               _count[CSP]:=registro;
               CSP:=CSP + 1;
               PC:=PC + 1;
            end
            else begin
                 result:=RESEJE_COUNTOVERFLOW;
            end;
          end;
     end;

     end;
     
     resultadoUltimaEjecucion:=result;
end;


end.
