unit UKProgramaCompilado;

interface

uses
    Classes, SysUtils;

const
     MINCMD           = 998;
     MAXCMD           = 1045;

     // COMANDOS ENSAMBLADOR
     CMD_EOP          = 998;
     CMD_APAGATE      = 999;   // EL APAGATE ES EQUIVALENTE AL FIN DE PROGRAMA
     CMD_POP          = 1000;
     CMD_PUSH         = 1001;
     CMD_RET          = 1002;
     CMD_DEBUG        = 1003;
     CMD_AVANZA       = 1004;
     CMD_NADA         = 1005;
     CMD_GIRAIZQ      = 1006;
     CMD_COGEZUM      = 1007;
     CMD_DEJAZUM      = 1008;
     CMD_JMPREGFALSE  = 1009;
     CMD_JMP          = 1010;
     CMD_PUSHREG      = 1011;
     CMD_POPANDREG    = 1012;
     CMD_POPORREG     = 1013;
     CMD_NOTREG       = 1014;
     CMD_SIESCERO     = 1015;
     CMD_LOADREG      = 1016;
     CMD_PRECEDE      = 1017;
     CMD_DECREG       = 1017; // EL COMANDO DECREG ES EQUIVALENTE AL COMANDO PRECEDE
     CMD_SUCEDE       = 1018;
     CMD_LOADREG_FROMHEAP = 1019;
     CMD_B_FL         = 1020;
     CMD_B_FB         = 1021;
     CMD_B_DL         = 1022;
     CMD_B_DB         = 1023;
     CMD_B_IL         = 1024;
     CMD_B_IB         = 1025;
     CMD_B_JAZ        = 1026;
     CMD_B_NJAZ       = 1027;
     CMD_B_AZELM      = 1028;
     CMD_B_NZELM      = 1029;
     CMD_B_OAN        = 1030;
     CMD_B_NOAN       = 1031;
     CMD_B_OAE        = 1032;
     CMD_B_NOAE       = 1033;
     CMD_B_OAS        = 1034;
     CMD_B_NOAS       = 1035;
     CMD_B_OAO        = 1036;
     CMD_B_NOAO       = 1037;
     CMD_POPREG       = 1038;
     CMD_PUSHPC       = 1039;
     CMD_POPPC        = 1040;
     CMD_CALL         = 1041;
     CMD_POP_FROMHEAP = 1042;
     CMD_PUSHREG_TOHEAP = 1043;
     CMD_PUSHREG_TOCOUNT = 1044;
     CMD_POP_FROMCOUNT   = 1045;

     CMD_NOMBRES : array [MINCMD..MAXCMD] of string = ('FIN','APAGATE','POP','PUSH','RETURN','DEBUG',
                          'AVANZA','NADA','GIRA_IZQUIERDA','COGE_ZUMBADOR','DEJA_ZUMBADOR',
                          'JMP_REG_FALSE','JMP','PUSH_REG','POP_AND_REG','POP_OR_REG',
                          'NOT_REG','SI_ES_CERO','LOAD_REG','PRECEDE',
                          'SUCEDE','LOAD_REG_FROM_HEAP','FRENTE_LIBRE',
                          'FRENTE_BLOQUEADO','DERECHA_LIBRE','DERECHA_BLOQUEADA',
                          'IZQUIERDA_LIBRE','IZQUIERDA_BLOQUEADA','JAZ','NJAZ',
                          'AZELM','NZELM','OAN','NOAN','OAE','NOAE','OAS','NOAS',
                          'OAO','NOAO','POP_REG','PUSH_PC','POP_PC','CALL',
                          'POP_FROM_HEAP','PUSH_REG_TO_HEAP','PUSH_REG_TO_COUNT','POP_FROM_COUNT');

     CMD_CONSTANTE  = 30000;


type
    PComando = ^TComando;

    {$A-}
    TComando = record
        Comando : WORD;
        P1, P2, P3 : WORD;
    end;
    {$A+}

    TKProgramaCompilado = class
    private
      af : FILE of TComando;
      Comandos : array of TComando;
      _cuentaComandos : array [MINCMD..MAXCMD] of integer;

      _errMsg : string;

      _programCounter : integer;
      _stackPointer : integer;

      _infoDepuracion : boolean;
      _listaDeConstantes : TStringList;

      _breakPoints : array [0..1000] of integer;
      _NBreakPoints : integer;

      function getNumeroDeInstrucciones: integer;
      function getNumeroDeComandosPorTipo(tipo: integer): integer;
      function getComando(PCi: integer): PComando;
      function getConstante(indice: integer): string;

    public
      constructor Create;
      destructor Destroy; override;

      procedure InsertaComando(Cmd : WORD; P1 : WORD = 0; P2 : WORD = 0; P3 : WORD = 0; Dir : integer = -1); // OK

      function guardaAArchivo(const nombreArchivo : string) : boolean; // OK
      function leeDeArchivo(const nombreArchivo : string) : boolean;   // OK
      function resumenCompilacion : AnsiString;
      function reconstruyeConstantes : integer;

      function HayBreakPoint(i : integer) : boolean;
      function ToggleBreakPoint(x : integer) : boolean;
      procedure LimpiaBreakPoints;

      property PC : integer read _programCounter write _programCounter;
      property SP : integer read _stackPointer write _stackPointer;

      property infoDepuracion : boolean read _infoDepuracion write _infoDepuracion;
      property numeroDeInstrucciones : integer read getNumeroDeInstrucciones;
      property numeroDeComandosPorTipo[tipo : integer] : integer read getNumeroDeComandosPorTipo;
      property comando[PCi : integer] : PComando read getComando;
      property constante[indice : integer] : string read getConstante;

      property errMsg : string read _errMsg write _errMsg;
    end;


implementation

{ TKProgramaCompilado }

constructor TKProgramaCompilado.Create;
begin
     setLength(comandos,0);
     _programCounter:=0;
     _stackPointer:=0;
     _NBreakpoints:=0;
     _listaDeConstantes:=TStringList.Create;
end;

destructor TKProgramaCompilado.Destroy;
begin
     setLength(comandos,0);

     _listaDeConstantes.Free;

     inherited;
end;

function TKProgramaCompilado.getComando(PCi: integer): PComando;
begin
     if (PCi >= 0) and (PCi <= High(Comandos)) then begin
        result:=@Comandos[PCi];
     end
     else
         result:=nil;
end;

function TKProgramaCompilado.getConstante(indice: integer): string;
begin
     if (indice >= 0) and (indice < _listaDeConstantes.Count) then
        result:=_listaDeConstantes[indice]
     else
         result:='';
end;

function TKProgramaCompilado.getNumeroDeComandosPorTipo(tipo: integer): integer;
begin
     if (tipo >= MINCMD) and (tipo <= MAXCMD) then
        result:=_cuentaComandos[tipo]
     else
         result:=0;
end;

function TKProgramaCompilado.getNumeroDeInstrucciones: integer;
begin
     result:=Length(comandos);
end;

function TKProgramaCompilado.guardaAArchivo(const nombreArchivo: string) : boolean;
var
   escritos : integer;
begin
     result:=false;
     try
        AssignFile(af,nombreArchivo);
        try
           Rewrite(af);
           BlockWrite(af,comandos[0],Length(comandos),escritos);
           if escritos = Length(comandos) then
              result:=true;
        finally
               CloseFile(af);
        end;
     except
           On E : exception do begin
              errMsg:=E.Message;
           end;           
     end;
end;

function TKProgramaCompilado.HayBreakPoint(i: integer): boolean;
var
   j : integer;
   res : boolean;
begin
     res:=false;
     for j:=0 to _NBreakPoints - 1 do begin
         if _breakPoints[j] = i then begin
            res:=true;
            break;
         end;
     end;
     result:=res;
end;

procedure TKProgramaCompilado.InsertaComando(Cmd, P1, P2, P3: WORD;
  Dir: integer);
begin

     {$region ' Si es informacion de depuracion (POSIBILIDAD DE PONER UN BREAKPOINT) verifica si debe agregarse '}
       if PC > 0 then begin
          if (Cmd = CMD_DEBUG) and (comandos[PC - 1].Comando = CMD_DEBUG) and (P1 = comandos[PC - 1].P1) then begin
             exit;
          end;

          if (Cmd = CMD_DEBUG) and Not(InfoDepuracion) then
             exit;
       end;
     {$endregion}

     {$region ' El direccionamiento es inmediato '}
       if Dir = -1 then begin
          {$region ' Asegurate que haya espacio suficiente '}
            if length(comandos) <= PC then
               setLength(comandos,PC + 1);
          {$endregion}
          comandos[PC].Comando:=Cmd;
          comandos[PC].P1:=p1;
          comandos[PC].P2:=p2;
          comandos[PC].P3:=p3;

          if (Cmd >= MINCMD) and (Cmd <= MAXCMD) then
             Inc(_cuentaComandos[Cmd]);

          PC:=PC + 1;
       end
     {$endregion}

     {$region ' El direccionamiento es indexado '}
       else begin
            {$region ' Asegurate que haya espacio suficiente '}
              if length(comandos) <= Dir then
                 setLength(comandos,Dir + 1);
            {$endregion}

            if comandos[Dir].Comando <> 0 then
               Dec(_cuentaComandos[comandos[Dir].Comando]);

            comandos[Dir].Comando:=Cmd;
            comandos[Dir].P1:=p1;
            comandos[Dir].P2:=p2;
            comandos[Dir].P3:=p3;

            Inc(_cuentaComandos[Cmd]);
       end;
     {$endregion}
end;

function TKProgramaCompilado.leeDeArchivo(const nombreArchivo: string) : boolean;
var
   leidos : integer;
   i, n : integer;
   buff : array [0..999] of TComando;
begin
     result:=false;
     try
        AssignFile(af,nombreArchivo);
        try
           Reset(af);
           n:=0;
           repeat
                 BlockRead(af,buff[0],1000,leidos);
                 SetLength(comandos,Length(comandos) + leidos);
                 for i:=0 to leidos - 1 do begin
                     comandos[n]:=buff[i];
                     Inc(n);
                 end;
           until leidos = 0;
           reconstruyeConstantes;
           result:=true;
        finally
               CloseFile(af);
        end;
     except
           On E : exception do begin
              errMsg:=E.Message;
           end;           
     end;
end;



procedure TKProgramaCompilado.LimpiaBreakPoints;
begin
     _NBreakPoints:=0;
end;

function TKProgramaCompilado.reconstruyeConstantes: integer;
var
   i, j, b : integer;
   st : string;
begin
     for i:=Low(comandos) to High(comandos) do begin
         if comandos[i].Comando = CMD_CONSTANTE then begin
            j:=comandos[i].P1;
            st:='';
            b:=(comandos[i].P2 and $FF00) shr 8;
            if b > 0 then st:=st + chr(b);
            b:=comandos[i].P2 and $FF;
            if b > 0 then st:=st + chr(b);
            b:=(comandos[i].P3 and $FF00) shr 8;
            if b > 0 then st:=st + chr(b);
            b:=comandos[i].P3 and $FF;
            if b > 0 then st:=st + chr(b);

            while j >= _listaDeConstantes.Count do _listaDeConstantes.Add('');

            _listaDeConstantes[j]:=_listaDeConstantes[j] + st;
         end;
     end;
end;

function TKProgramaCompilado.resumenCompilacion: AnsiString;
begin
     result:='instrucciones ensamblador: ' + IntToStr(numeroDeInstrucciones) + #13#10 +
             'Avanza: ' + IntToStr(numeroDeComandosPorTipo[CMD_AVANZA]) + #13#10 +
             'Apagate: ' + IntToStr(numeroDeComandosPorTipo[CMD_APAGATE]) + #13#10 +
             'Gira-Izquierda: ' + IntToStr(numeroDeComandosPorTipo[CMD_GIRAIZQ]) + #13#10 +
             'Deja-Zumbador: ' + IntToStr(numeroDeComandosPorTipo[CMD_DEJAZUM]) + #13#10 +
             'Coge-Zumbador: ' + IntToStr(numeroDeCOmandosPorTipo[CMD_COGEZUM]) + #13#10 +
             'Breakpoints depuracion: ' + IntToStr(NumeroDeComandosPorTipo[CMD_DEBUG]) + #13#10#13#10;
end;

function TKProgramaCompilado.ToggleBreakPoint(x: integer): boolean;
var
   i, j : integer;
   res : boolean;
begin
     res:=true;
     for i:=0 to _NBreakPoints - 1 do begin
         if _breakPoints[i] = x then begin
            for j:=i to _NBreakPoints - 2 do
                _breakpoints[j]:=_breakPoints[j + 1];
            res:=false;
            Dec(_NBreakPoints);
            break;
         end;
     end;

     if res then begin
        _breakPoints[_NBreakPoints]:=x;
        Inc(_NBreakPoints);
     end;
     
     result:=res;
end;

end.
