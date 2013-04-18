unit UCompilador_V3;

interface

uses
    Classes, SysUtils, 

    UKProgramaCompilado;

const
     LANG_NODEFINIDO    = -1;
     LANG_PASCAL = 0;
     LANG_JAVA   = 1;

     descripcionLenguajes : array [LANG_NODEFINIDO..LANG_JAVA] of string = ('No definido','Pascal','Java');

     RES_OK      = 0;
     RES_ERR     = -1;


     // TIPOS DE TOKEN
     TT_SEPARADOR     = 100;
     TT_COMENTARIO    = 101;
     TT_OPERADOR      = 102;
     TT_CADENA        = 103;
     TT_NENTERO       = 104;
     TT_NREAL         = 105;
     TT_IDENTIFICADOR = 106;

     // TIPOS DE CONSTANTE
     TC_ENTERO        = 200;
     TC_REAL          = 201;
     TC_CADENA        = 202;

     STACKLEN = 65000;

type
    PInteger = ^Integer;
    PReal    = ^Double;

    PToken = ^TToken;
    TToken = record
        Token : string;
        Tipo : integer;
        SubClase : integer;
        Linea : integer;
        Posicion : integer;
    end;

    PProc = ^TProc;
    TProc = record
        Nombre : string;
        Inicio : integer;
        RequiereParam : boolean;
        xInfo : integer;
    end;

    PVar = ^TVar;
    TVar = record
         Nombre : string;
         Posicion : integer;
    end;

    PJCallProc = ^TJCallProc;
    TJCallProc = record
        ProcName : string;
        CallLine : integer;
        param : boolean;
    end;

    TKCompilador = class
    private
      listaTokens, listaVariables,
      listaProcedimientos,
      listaProcedimientosJava : TList;

      _mensajeError : string;
      _lenguaje : integer;

      _codigo : AnsiString;
      _posCodigo, _lCodigo : integer;
      _lineaCodigo, _posicionLineaCodigo : integer;

      _resultadoCompilacion : TKProgramaCompilado;
      _indiceTokenActual : integer;

      _infoDepuracion : boolean;
      _listaDeConstantes : TStringList;

      {$region ' Propiedades exclusivas para compilar Java '}
        _JNameProg : string;
      {$endregion}

    public
      constructor Create;
      destructor Destroy; override;

      procedure limpiaListas;
      function agregaConstanteALista(const constante : string) : integer;

      // FUNCIONES PARA MANEJO DE TOKENS
      function SeparaEnTokens : integer; // OK
      function EliminaEspaciosEnBlanco : integer; // OK

      function ObtenSeparador : integer; // OK
      function ObtenIdentificador : integer; // OK
      function ObtenNumero : integer; // OK
      function ObtenCadenaHasta(FinCad : string; UnaLinea : boolean) : AnsiString; // OK
      function ObtenProcedimiento(ProcName : string; var index : integer) : PProc;

      function tokenActual : PToken;
      function avanzaToken : integer;
      function retrocedeToken : integer;

      function compilaPrograma(unLenguage : integer; const unCodigo : AnsiString) : TKProgramaCompilado; // OK
      function compila(programaCompilado : TKProgramaCompilado) : boolean; // OK

      {$region ' Funciones para compilar gramatica de Pascal '}
        procedure CompilaBloque; // OK
        procedure CompilaDeclaracionDeProcedimiento; // OK
        procedure CompilaDeclaracionDePrototipo;
        procedure CompilaDeclaracionDeEnlace;
        procedure CompilaExpresionGeneral(varNameList : TList = nil); // OK
        procedure CompilaExpresion(varNameList : TList = nil); // OK
        procedure CompilaExpresionSi(varNameList : TList = nil); // OK
        procedure CompilaExpresionRepite(varNameList : TList = nil); // OK
        procedure CompilaExpresionMientras(varNameList : TList = nil); // OK
        procedure CompilaTermino(varNameList : TList = nil); // OK
        procedure CompilaClausulaY(varNameList : TList = nil); // OK
        procedure CompilaClausulaNo(varNameList : TList = nil); // OK
        procedure CompilaClausulaAtomica(varNameList : TList = nil); // OK
        procedure CompilaExpresionEntera(varNameList : TList = nil); // OK
        procedure CompilaFuncionBooleana; // OK
      {$endregion}

      {$region ' Funciones para compilar gramatica de Java '}
        procedure JCompilaClase;
        procedure JCompilaDeclaracionDeMetodo;
        procedure CompilaJBlock(varNameList : TList = nil);
        procedure JCompilaStatement(varNameList : TList = nil);
        procedure JCompilaExpresionSi(varNameList : TList = nil);
        procedure JCompilaExpresionRepite(varNameList : TList = nil);
        procedure JCompilaExpresionMientras(varNameList : TList = nil);
        procedure JCompilaTermino(varNameList : TList = nil);
        procedure JCompilaClausulaY(varNameList : TList = nil);
        procedure JCompilaClausulaNo(varNameList : TList = nil);
        procedure JCompilaClausulaAtomica(varNameList : TList = nil);
        procedure JCompilaExpresionEntera(varNameList : TList = nil);
        procedure JCompilaFuncionBooleana;
      {$endregion}

      property codigo : AnsiString read _codigo write _codigo;
      property lenguaje : integer read _lenguaje write _lenguaje;
      property posicionCodigo : integer read _posCodigo write _posCodigo;
      property longitudCodigo : longint read _lCodigo write _lCodigo;
      property lineaCodigo : integer read _lineaCodigo write _lineaCodigo;
      property posicionLineaCodigo : integer read _posicionLineaCodigo write _posicionLineaCodigo;

      property infoDepuracion : boolean read _infoDepuracion write _infoDepuracion;

      property resultadoCompilacion : TKProgramaCompilado read _resultadoCompilacion write _resultadoCompilacion;
      property indiceTokenActual : integer read _indiceTokenActual write _indiceTokenActual;

      {$region ' Propiedades exclusivas para compilar Java '}
        property JNameProg : string read _JNameProg write _JNameProg;
      {$endregion}

      property errMsg : string read _mensajeError write _mensajeError;
    end;


implementation

var
   // CONJUNTOS
   ChrLetras : set of char = ['a'..'z','A'..'Z','_'];
   ChrSeparadores : set of char = [';',',','.','(',')','[',']',':','{','}',#39,'<','>','=','^','+','-','*','&','|','!'];
   ChrNumeros : set of char = ['0'..'9'];
   ChrEspacioEnBlanco : set of char = [' ',#10,#13,#9];


{ TKCompilador }

function TKCompilador.agregaConstanteALista(const constante: string): integer;
var
   i : integer;
begin
     i:=_listaDeConstantes.IndexOf(constante);
     if i < 0 then begin
        _listaDeConstantes.Add(constante);
        result:=_listaDeConstantes.Count - 1;
     end
     else
         result:=i;
end;

function TKCompilador.avanzaToken: integer;
begin
     indiceTokenActual:=indiceTokenActual + 1;
     if indiceTokenActual < listaTokens.Count then begin
        Result:=RES_OK;
     end
     else begin
          indiceTokenActual:=Pred(listaTokens.Count);
          Result:=RES_ERR;
          raise Exception.Create('Fin inesperado de codigo.');
     end;
end;

function TKCompilador.compila(programaCompilado: TKProgramaCompilado): boolean;
var
   i, j : integer;
   NProc : PProc;
   indproc, indNProc : integer;
   nombreProc : string;
   ok : boolean;
begin
     result:=true;
     resultadoCompilacion:=programaCompilado;
     indiceTokenActual:=0;
     try
        case lenguaje of
             {$region ' PASCAL '}
               LANG_PASCAL : begin
                  if tokenActual^.Token = 'INICIAR-PROGRAMA' then begin // EL ENCABEZADO DEL PROGRAMA ES CORRECTO, DEBE ESTAR SEGUIDO POR UN BLOQUE
                     if avanzaToken = RES_OK then begin
                        CompilaBloque;
                        if tokenActual^.Token <> 'FINALIZAR-PROGRAMA' then begin // EL PROGRAMA NO FINALIZA CORRECTAMENTE
                           raise Exception.Create('Se esperaba "FINALIZAR-PROGRAMA" al final del bloque del programa');
                        end;
                     end
                     else begin
                          raise Exception.Create('Error en el encabezado del programa');
                     end;
                  end
                  else begin
                       raise Exception.Create('Error en el encabezado del programa');
                  end;

                  {$region ' Hay que actualizar los jumps de las llamadas a prototipos '}
                    for i:=0 to Pred(listaProcedimientos.Count) do begin
                        if Pos('_prototype_',PProc(listaProcedimientos.Items[i])^.Nombre) <> 0 then begin
                           nombreProc:=Copy(PProc(listaProcedimientos.Items[i])^.Nombre,1,Length(PProc(listaProcedimientos.Items[i])^.Nombre) - Length('_prototype_'));
                           ok:=false;
                           for j:=i + 1 to Pred(listaProcedimientos.Count) do begin
                               if PProc(listaProcedimientos.Items[j])^.Nombre = nombreProc then begin

                                  // SI NO COINCIDEN EN SU REQUERIMIENTO DE PARAMETROS
                                  if PProc(listaProcedimientos.Items[i])^.RequiereParam <> PProc(listaProcedimientos.Items[j])^.RequiereParam then begin
                                     raise Exception.Create('La definición de la instrucción ' + nombreProc +
                                                            ' en la línea ' + IntToStr(PProc(listaProcedimientos.Items[j])^.Inicio + 1) +
                                                            ' difiere de la definición de su prototipo en la línea ' +
                                                            IntToStr(PProc(listaProcedimientos.Items[i])^.Inicio + 1));
                                  end;

                                  resultadoCompilacion.InsertaComando(CMD_JMP,PProc(listaProcedimientos.Items[j])^.Inicio,0,0,PProc(listaProcedimientos.Items[i])^.xInfo);
                                  ok:=true;
                                  break;
                               end;
                           end;

                           // SI NO ESTA LA DEFINICION DEL PROTOTIPO AVISA
                           if not ok then begin
                              raise Exception.Create('No se encontró la definición de la instrucción ' + nombreProc);
                           end;
                        end;
                    end;
                 {$endregion}

               end;
             {$endregion}

             {$region ' JAVA '}
               LANG_JAVA : begin
                 if tokenActual^.Token = 'class' then begin
                    avanzaToken;
                    if tokenActual^.Tipo = TT_IDENTIFICADOR then begin
                       JNameProg:=tokenActual^.Token;
                       avanzaToken;
                       JCompilaClase;
                    end
                    else begin
                         raise Exception.Create('Se esperaba un identificador como nombre de la clase principal');
                    end;
                 end
                 else begin
                      raise Exception.Create('Error al definir la clase principal del programa');
                 end;

                 {$region ' Hay que actualizar la lista de llamadas a procedimiento '}
                   for i:=0 to Pred(listaProcedimientosJava.Count) do begin
                       NProc:=ObtenProcedimiento(PJCallProc(listaProcedimientosJava.Items[i])^.ProcName,indproc);
                       if NProc <> nil then begin
                          if (NProc^.RequiereParam <> PJCallProc(listaProcedimientosJava.Items[i])^.param) and (NProc^.Nombre <> 'program') then begin
                             raise Exception.Create('El numero de parametros del metodo ' + PJCallProc(listaProcedimientosJava.Items[i])^.ProcName + ' difiere al de su declaracion');
                          end
                          else begin
                               if infoDepuracion then begin
                                  if NProc^.RequiereParam then begin
                                     indNProc:=agregaConstanteALista('*' + NProc^.Nombre);
                                  end
                                  else begin
                                       indNProc:=agregaConstanteALista(NProc^.Nombre);
                                  end;
                               end
                               else
                                   indNProc:=$FFFF;
                               resultadoCompilacion.InsertaComando(CMD_CALL,NProc^.Inicio,indProc,indNProc,PJCallProc(listaProcedimientosJava.Items[i])^.CallLine);
                          end;
                       end
                       else begin
                            raise Exception.Create('El metodo ' + PJCallProc(listaProcedimientosJava.Items[i])^.ProcName + ' no esta definido');
                       end;
                   end;
                 {$endregion}
               end;
             {$endregion}
        end;
     except
           On E : Exception do begin
              result:=false;
              errMsg:=E.Message;
           end;
     end;
end;

procedure TKCompilador.CompilaBloque;
begin
     // UN BLOQUE SE COMPONE DE VARIAS PARTES, SU SINTAXIS ES

     {BLOQUE ::=
                [DeclaracionDeProcedimiento ";" | DeclaracionDeEnlace ";"] ...
                "INICIA-EJECUCION"
                   ExpresionGeneral [";" ExpresionGeneral]...
                "TERMINA-EJECUCION"
     }

     // EL PROGRAMA SIEMPRE VA A INICIAR EN LA DIRECCION CERO, POR LO QUE DEBE SALTAR DE INICIO A LA DIRECCION DE LA RUTINA PRINCIPAL,
     // COMO NO SABEMOS DONDE ESTA DE MOMENTO TENEMOS QUE DEJAR EL ESPACIO PARA LA INSTRUCCION DE SALTO
     resultadoCompilacion.InsertaComando(CMD_JMP,0,0,0);

     {$region ' Compila todas las declaraciones de procedimiento '}
       while (tokenActual^.Token = 'DEFINE-NUEVA-INSTRUCCION') or (tokenActual^.Token = 'EXTERNO') or (tokenActual^.Token = 'DEFINE-PROTOTIPO-INSTRUCCION') do begin
             if tokenActual^.Token = 'DEFINE-NUEVA-INSTRUCCION' then begin
                // ES UNA DECLARACION DE PROCEDIMIENTO
                CompilaDeclaracionDeProcedimiento;
             end
             else if tokenActual^.Token = 'DEFINE-PROTOTIPO-INSTRUCCION' then begin
                  CompilaDeclaracionDePrototipo;
             end
             else begin
                  // ES UNA DECLARACION DE ENLACE.
                  CompilaDeclaracionDeEnlace;
             end;
       end;
     {$endregion}

     {$region ' Comila el bloque del ciclo principal del programa '}
       if tokenActual^.Token = 'INICIA-EJECUCION' then begin
          resultadoCompilacion.InsertaComando(CMD_JMP,resultadoCompilacion.PC,0,0,0);
          avanzaToken; // AVANZA AL SIGUIENTE TOKEN
          CompilaExpresionGeneral;
          if tokenActual^.Token <> 'TERMINA-EJECUCION' then begin
             raise Exception.Create('Se esperaba la palabra clave "TERMINA-EJECUCION" al final del bloque del programa');
          end
          else begin
               // TERMINO OK, FIN DEL PROGRAMA
               resultadoCompilacion.InsertaComando(CMD_EOP,0,0,0);
               avanzaToken;
          end;
       end;
     {$endregion}
end;

procedure TKCompilador.CompilaClausulaAtomica(varNameList: TList);
begin
     // LA CLAUSULA ATOMICA TIENE LA SIGUIENTE SINTAXIS
     {
        ClausulaAtomica ::=  {
                              "SI-ES-CERO" "(" ExpresionEntera ")" |
                              FuncionBooleana |
                              "(" Termino ")"
                             }
     }

     if tokenActual^.Token = 'SI-ES-CERO' then begin
        avanzaToken;
        if tokenActual^.Token = '(' then begin
           avanzaToken;
           CompilaExpresionEntera(varNameList);
           if tokenActual^.Token = ')' then begin
              // INSERTA EL COMANDO DE EVALUACION
              resultadoCompilacion.InsertaComando(CMD_SIESCERO,0,0,0);
              avanzaToken;
           end
           else begin
                raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
           end;
        end
        else begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
     end
     else if tokenActual^.Token = '(' then begin
          avanzaToken;
          CompilaTermino(varNameList);
          if tokenActual^.Token = ')' then begin
             // sintaxis correcta, avanza al siguiente token
             avanzaToken;
          end
          else begin
               raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else begin
          CompilaFuncionBooleana;
     end;
end;

procedure TKCompilador.CompilaClausulaNo(varNameList: TList);
begin
     // LA CLAUSULA NO TIENE LA SIGUIENTE SINTAXIS
     {
        ClausulaNo ::= ["NO"] ClausulaAtomica
     }
     if tokenActual^.Token = 'NO' then begin
        avanzaToken;
        CompilaClausulaAtomica(varNameList);
        // AHORA NIEGA EL RESULTADO
        resultadoCompilacion.InsertaComando(CMD_NOTREG,0,0,0);
     end
     else begin
          CompilaClausulaAtomica(varNameList);
     end;
end;

procedure TKCompilador.CompilaClausulaY(varNameList: TList);
begin
     // LA CLAUSULA Y TIENE LA SIGUIENTE SINTAXIS
     {
        ClausulaY ::= ClausulaNo ["Y" ClausulaNo]...
     }
     CompilaClausulaNo(varNameList);

     while tokenActual^.Token = 'Y' do begin
           // SI HAY UN NUEVO TERMINO, METE EL ULTIMO RESULTADO A LA PILA
           resultadoCompilacion.InsertaComando(CMD_PUSHREG,0,0,0);

           // AUMENTA EN UNO EL OFFSET DE LAS VARIABLES
           resultadoCompilacion.SP:=resultadoCompilacion.SP + 1;

           avanzaToken;
           CompilaClausulaNo(varNameList);
           // AL TERMINAR LA CLAUSULA NO SACALA DE LA PILA
           resultadoCompilacion.InsertaComando(CMD_POPANDREG,0,0,0);

           // DECREMENTA EL OFFSET DE LAS VARIABLES
           resultadoCompilacion.SP:=resultadoCompilacion.SP - 1;
     end;
end;

procedure TKCompilador.CompilaDeclaracionDeEnlace;
begin
     {TODO : Para incluir funciones de librerias externas}
end;

procedure TKCompilador.CompilaDeclaracionDeProcedimiento;
var
   NProc : PProc;
   listaDeVariables : TList;
   Lini, i : integer;
   NVar : PVar;
begin
     // UNA DECLARACION DE PROCEDIMIENTO, TIENE LA SIGUIENTE ESTRUCTURA
     {
      DeclaracionDeProcedimiento ::= "DEFINE-NUEVA-INSTRUCCION" Identificador ["(" Identificador ")"] "COMO"
                                         Expresion
     }

     if tokenActual^.Token <> 'DEFINE-NUEVA-INSTRUCCION' then begin
        raise Exception.Create('Se esperaba palabra clave "DEFINE-NUEVA-INSTRUCCION" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        exit;
     end;

     LIni:=tokenActual^.Linea; // GUARDA INFORMACION PARA DEPURACION
     avanzaToken;

     if tokenActual^.Tipo <> TT_IDENTIFICADOR then begin
        raise Exception.Create('Se esperaba un Identificador en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        exit;
     end;

     New(NProc);
     NProc^.Nombre:=tokenActual^.Token;
     NProc^.Inicio:=resultadoCompilacion.PC;
     listaDeVariables:=TList.Create;

     {$region ' Verifica si el procedimiento recibe parametros '}
       avanzaToken;
       if tokenActual^.Token = 'COMO' then begin
          NProc^.RequiereParam:=false;
          avanzaToken;
       end
       else if tokenActual^.Token = '(' then begin
            NProc^.RequiereParam:=true;
            avanzaToken;

            if tokenActual^.Tipo <> TT_IDENTIFICADOR then begin
               raise Exception.Create('Se esperaba un Identificador en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
               exit;
            end;
       end
       else begin
            raise Exception.Create('Se esperaba la palabra clave "COMO" o una lista de parametros en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
            exit;
       end;
     {$endregion}

     {$region ' Si recibe parametros obten el nombre de la variable '}
       if NProc^.RequiereParam then begin
          New(NVar);
          NVar^.Nombre:=tokenActual^.Token;
          NVar^.Posicion:=resultadoCompilacion.SP;
          listaDeVariables.Add(NVar); // GUARDA EL NOMBRE DE LA VARIABLE PARA INDEXARLO

          avanzaToken;

          if tokenActual^.Token <> ')' then begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             exit;
          end;

          avanzaToken;
          if tokenActual^.Token <> 'COMO' then begin
             raise Exception.Create('Se esperaba la palabra clave "COMO" o una lista de parametros en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             exit;
          end;

          avanzaToken; // AVANZA AL PRIMER TOKEN DE LA EXPRESION
       end;
     {$endregion}

     // INSERTA EL PROCEDIMIENTO EN LA LISTA
     self.listaProcedimientos.Add(NProc);

     // INSERTA LA INFORMACION DE DEPURACION EN EL CODIGO
     resultadoCompilacion.InsertaComando(CMD_DEBUG,LIni,0,0);

     // AQUI HAY QUE COMPILAR LA EXPRESION DEL PROCEDIMIENTO
     CompilaExpresion(listaDeVariables);

     // LIMPIA LAS VARIABLES DEL STACK Y REGRESA
     for i:=0 to Pred(listaDeVariables.Count) do begin
         resultadoCompilacion.InsertaComando(CMD_POP_FROMHEAP,0,0,0); // SACA LAS VARIABLES DEL STACK
     end;

     resultadoCompilacion.InsertaComando(CMD_RET,0,0,0); // TERMINA EL PROCEDIMIENTO;

     // SI TERMINA CON UN PUNTO Y COMA AVANZA
     while tokenActual^.Token = ';' do begin
           avanzaToken;
     end;

end;

procedure TKCompilador.CompilaDeclaracionDePrototipo;
var
   NProc : PProc;
   listaDeVariables : TList;
   Lini, i : integer;
   NVar : PVar;
   LPC : integer;
begin
     {$region ' Verifica que no venga de ningun lugar inesperado '}
       if tokenActual^.Token <> 'DEFINE-PROTOTIPO-INSTRUCCION' then begin
          raise Exception.Create('Se esperaba palabra clave "DEFINE-PROTOTIPO-INSTRUCCION" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          exit;
       end;
     {$endregion}

     LIni:=tokenActual^.Linea; // GUARDA INFORMACION PARA DEPURACION
     avanzaToken;

     {$region ' El nombre de la instruccion debe ser un identificador '}
       if tokenActual^.Tipo <> TT_IDENTIFICADOR then begin
          raise Exception.Create('Se esperaba un Identificador en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          exit;
       end;
     {$endregion}

     New(NProc);
     NProc^.Nombre:=tokenActual^.Token + '_prototype_';
     NProc^.Inicio:=resultadoCompilacion.PC;
     listaDeVariables:=TList.Create;

     {$region ' Verifica si el procedimiento recibe parametros '}
       avanzaToken;
       if tokenActual^.Token = ';' then begin
          NProc^.RequiereParam:=false;
          avanzaToken;
       end
       else if tokenActual^.Token = '(' then begin
            NProc^.RequiereParam:=true;
            avanzaToken;

            if tokenActual^.Tipo <> TT_IDENTIFICADOR then begin
               raise Exception.Create('Se esperaba un Identificador en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
               exit;
            end;
       end
       else begin
            raise Exception.Create('Se esperaba ";" o una lista de parametros en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
            exit;
       end;
     {$endregion}

     {$region ' Si recibe parametros obten el nombre de la variable '}
       if NProc^.RequiereParam then begin
          New(NVar);
          NVar^.Nombre:=tokenActual^.Token + '_prototype_';
          NVar^.Posicion:=resultadoCompilacion.SP;
          listaDeVariables.Add(NVar); // GUARDA EL NOMBRE DE LA VARIABLE PARA INDEXARLO

          avanzaToken;

          if tokenActual^.Token <> ')' then begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             exit;
          end;

          avanzaToken;

          if tokenActual^.Token <> ';' then begin
             raise Exception.Create('Se esperaba ";" o una lista de parametros en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             exit;
          end;

          avanzaToken; // AVANZA AL PRIMER TOKEN DE LA EXPRESION
       end;
     {$endregion}

     // INSERTA EL PROCEDIMIENTO EN LA LISTA
     self.listaProcedimientos.Add(NProc);

     // INSERTA LA INFORMACION DE DEPURACION EN EL CODIGO
     resultadoCompilacion.InsertaComando(CMD_DEBUG,LIni,0,0);

     // HAY QUE INSERTAR COMO CODIGO EL QUE ESTA FUNCION LLAME A LA FUNCION REAL
     NProc^.xInfo:=resultadoCompilacion.PC;
     resultadoCompilacion.InsertaComando(CMD_JMP,0,0,0);

     // SI TERMINA CON UN PUNTO Y COMA AVANZA
     while tokenActual^.Token = ';' do begin
           avanzaToken;
     end;

end;

procedure TKCompilador.CompilaExpresion(varNameList: TList);
var
   NProc : PProc;
   LPC : integer;
   i : integer;
   indproc : integer;
   indNProc : integer;
begin
     // UNA EXPRESION TIENE LA SIGUIENTE SINTAXIS
     {
        Expresion :: = {
                          "apagate"
                          "gira-izquierda"
                          "avanza"
                          "coge-zumbador"
                          "deja-zumbador"
                          "sal-de-funcion"
                          ExpresionLlamada
                          ExpresionSi
                          ExpresionRepite
                          ExpresionMientras
                          "inicio"
                              ExpresionGeneral [";" ExpresionGeneral] ...
                          "fin"
                       }{

     }}
     if tokenActual^.Token = 'APAGATE' then begin
        resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
        resultadoCompilacion.InsertaComando(CMD_APAGATE,0,0,0);
        avanzaToken;
     end
     else if tokenActual^.Token = 'GIRA-IZQUIERDA' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_GIRAIZQ,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'AVANZA' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_AVANZA,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'COGE-ZUMBADOR' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_COGEZUM,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'DEJA-ZUMBADOR' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_DEJAZUM,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'SAL-DE-INSTRUCCION' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);

          if VarNameList <> nil then
             // LIMPIA LAS VARIABLES DEL STACK Y REGRESA
             for i:=0 to Pred(VarNameList.Count) do begin
                 resultadoCompilacion.InsertaComando(CMD_POP_FROMHEAP,0,0,0); // SACA LAS VARIABLES DEL STACK
             end;

          resultadoCompilacion.InsertaComando(CMD_RET,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'SI' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          CompilaExpresionSi(VarNameList);
     end
     else if tokenActual^.Token = 'MIENTRAS' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          CompilaExpresionMientras(VarNameList);
     end
     else if tokenActual^.Token = 'REPETIR' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          CompilaExpresionRepite(VarNameList);
     end
     else if tokenActual^.Token = 'INICIO' then begin
          avanzaToken;
          CompilaExpresionGeneral(VarNameList);
          if tokenActual^.Token = 'FIN' then begin
             resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
             avanzaToken;
          end
          else begin
               raise Exception.Create('Se esperaba "FIN" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else if tokenActual^.Tipo = TT_IDENTIFICADOR then begin
          // PUEDE SER UNA LLAMADA PERO UNICAMENTE SI EL IDENTIFICADOR ESTA DADO DE ALTA EN LA LISTA DE PROCEDIMIENTOS.
          NProc:=ObtenProcedimiento(tokenActual^.Token, indproc);
          if NProc <> nil then begin

             // OK, EL PROCEDIMIENTO EXISTE,
             resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);

             avanzaToken;
             // AHORA HAY QUE VER SI REQUIERE PARAMETROS

             // HAY QUE INSERTAR EN LA PILA LA DIRECCION DE REGRESO
             LPC:=resultadoCompilacion.PC;
             resultadoCompilacion.InsertaComando(CMD_PUSHPC);

             if NProc^.RequiereParam then begin
                // EL SIGUIENTE TOKEN DEBE SER UN PARENTESIS ABIERTO
                if tokenActual^.Token = '(' then begin
                   avanzaToken;
                   if tokenActual^.Token = ')' then begin
                      raise Exception.Create('Muy pocos parametros, se esperaba un valor numerico en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
                   end
                   else begin
                     CompilaExpresionEntera(VarNameList);
                     // METE LA VARIABLE AL STACK
                     resultadoCompilacion.InsertaComando(CMD_PUSHREG_TOHEAP);

                     // CHECA QUE CIERREN EL PARENTESIS
                     if tokenActual^.Token = ')' then begin
                        avanzaToken;
                     end
                     else begin
                          raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
                     end;
                   end;
                end
                else begin
                     raise Exception.Create('Muy pocos parametros, se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
                end;

                resultadoCompilacion.InsertaComando(CMD_PUSHPC,resultadoCompilacion.PC-LPC,0,0,LPC);

                // LLAMA AL PROCEDIMIENTO
                if infoDepuracion then
                   indNProc:=agregaConstanteALista('*' + NProc^.Nombre)
                else
                    indNProc:=$FFFF;
                resultadoCompilacion.InsertaComando(CMD_CALL,NProc^.Inicio,indproc,indNProc);
             end
             else begin


                  resultadoCompilacion.InsertaComando(CMD_PUSHPC,resultadoCompilacion.PC-LPC,0,0,LPC);

                  // LLAMA AL PROCEDIMIENTO
                  if infoDepuracion then
                     indNProc:=agregaConstanteALista(NProc^.Nombre)
                  else
                      indNProc:=$FFFF;
                  resultadoCompilacion.InsertaComando(CMD_CALL,NProc^.Inicio,indproc,indNProc);
             end;
          end
          else begin
               raise Exception.Create('El procedimiento ' + tokenActual^.Token + ' no ha sido previamente definido ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else begin
          raise Exception.Create('Se esperaba un procedimiento o comando en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.CompilaExpresionEntera(varNameList: TList);
var
   i : integer;
   OKvar : boolean;
begin
     // LA SINTAXIS DE UNA EXPRESION ENTERA ES
     {
        ExpresionEntera ::= { Decimal | Identificador | "PRECEDE" "(" ExpresionEntera ")" | "SUCEDE" "(" ExpresionEntera ")" }{
     }}
     if tokenActual^.Tipo = TT_NENTERO then begin
        resultadoCompilacion.InsertaComando(CMD_LOADREG,StrToInt(tokenActual^.Token),0,0);
        avanzaToken;
     end
     else if tokenActual^.Token = 'PRECEDE' then begin
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             CompilaExpresionEntera(varNameList);
             if tokenActual^.Token = ')' then begin
                resultadoCompilacion.InsertaComando(CMD_PRECEDE,0,0,0);
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end
          else begin
               raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else if tokenActual^.Token = 'SUCEDE' then begin
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             CompilaExpresionEntera(varNameList);
             if tokenActual^.Token = ')' then begin
                resultadoCompilacion.InsertaComando(CMD_SUCEDE,0,0,0);
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end
          else begin
               raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else if tokenActual^.Tipo = TT_IDENTIFICADOR then begin
          // SI ES UNA VARIABLE TIENE QUE ESTAR DENTRO DE LA LISTA DE VARIABLES
          OKvar:=false;
          for i:=0 to Pred(varNameList.Count) do begin
              if PVar(varNameList.Items[i])^.Nombre = tokenActual^.Token then begin
                 // ESTA ES LA VARIABLE, OBTEN SU POSICION EN EL STACK
                 OKVar:=true;
                 resultadoCompilacion.InsertaComando(CMD_LOADREG_FROMHEAP,PVar(varNameList.Items[i])^.Posicion + 1,0,0);
                 avanzaToken;
                 break;
              end;
          end;

          if Not(OKVar) then begin
             raise Exception.Create('El identificador ' + tokenActual^.Token + ' no ha sido definido.  Linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else begin
          raise Exception.Create('Se esperaba una ExpresionEntera en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.CompilaExpresionGeneral(varNameList: TList);
begin
     while (tokenActual^.Token <> 'FIN') and (tokenActual^.Token <> 'TERMINA-EJECUCION') do begin
          CompilaExpresion(varNameList);

          if (tokenActual^.Token <> ';') and (tokenActual^.Token <> 'FIN') and (tokenActual^.Token <> 'TERMINA-EJECUCION') then begin
             raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end
          else if tokenActual^.Token = ';' then begin
               // ES CORRECTO, AVANZA AL SIGUIENTE TOKEN
               avanzaToken;
          end;
     end;
end;

procedure TKCompilador.CompilaExpresionMientras(varNameList: TList);
var
   LIni : integer;
   LIf : integer;
begin
     // LA EXPRESION MIENTRAS TIENE LA SIGUIENTE SINTAXIS
     {
        ExpresionMientras ::= "Mientras" Termino "hacer"
                                  Expresion
     }
     if tokenActual^.Token <> 'MIENTRAS' then begin
        raise Exception.Create('Se esperaba "MIENTRAS" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;

     LIni:=resultadoCompilacion.PC; // GUARDA LA LINEA DONDE COMIENZA LA EXPRESION PARA DESPUES PODER VOLVER A ELLA

     avanzaToken; // AVANZA AL ULTIMO TERMINO
     CompilaTermino(varNameList);
     if tokenActual^.Token <> 'HACER' then begin
        raise Exception.Create('Se esperaba "HACER" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        exit;
     end;

     LIf:=resultadoCompilacion.PC; // AQUI VA IR EL SALTO
     resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,0,0,0); // SALTA SI EL TERMINO EVALUO A FALSO

     avanzaToken;
     CompilaExpresion(VarNameList);
     // AL FINAL DE LA EXPRESION DEBE SALTAR DE NUEVO AL INICIO
     resultadoCompilacion.InsertaComando(CMD_JMP,LIni,0,0);
     resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,LIf); // ACTUALIZA EL SALTO PARA SALIR DEL CICLO MIENTRAS
end;

procedure TKCompilador.CompilaExpresionRepite(varNameList: TList);
var
   LIni : integer;
   LIf : integer;
begin
     // LA ExpresionRepite TIENE LA SIGUIENTE SINTAXIS
     {
        ExpresionRepite::= "repetir" ExpresionEntera "veces"
                              Expresion
     }
     if tokenActual^.Token <> 'REPETIR' then begin
          raise Exception.Create('Se esperaba "REPETIR" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          exit;
     end;

     avanzaToken;
     CompilaExpresionEntera(VarNameList);

     if tokenActual^.Token <> 'VECES' then begin
        raise Exception.Create('Se esperaba "VECES" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        exit;
     end;

     // INSERTA INFORMACION DE DEPURACION
     LIni:=resultadoCompilacion.PC;

     // REVISA SI YA HIZO TODAS LAS VECES, SI NO
     LIf:=resultadoCompilacion.PC;
     resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,0,0,0);
     resultadoCompilacion.InsertaComando(CMD_PUSHREG_TOCOUNT,0,0,0);

     avanzaToken;
     CompilaExpresion(VarNameList);

     resultadoCompilacion.InsertaComando(CMD_POP_FROMCOUNT,0,0,0);
     resultadoCompilacion.InsertaComando(CMD_DECREG,0,0,0);
     resultadoCompilacion.InsertaComando(CMD_JMP,LIni,0,0);
     resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,LIf);
end;

procedure TKCompilador.CompilaExpresionSi(varNameList: TList);
var
   Lini, Lif, LElse : integer;
begin
     // UNA ExpresionSi TIENE LA SIGUIENTE SINTAXIS
     {
        ExpresionSi ::= "SI" Termino "ENTONCES"
                             Expresion
                        ["SINO"
                               Expresion
                        ]
     }

     if tokenActual^.Token <> 'SI' then begin
        raise Exception.Create('Se esperaba "SI" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        exit;
     end;

     LIni:=resultadoCompilacion.PC;
     // INSERTA INFORMACION DE DEPURACION

     avanzaToken;
     CompilaTermino(VarNameList);

     if tokenActual^.Token <> 'ENTONCES' then begin
        raise Exception.Create('Se esperaba "ENTONCES" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        exit;
     end;

     LIf:=resultadoCompilacion.PC;
     resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE);

     avanzaToken;
     CompilaExpresion(VarNameList);

     {$region ' Checa si hay un else '}
       if tokenActual^.Token = 'SINO' then begin
          LElse:=resultadoCompilacion.PC;
          resultadoCompilacion.InsertaComando(CMD_JMP); // SALTO PARA EVITAR LA PARTE DE CODIGO DEL ELSE

          resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,Lif); // SI EL TERMINO ES FALSO, SALTA AL ELSE

          avanzaToken;
          CompilaExpresion(VarNameList);

          // AL FINAL DE LA EXPRESION ACTUALIZA EL SALTO AL FINAL DEL IF
          resultadoCompilacion.InsertaComando(CMD_JMP,resultadoCompilacion.PC,0,0,LElse);
       end
       else begin
            // NO, BASTA CON SALTAR A LA SIGUIENTE LINEA
            resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,Lif);
       end;
     {$endregion}
end;

procedure TKCompilador.CompilaFuncionBooleana;
begin
     // UNA FuncionBooleana TIENE LA SIGUIENTE SINTAXIS
     {
        FuncionBooleana ::= {
                               "FRENTE-LIBRE"
                               "FRENTE-BLOQUEADO"
                               "DERECHA-LIBRE"
                               "DERECHA-BLOQUEADA"
                               "IZQUIERAD-LIBRE"
                               "IZQUIERDA-BLOQUEADA"
                               "JUNTO-A-ZUMBADOR"
                               "NO-JUNTO-A-ZUMBADOR"
                               "ALGUN-ZUMBADOR-EN-LA-MOCHILA"
                               "NINGUN-ZUMBADOR-EN-LA-MOCHILA"
                               "ORIENTADO-AL-NORTE"
                               "NO-ORIENTADO-AL-NORTE"
                               "ORIENTADO-AL-ESTE"
                               "NO-ORIENTADO-AL-ESTE"
                               "ORIENTADO-AL-SUR"
                               "NO-ORIENTADO-AL-SUR"
                               "ORIENTADO-AL-OESTE"
                               "NO-ORIENTADO-AL-OESTE"
                            }{
     }}

     if tokenActual^.Token = 'FRENTE-LIBRE' then begin
        resultadoCompilacion.InsertaComando(CMD_B_FL,0,0,0);
        avanzaToken;
     end
     else if tokenActual^.Token = 'FRENTE-BLOQUEADO' then begin
          resultadoCompilacion.InsertaComando(CMD_B_FB,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'DERECHA-LIBRE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_DL,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'DERECHA-BLOQUEADA' then begin
          resultadoCompilacion.InsertaComando(CMD_B_DB,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'IZQUIERDA-LIBRE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_IL,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'IZQUIERDA-BLOQUEADA' then begin
          resultadoCompilacion.InsertaComando(CMD_B_IB,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'JUNTO-A-ZUMBADOR' then begin
          resultadoCompilacion.InsertaComando(CMD_B_JAZ,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'NO-JUNTO-A-ZUMBADOR' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NJAZ,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'ALGUN-ZUMBADOR-EN-LA-MOCHILA' then begin
          resultadoCompilacion.InsertaComando(CMD_B_AZELM,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'NINGUN-ZUMBADOR-EN-LA-MOCHILA' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NZELM,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'ORIENTADO-AL-NORTE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAN,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'NO-ORIENTADO-AL-NORTE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAN,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'ORIENTADO-AL-ESTE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAE,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'NO-ORIENTADO-AL-ESTE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAE,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'ORIENTADO-AL-SUR' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAS,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'NO-ORIENTADO-AL-SUR' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAS,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'ORIENTADO-AL-OESTE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAO,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'NO-ORIENTADO-AL-OESTE' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAO,0,0,0);
          avanzaToken;
     end
     else begin
          raise Exception.Create('Se esperaba una FuncionBooleana en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.CompilaJBlock(varNameList: TList);
begin
     // JBlock ::= "{" [Statement]... "}"

     if tokenActual^.Token = '{' then begin
        avanzaToken;
        while tokenActual^.Token <> '}' do begin
              JCompilaStatement(varNameList);
        end;
        avanzaToken;
     end
     else begin
          raise Exception.Create('Se esperaba "{" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

function TKCompilador.compilaPrograma(unLenguage: integer;
  const unCodigo: AnsiString): TKProgramaCompilado;
var
   i, j : integer;
   a, b, c : WORD;
begin
     result:=TKProgramaCompilado.Create;
     result.infoDepuracion:=infoDepuracion;
     limpiaListas;
     codigo:=unCodigo;
     posicionCodigo:=1;
     lenguaje:=unLenguage;
     _lineaCodigo:=1;
     _posicionLineaCodigo:=1;
     longitudCodigo:=Length(_codigo);

     if (separaEnTokens <> RES_OK) or Not(Compila(result)) then begin
        result.Free;
        result:=nil;
     end;

     // AL FINAL DE LA COMPILACION AGREGA TODAS LAS CONSTANTES
     if infoDepuracion and (result <> nil) then begin
        for i:=0 to _listaDeConstantes.Count - 1 do begin
            j:=1;
            a:=i;
            b:=0;
            c:=0;
            while j <= Length(_listaDeConstantes[i]) do begin
                  case j mod 4 of
                       1 : begin
                         b:=b or (Ord(_listaDeConstantes[i][j]) shl 8);
                       end;
                       2 : begin
                         b:=b or Ord(_listaDeConstantes[i][j]);
                       end;
                       3 : begin
                         c:=c or (Ord(_listaDeConstantes[i][j]) shl 8);
                       end;
                       0 : begin
                         c:=c or Ord(_listaDeConstantes[i][j]);
                         result.InsertaComando(CMD_CONSTANTE,a,b,c);
                         b:=0;
                         c:=0;
                       end;
                  end;
                  Inc(j);
            end;
            if (b <> 0) or (c <> 0) then result.InsertaComando(CMD_CONSTANTE,a,b,c);
        end;
     end;
end;

procedure TKCompilador.CompilaTermino(varNameList: TList);
begin
     // UN TERMINO TIENE LA SIGUIENTE SINTAXIS
     {
        Termino ::= ClausulaY [ "o" ClausulaY] ...
     }
     CompilaClausulaY(varNameList);

     while tokenActual^.Token = 'O' do begin
           // SI HAY UN NUEVO TERMINO, METE EL ULTIMO RESULTADO A LA PILA
           resultadoCompilacion.InsertaComando(CMD_PUSHREG,0,0,0);
           resultadoCompilacion.SP:=resultadoCompilacion.SP + 1;

           avanzaToken;
           CompilaClausulaY(varNameList);
           // DESPUES DE COMPILAR EL TERMINO ANTERIOR, SACALO DE LA PILA Y HAZ LA OPERACION
           resultadoCompilacion.InsertaComando(CMD_POPORREG,0,0,0);
           resultadoCompilacion.SP:=resultadoCompilacion.SP - 1;
     end;
end;

constructor TKCompilador.Create;
begin
     listaTokens:=TList.Create;
     listaVariables:=TList.Create;
     listaProcedimientos:=TList.Create;
     listaProcedimientosJava:=TList.Create;
     _listaDeConstantes:=TStringList.Create;
end;

destructor TKCompilador.Destroy;
var
   i : integer;
begin
     // LIBERA LA MEMORIA DE LAS LISTAS
     limpiaListas;

     listaTokens.Free;
     listaVariables.Free;
     listaProcedimientos.Free;
     listaProcedimientosJava.Free;

     _listaDeConstantes.Free;

     inherited;
end;

function TKCompilador.EliminaEspaciosEnBlanco: integer;
begin
     // AVANZA HASTA ENCONTRAR EL PRIMER CARACTER DIFERENTE DE ESPACIO EN BLANCO
     result:=RES_ERR;
     while posicionCodigo <= longitudCodigo do begin
           if codigo[posicionCodigo] in chrEspacioEnBlanco then begin
              if codigo[posicionCodigo] = #10 then begin
                 lineaCodigo:=lineaCodigo + 1;
                 posicionLineaCodigo:=0;
              end;
              posicionCodigo:=posicionCodigo + 1;
              posicionLineaCodigo:=posicionLineaCodigo + 1;
           end
           else begin
                result:=RES_OK;
                break;
           end;
     end;
end;

procedure TKCompilador.JCompilaClase;
var
   njcp : PJcallProc;
begin

     {Clase ::= "{"
            [DeclaracionDeMetodo ] ...
            DeclaracionDeConstructor
            [DeclaracionDeMetodo ] ...
            "}{"
     }}

     // EL PROGRAMA SIEMPRE VA A INICIAR EN LA DIRECCION CERO, POR LO QUE DEBE SALTAR DE INICIO A LA DIRECCION DE LA RUTINA PRINCIPAL
     resultadoCompilacion.InsertaComando(CMD_PUSHPC,1);
     resultadoCompilacion.InsertaComando(CMD_JMP,3);
     resultadoCompilacion.InsertaComando(CMD_APAGATE);
     resultadoCompilacion.InsertaComando(CMD_CALL,0,$FFFF,$FFFF);
     New(njcp);
     njcp^.ProcName:=JNameProg;
     njcp^.CallLine:=3;
     listaProcedimientosJava.Add(njcp);

     if tokenActual^.Token = '{' then begin
        avanzaToken;
        while (tokenActual^.Token = 'void') or (tokenActual^.Token = 'define') or (tokenActual^.Token = JNameProg) do begin
              if tokenActual^.Token = JNameProg then begin
                 JCompilaDeclaracionDeMetodo;
              end
              else begin
                   avanzaToken;
                   JCompilaDeclaracionDeMetodo;
              end;
        end;
        if tokenActual^.Token = '}' then begin
           resultadoCompilacion.InsertaComando(CMD_EOP);
        end
        else begin
             raise Exception.Create('Se esperaba "}" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
     end
     else begin
          raise Exception.Create('Se esperaba "{" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaClausulaAtomica(varNameList: TList);
begin
     if tokenActual^.Token = 'iszero' then begin
        avanzaToken;
        if tokenActual^.Token = '(' then begin
           avanzaToken;
           JCompilaExpresionEntera(varNameList);
           if tokenActual^.Token = ')' then begin
              // INSERTA EL COMANDO DE EVALUACION
              resultadoCompilacion.InsertaComando(CMD_SIESCERO,0,0,0);
              avanzaToken;
              // AVANZA AL SIGUIENTE TOKEN
           end
           else begin
                raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
           end;
        end
        else begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
     end
     else if tokenActual^.Token = '(' then begin
          avanzaToken;
          JCompilaTermino(varNameList);
          if tokenActual^.Token = ')' then begin
             // sintaxis correcta, avanza al siguiente token
             avanzaToken;
          end
          else begin
               raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else begin
          JCompilaFuncionBooleana;
     end;
end;

procedure TKCompilador.JCompilaClausulaNo(varNameList: TList);
begin
     if tokenActual^.Token = '!' then begin
        avanzaToken;
        JCompilaClausulaAtomica(varNameList);
        // AHORA NIEGA EL RESULTADO
        resultadoCompilacion.InsertaComando(CMD_NOTREG,0,0,0);
     end
     else begin
          JCompilaClausulaAtomica(varNameList);
     end;
end;

procedure TKCompilador.JCompilaClausulaY(varNameList: TList);
begin
     JCompilaClausulaNo(varNameList);

     while tokenActual^.Token = '&&' do begin
           // SI HAY UN NUEVO TERMINO, METE EL ULTIMO RESULTADO A LA PILA
           resultadoCompilacion.InsertaComando(CMD_PUSHREG,0,0,0);

           // AUMENTA EN UNO EL OFFSET DE LAS VARIABLES
           resultadoCompilacion.SP:=resultadoCompilacion.SP + 1;

           avanzaToken;
           JCompilaClausulaNo(varNameList);
           // AL TERMINAR LA CLAUSULA NO SACALA DE LA PILA
           resultadoCompilacion.InsertaComando(CMD_POPANDREG,0,0,0);

           // DECREMENTA EL OFFSET DE LAS VARIABLES
           resultadoCompilacion.SP:=resultadoCompilacion.SP - 1;
     end;

end;

procedure TKCompilador.JCompilaDeclaracionDeMetodo;
var
   NProc : PProc;
   VarNameList : TList;
   Lini, i : integer;
   NVar : PVar;
   njcp : PJCallProc;
begin
     {
        DeclaracionDeMetodo ::= Identificador "(" [Identificador] ")" bloque
     }
     if tokenActual^.Tipo = TT_IDENTIFICADOR then begin
        // ESTE ES EL NOMBRE DEL PROCEDIMIENTO, HAY QUE METERLO A LA LISTA DE PROCEDIMIENTOS
        LIni:=tokenActual^.Linea; // GUARDA INFORMACION PARA DEPURACION

        // CREA EL NUEVO PROCEDIMIENTO
        New(NProc);
        NProc^.Nombre:=tokenActual^.Token;
        NProc^.Inicio:=resultadoCompilacion.PC;

        // INSERTA LA INFORMACION DE DEPURACION EN EL CODIGO
        resultadoCompilacion.InsertaComando(CMD_DEBUG,LIni,0,0);

        // INICIALIZA LA LISTA DE VARIABLES LOCALES
        VarNameList:=TList.Create;

        // DE UN IDENTIFICADOR PUEDE SEGUIR UNA LISTA DE PARAMETROS
        avanzaToken;
        if tokenActual^.Token = '(' then begin
           avanzaToken;

           // Crea la lista de variables
           if tokenActual^.Tipo = TT_IDENTIFICADOR then begin
              NProc^.RequiereParam:=true;

              New(NVar);
              NVar^.Nombre:=tokenActual^.Token;
              NVar^.Posicion:=resultadoCompilacion.SP;
              VarNameList.Add(NVar); // GUARDA EL NOMBRE DE LA VARIABLE PARA INDEXARLO

              avanzaToken;
           end
           else begin
                NProc^.RequiereParam:=false;
           end;

           if tokenActual^.Token = ')' then begin
              avanzaToken;
           end
           else begin
                raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
           end;

           // INSERTA EL PROCEDIMIENTO
           listaProcedimientos.Add(NProc);

           // AQUI HAY QUE COMPILAR LA EXPRESION DEL PROCEDIMIENTO
           CompilaJBlock(VarNameList);

           // LIMPIA LAS VARIABLES DEL STACK Y REGRESA
           for i:=0 to Pred(VarNameList.Count) do begin
               resultadoCompilacion.InsertaComando(CMD_POP_FROMHEAP,0,0,0); // SACA LAS VARIABLES DEL STACK
           end;
           resultadoCompilacion.InsertaComando(CMD_RET,0,0,0); // TERMINA EL PROCEDIMIENTO;

        end
        else begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
     end
     else begin
          raise Exception.Create('Se esperaba un identificador en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaExpresionEntera(varNameList: TList);
var
   i : integer;
   OKVar : boolean;
begin
     if tokenActual^.Tipo = TT_NENTERO then begin
        resultadoCompilacion.InsertaComando(CMD_LOADREG,StrToInt(tokenActual^.Token),0,0);
        avanzaToken;
     end
     else if tokenActual^.Token = 'pred' then begin
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             JCompilaExpresionEntera(varNameList);
             if tokenActual^.Token = ')' then begin
                resultadoCompilacion.InsertaComando(CMD_PRECEDE,0,0,0);
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end
          else begin
               raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else if tokenActual^.Token = 'succ' then begin
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             JCompilaExpresionEntera(varNameList);
             if tokenActual^.Token = ')' then begin
                resultadoCompilacion.InsertaComando(CMD_SUCEDE,0,0,0);
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end
          else begin
               raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else if tokenActual^.Tipo = TT_IDENTIFICADOR then begin
          // SI ES UNA VARIABLE TIENE QUE ESTAR DENTRO DE LA LISTA DE VARIABLES
          OKvar:=false;
          for i:=0 to Pred(varNameList.Count) do begin
              if PVar(varNameList.Items[i])^.Nombre = tokenActual^.Token then begin
                 // ESTA ES LA VARIABLE, OBTEN SU POSICION EN EL STACK
                 OKVar:=true;
                 resultadoCompilacion.InsertaComando(CMD_LOADREG_FROMHEAP,PVar(varNameList.Items[i])^.Posicion + 1,0,0);
                 avanzaToken;
                 break;
              end;
          end;

          if Not(OKVar) then begin
             raise Exception.Create('El identificador ' + tokenActual^.Token + ' no ha sido definido.  Linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else begin
          raise Exception.Create('Se esperaba una ExpresionEntera en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaExpresionMientras(varNameList: TList);
var
   LIni : integer;
   LIf : integer;
begin
     // LA EXPRESION MIENTRAS TIENE LA SIGUIENTE SINTAXIS
     {
        ExpresionMientras ::= "Mientras" Termino "hacer"
                                  Expresion
     }
     if tokenActual^.Token = 'while' then begin
        LIni:=resultadoCompilacion.PC; // GUARDA LA LINEA DONDE COMIENZA LA EXPRESION PARA DESPUES PODER VOLVER A ELLA
        avanzaToken; // AVANZA AL ULTIMO TERMINO

        if tokenActual^.Token <> '(' then begin
           raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
        avanzaToken;

        JCompilaTermino(varNameList);
        if tokenActual^.Token = ')' then begin
           LIf:=resultadoCompilacion.PC; // AQUI VA IR EL SALTO
           resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,0,0,0); // SALTA SI EL TERMINO EVALUO A FALSO

           avanzaToken;
           JCompilaStatement(VarNameList);
           // AL FINAL DE LA EXPRESION DEBE SALTAR DE NUEVO AL INICIO
           resultadoCompilacion.InsertaComando(CMD_JMP,LIni,0,0);
           resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,LIf); // ACTUALIZA EL SALTO PARA SALIR DEL CICLO MIENTRAS
        end
        else begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
     end
     else begin
          raise Exception.Create('Se esperaba "while" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaExpresionRepite(varNameList: TList);
var
   LIni : integer;
   LIf : integer;
begin
     // LA ExpresionRepite TIENE LA SIGUIENTE SINTAXIS
     {
        ExpresionRepite::= "repetir" ExpresionEntera "veces"
                              Expresion
     }
     if tokenActual^.Token = 'iterate' then begin
        avanzaToken;

        if tokenActual^.Token <> '(' then begin
           raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
        avanzaToken;

        JCompilaExpresionEntera(VarNameList);

        if tokenActual^.Token = ')' then begin
           // INSERTA INFORMACION DE DEPURACION
           LIni:=resultadoCompilacion.PC;

           // REVISA SI YA HIZO TODAS LAS VECES, SI NO
           LIf:=resultadoCompilacion.PC;
           resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,0,0,0);
           resultadoCompilacion.InsertaComando(CMD_PUSHREG_TOCOUNT,0,0,0);

           avanzaToken;
           JCompilaStatement(VarNameList);

           resultadoCompilacion.InsertaComando(CMD_POP_FROMCOUNT,0,0,0);
           resultadoCompilacion.InsertaComando(CMD_DECREG,0,0,0);
           resultadoCompilacion.InsertaComando(CMD_JMP,LIni,0,0);
           resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,LIf);
        end
        else begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
     end
     else begin
          raise Exception.Create('Se esperaba "iterate" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaExpresionSi(varNameList: TList);
var
   Lini, Lif, LElse : integer;
begin
     // UNA ExpresionSi TIENE LA SIGUIENTE SINTAXIS
     {
        ExpresionSi ::= "SI" Termino "ENTONCES"
                             Expresion
                        ["SINO"
                               Expresion
                        ]
     }
     if tokenActual^.Token = 'if' then begin
        LIni:=resultadoCompilacion.PC;
        avanzaToken;

        if tokenActual^.Token <> '(' then begin
           raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
        avanzaToken;

        JCompilaTermino(VarNameList);

        if tokenActual^.Token = ')' then begin
           LIf:=resultadoCompilacion.PC;
           resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE);

           avanzaToken;
           JCompilaStatement(VarNameList);

           // VEAMOS SI HAY UN 'ELSE'
           if tokenActual^.Token = 'else' then begin
              LElse:=resultadoCompilacion.PC;
              resultadoCompilacion.InsertaComando(CMD_JMP); // SALTO PARA EVITAR LA PARTE DE CODIGO DEL ELSE

              resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,Lif); // SI EL TERMINO ES FALSO, SALTA AL ELSE

              avanzaToken;
              JCompilaStatement(VarNameList);

              // AL FINAL DE LA EXPRESION ACTUALIZA EL SALTO AL FINAL DEL IF
              resultadoCompilacion.InsertaComando(CMD_JMP,resultadoCompilacion.PC,0,0,LElse);

           end
           else begin
                // NO, BASTA CON SALTAR A LA SIGUIENTE LINEA
                resultadoCompilacion.InsertaComando(CMD_JMPREGFALSE,resultadoCompilacion.PC,0,0,Lif);
           end;
        end
        else begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
     end
     else begin
          raise Exception.Create('Se esperaba "if" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaFuncionBooleana;
begin
     if tokenActual^.Token = 'frontIsClear' then begin
        resultadoCompilacion.InsertaComando(CMD_B_FL,0,0,0);
        avanzaToken;
        if tokenActual^.Token = '(' then begin
           avanzaToken;
           if tokenActual^.Token = ')' then begin
              avanzaToken;
           end
           else begin
                raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
           end;
        end;
     end
     else if tokenActual^.Token = 'frontIsBlocked' then begin
          resultadoCompilacion.InsertaComando(CMD_B_FB,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'rightIsClear' then begin
          resultadoCompilacion.InsertaComando(CMD_B_DL,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'rightIsBlocked' then begin
          resultadoCompilacion.InsertaComando(CMD_B_DB,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'leftIsClear' then begin
          resultadoCompilacion.InsertaComando(CMD_B_IL,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'leftIsBlocked' then begin
          resultadoCompilacion.InsertaComando(CMD_B_IB,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'nextToABeeper' then begin
          resultadoCompilacion.InsertaComando(CMD_B_JAZ,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'notNextToABeeper' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NJAZ,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'anyBeepersInBeeperBag' then begin
          resultadoCompilacion.InsertaComando(CMD_B_AZELM,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'noBeepersInBeeperBag' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NZELM,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'facingNorth' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAN,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'notFacingNorth' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAN,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'facingEast' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAE,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'notFacingEast' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAE,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'facingSouth' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAS,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'notFacingSouth' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAS,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'facingWest' then begin
          resultadoCompilacion.InsertaComando(CMD_B_OAO,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else if tokenActual^.Token = 'notFacingWest' then begin
          resultadoCompilacion.InsertaComando(CMD_B_NOAO,0,0,0);
          avanzaToken;
          if tokenActual^.Token = '(' then begin
             avanzaToken;
             if tokenActual^.Token = ')' then begin
                avanzaToken;
             end
             else begin
                  raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
             end;
          end;
     end
     else begin
          raise Exception.Create('Se esperaba una FuncionBooleana en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaStatement(varNameList: TList);
var
   NProc : PProc;
   LPC : integer;
   njcp : PJCallProc;
   i : integer;
begin
     if tokenActual^.Token = 'turnoff' then begin
        avanzaToken;
        if tokenActual^.Token <> '(' then begin
           raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
        avanzaToken;
        if tokenActual^.Token <> ')' then begin
           raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
        avanzaToken;
        if tokenActual^.Token <> ';' then begin
           raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
        end;
        resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
        resultadoCompilacion.InsertaComando(CMD_APAGATE,0,0,0);
        avanzaToken;
     end
     else if tokenActual^.Token = 'turnleft' then begin
          avanzaToken;
          if tokenActual^.Token <> '(' then begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ')' then begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ';' then begin
             raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_GIRAIZQ,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'move' then begin
          avanzaToken;
          if tokenActual^.Token <> '(' then begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ')' then begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ';' then begin
             raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_AVANZA,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'pickbeeper' then begin
          avanzaToken;
          if tokenActual^.Token <> '(' then begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ')' then begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ';' then begin
             raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_COGEZUM,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'putbeeper' then begin
          avanzaToken;
          if tokenActual^.Token <> '(' then begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ')' then begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ';' then begin
             raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          resultadoCompilacion.InsertaComando(CMD_DEJAZUM,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'return' then begin
          avanzaToken;
          if tokenActual^.Token <> '(' then begin
             raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ')' then begin
             raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          avanzaToken;
          if tokenActual^.Token <> ';' then begin
             raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          if VarNameList <> nil then
             // LIMPIA LAS VARIABLES DEL STACK Y REGRESA
             for i:=0 to Pred(VarNameList.Count) do begin
                 resultadoCompilacion.InsertaComando(CMD_POP_FROMHEAP,0,0,0); // SACA LAS VARIABLES DEL STACK
             end;

          resultadoCompilacion.InsertaComando(CMD_RET,0,0,0);
          avanzaToken;
     end
     else if tokenActual^.Token = 'if' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          JCompilaExpresionSi(VarNameList);
     end
     else if tokenActual^.Token = 'while' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          JCompilaExpresionMientras(VarNameList);
     end
     else if tokenActual^.Token = 'iterate' then begin
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);
          JCompilaExpresionRepite(VarNameList);
     end
     else if tokenActual^.Token = ';' then begin
          avanzaToken;
     end
     else if tokenActual^.Token = '{' then begin
          CompilaJBlock(VarNameList);
//          if tokenActual.Token = '}' then begin
//             resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual.Linea);
//             avanzaToken;
//          end
//          else begin
//               raise Exception.Create('Se esperaba "}" en la linea ' + IntToStr(tokenActual.Linea) + ' posicion ' + IntToStr(tokenActual.Posicion));
//          end;
     end
     else if tokenActual^.Tipo = TT_IDENTIFICADOR then begin
          // A DIFERENCIA DE PASCAL, AQUI NO CONOCEMOS CON ANTERIORIDAD TODOS LOS PROCEDIMIENTOS, ASI
          // QUE VAMOS A CONFIAR EN QUE ESTE DEFINIDO.

          // HAY QUE GUARDARLO PARA PODER ACTUALIZAR LA LLAMADA LUEGO
          New(njcp);
          njcp^.ProcName:=tokenActual^.Token;

          // OK, EL PROCEDIMIENTO EXISTE,
          resultadoCompilacion.InsertaComando(CMD_DEBUG,tokenActual^.Linea);

          avanzaToken;
          // AHORA HAY QUE VER SI REQUIERE PARAMETROS

          // HAY QUE INSERTAR EN LA PILA LA DIRECCION DE REGRESO
          LPC:=resultadoCompilacion.PC;
          resultadoCompilacion.InsertaComando(CMD_PUSHPC);

          if tokenActual^.Token = '(' then begin
             avanzaToken;

             if tokenActual^.Token = ')' then begin

                // NO TIENE PARAMETROS;

                  avanzaToken;
                  if tokenActual^.Token = ';' then begin
                     njcp^.param := false;
                     avanzaToken;
                  end
                  else begin
                       raise Exception.Create('Se esperaba ";" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
                  end;

             end
             else begin

                  // REQUIERE PARAMETROS
                  JCompilaExpresionEntera(VarNameList);

                  // METE LA VARIABLE AL STACK
                  resultadoCompilacion.InsertaComando(CMD_PUSHREG_TOHEAP);

                  // CHECA QUE CIERREN EL PARENTESIS
                  if tokenActual^.Token = ')' then begin
                     njcp^.param := true;
                     avanzaToken;
                  end
                  else begin
                       raise Exception.Create('Se esperaba ")" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
                  end;

             end;

             resultadoCompilacion.InsertaComando(CMD_PUSHPC,resultadoCompilacion.PC-LPC,0,0,LPC);

             // LLAMA AL PROCEDIMIENTO
             njcp^.CallLine:=resultadoCompilacion.PC;
             resultadoCompilacion.InsertaComando(CMD_CALL,0,$FFFF,$FFFF);
             listaProcedimientosJava.Add(njcp);
          end
          else begin
               raise Exception.Create('Se esperaba "(" en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
          end;
     end
     else begin
          raise Exception.Create('Se esperaba un procedimiento o comando en la linea ' + IntToStr(tokenActual^.Linea) + ' posicion ' + IntToStr(tokenActual^.Posicion));
     end;
end;

procedure TKCompilador.JCompilaTermino(varNameList: TList);
begin
     JCompilaClausulaY(varNameList);

     while tokenActual^.Token = '||' do begin
           // SI HAY UN NUEVO TERMINO, METE EL ULTIMO RESULTADO A LA PILA
           resultadoCompilacion.InsertaComando(CMD_PUSHREG,0,0,0);
           resultadoCompilacion.SP:=resultadoCompilacion.SP + 1;

           avanzaToken;
           JCompilaClausulaY(varNameList);
           // DESPUES DE COMPILAR EL TERMINO ANTERIOR, SACALO DE LA PILA Y HAZ LA OPERACION
           resultadoCompilacion.InsertaComando(CMD_POPORREG,0,0,0);
           resultadoCompilacion.SP:=resultadoCompilacion.SP - 1;
     end;

end;

procedure TKCompilador.limpiaListas;
var
   i : integer;
begin
     for i:=0 to Pred(listaTokens.Count) do begin
         Dispose(PVar(listaTokens.Items[i]));
     end;
     listaTokens.Clear;

     for i:=0 to Pred(listaVariables.Count) do begin
         Dispose(PVar(listaVariables.Items[i]));
     end;
     listaVariables.Clear;

     for i:=0 to Pred(listaProcedimientos.Count) do begin
         Dispose(PProc(listaProcedimientos.Items[i]));
     end;
     listaProcedimientos.Clear;

     for i:=0 to Pred(listaProcedimientosJava.Count) do begin
         Dispose(PJCallProc(listaProcedimientosJava.Items[i]));
     end;
     listaProcedimientosJava.Clear;

     _listaDeConstantes.Clear;
end;

function TKCompilador.ObtenCadenaHasta(FinCad: string;
  UnaLinea: boolean): AnsiString;
var
   res : AnsiString;
   i : integer;
   match : boolean;
   lFinCad : integer;
   saltosDeLinea, posicionLineaTmp : integer;
begin
     // ESTA FUNCION OBTIENE UNA CADENA DE CUALQUIER TIPO DE CARACTER HASTA LA CADENA DE FINAL
     // PUEDE SER EN UNA LINEA O VARIAS
     res:='';
     lFinCad:=Length(FinCad);
     match:=false;

     while Not(match) do begin
        while Not(match) and (posicionCodigo + lFinCad - 1 <= longitudCodigo) do begin
           // TODAVIA CABE LA CADENA, CHECA SI ESTA.
           match:=true;
           saltosDeLinea:=0;
           posicionLineaTmp:=posicionLineaCodigo;
           for i:=1 to lFinCad do begin
               if (codigo[posicionCodigo + i - 1] <> FinCad[i]) or (unaLinea and (codigo[posicionCodigo + i - 1] = #10)) then begin
                  match:=false;
                  break;
               end
               else if codigo[posicionCodigo + i - 1] = #10 then begin
                    Inc(saltosDeLinea);
                    posicionLineaTmp:=1;
               end
               else
                   Inc(posicionLineaTmp);
           end;

           if Not(match) then begin
              // SI NO FUE MATCH AGREGA A LA CADENA DE RESULTADO
              res:=res + codigo[posicionCodigo];

              if unaLinea and (codigo[posicionCodigo + i - 1] = #10) then
                 break;
           end;

           posicionCodigo:=posicionCodigo + 1;
           if codigo[posicionCodigo] = #10 then begin
              posicionLineaCodigo:=0;
              lineaCodigo:=lineaCodigo + 1;
           end;
           posicionLineaCodigo:=posicionLineaCodigo + 1;
        end;

        // SI TERMINO, HAY QUE VER PORQUE FUE
        if match then begin
           // ENCONTRO UN MATCH, AVANZA EL CONTADOR DE POSICION Y TERMINA
           posicionCodigo:=posicionCodigo + lFinCad;
           posicionLineaCodigo:=posicionLineaTmp;
           lineaCodigo:=lineaCodigo + saltosDeLinea;
        end
        else begin
             // SE TERMINO LA LINEA HAY QUE VER SI ERA DE UNA UNICA LINEA
             if UnaLinea then begin
                res:='ERR';
                exit;
             end;
        end;
     end;

     result:=res;
end;

function TKCompilador.ObtenIdentificador: integer;
var
   res : integer;
   NToken : PToken;
begin
     // UN IDENTIFICADOR ES FACIL DE OBTENER YA QUE SON LETRAS SEGUIDAS DE NUMEROS HASTA UN ESPACIO O SEPARADOR
     New(NToken);
     NToken^.Tipo:=TT_IDENTIFICADOR;
     NToken^.Linea:=lineaCodigo;
     NToken^.Posicion:=posicionLineaCodigo;
     NToken^.Token:='';

     res:=RES_OK;

     while (posicionCodigo < longitudCodigo) and ((codigo[posicionCodigo] in ChrLetras) or
                                                  (codigo[posicionCodigo] in ChrNumeros) or
                                                  (codigo[posicionCodigo] = '-')) do begin
           if Lenguaje = LANG_PASCAL then begin
              NToken^.Token:=NToken^.Token + UpCase(codigo[posicionCodigo]);
           end
           else begin
                NToken^.Token:=NToken^.Token + codigo[posicionCodigo];
           end;
           posicionCodigo:=posicionCodigo + 1;
           posicionLineaCodigo:=posicionLineaCodigo + 1;
     end;

     // INSERTA EL TOKEN
     listaTokens.Add(NToken);

     result:=RES;
end;

function TKCompilador.ObtenNumero: integer;
var
   res : integer;
   NToken : PToken;
begin
     New(NToken);
     NToken^.Tipo:=TT_NENTERO;
     NToken^.Linea:=lineaCodigo;
     NToken^.Posicion:=posicionLineaCodigo;
     NToken^.Token:='';

     res:=RES_OK;

     while (posicionCodigo < longitudCodigo) and (codigo[posicionCodigo] in ChrNumeros) do begin
           NToken^.Token:=NToken^.Token + codigo[posicionCodigo];
           posicionCodigo:=posicionCodigo + 1;
           posicionLineaCodigo:=posicionLineaCodigo + 1;
     end;

     // TERMINANDO EL NUMERO HAY QUE VER SI ES ENTERO
     if posicionCodigo <= longitudCodigo then begin
        if codigo[posicionCodigo] = '.' then begin
           NToken^.Tipo:=TT_NREAL;
           NToken^.Token:=NToken^.Token + '.';
           posicionCodigo:=posicionCodigo + 1;
           posicionLineaCodigo:=posicionLineaCodigo + 1;
           while (posicionCodigo <= longitudCodigo) and (codigo[posicionCodigo] in ChrNumeros) do begin
                 NToken^.Token:=NToken^.Token + codigo[posicionCodigo];
                 posicionCodigo:=posicionCodigo + 1;
                 posicionLineaCodigo:=posicionLineaCodigo + 1;
           end;
        end;
     end;

     // INSERTA EL TOKEN
     listaTokens.Add(NToken);

     result:=RES;
end;

function TKCompilador.ObtenProcedimiento(ProcName: string;
  var index: integer): PProc;
var
   i : integer;
   res : PProc;
begin
     res:=nil;
     index:=-1;

     // BUSCA EN LA LISTA DE PROCEDIMIENTOS
     for i:=0 to Pred(listaProcedimientos.Count) do begin
         if PProc(listaProcedimientos.Items[i])^.Nombre = ProcName then begin
            res:=PProc(listaProcedimientos.Items[i]);
            index:=i;
            break;
         end;
     end;

     // BUSCA EN LA LISTA DE PROTOTIPOS
     if index = -1 then begin
        ProcName:=ProcName + '_prototype_';
        for i:=0 to Pred(listaProcedimientos.Count) do begin
            if PProc(listaProcedimientos.Items[i])^.Nombre = ProcName then begin
               res:=PProc(listaProcedimientos.Items[i]);
               index:=i;
               break;
            end;
        end;
     end;

     result:=res;
end;

function TKCompilador.ObtenSeparador: integer;
var
   NToken : PToken;
begin
     // ESTA FUNCION OBTIENE UN TOKEN SEPARADOR Y LO PONE EN LA LISTA DE TOKENS
     New(NToken);
     NToken^.Linea:=lineaCodigo;
     NToken^.Posicion:=posicionLineaCodigo;
     NToken^.Tipo:=TT_SEPARADOR;
     NToken^.SubClase:=0;
     case codigo[posicionCodigo] of
          ';' : begin
                     NToken^.Token:=';';
                end;
          ',' : begin
                     NToken^.Token:=',';
                end;
          '.' : begin
                     if posicionCodigo < longitudCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '.' then begin
                           // ES UN COMENTARIO
                           // HAY QUE AVANZAR HASTA EL FINAL DEL COMENTARIO
                           NToken^.Token:='..';
                           posicionCodigo:=posicionCodigo + 1;
                           posicionLineaCodigo:=posicionLineaCodigo + 1;
                        end
                        else begin
                             NToken^.Token:='.';
                        end;
                     end
                     else begin
                          NToken^.Token:='.';
                     end;
                end;
          '(' : begin
                     // PUEDE SER SEPARADOR DE COMENTARIO
                     if posicionCodigo < longitudCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '*' then begin
                           // ES UN COMENTARIO
                           // HAY QUE AVANZAR HASTA EL FINAL DEL COMENTARIO
                           posicionCodigo:=posicionCodigo + 1;
                           posicionLineaCodigo:=posicionLineaCodigo + 1;
                           NToken^.Tipo:=TT_COMENTARIO;
                           NToken^.Token:=ObtenCadenaHasta('*)',false);
                           if NToken^.Token = 'ERR' then begin
                              // SI REGRESO ERROR QUIERE DECIR QUE EL COMENTARIO ESTA SIN TERMINAR
                              raise Exception('Comentario sin terminar iniciado en la linea ' + IntToStr(NToken^.Linea) + ' posicion ' + IntToStr(NToken^.Posicion));
                           end;
                        end
                        else begin
                             NToken^.Token:='(';
                        end;
                     end
                     else begin
                          NToken^.Token:='(';
                     end;
                end;
          ')' : begin
                     NToken^.Token:=')';
                end;
          '[' : begin
                     NToken^.Token:='[';
                end;
          ']' : begin
                     NToken^.Token:=']';
                end;
          ':' : begin
                     // PUEDE SER TAMBIEN UN OPERADOR DE ASIGNACION
                     if posicionCodigo < longitudCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '=' then begin
                           // ES UN COMENTARIO
                           // HAY QUE AVANZAR HASTA EL FINAL DEL COMENTARIO
                           NToken^.Tipo:=TT_OPERADOR;
                           NToken^.Token:=':=';
                           posicionCodigo:=posicionCodigo + 1;
                           posicionLineaCodigo:=posicionLineaCodigo + 1;
                        end
                        else begin
                             NToken^.Token:=':';
                        end;
                     end
                     else begin
                          NToken^.Token:=':';
                     end;
                end;
          '&' : begin
                     // PUEDE SER TAMBIEN UN OPERADOR DE ASIGNACION
                     if posicionCodigo < longitudCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '&' then begin
                           // ES UN COMENTARIO
                           // HAY QUE AVANZAR HASTA EL FINAL DEL COMENTARIO
                           NToken^.Tipo:=TT_OPERADOR;
                           NToken^.Token:='&&';
                           posicionCodigo:=posicionCodigo + 1;
                           posicionLineaCodigo:=posicionLineaCodigo + 1;
                        end
                        else begin
                             NToken^.Token:='&';
                        end;
                     end
                     else begin
                          NToken^.Token:='&';
                     end;
                end;
          '|' : begin
                     // PUEDE SER TAMBIEN UN OPERANDO OR
                     if posicionCodigo < longitudCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '|' then begin
                           // ES UN COMENTARIO
                           // HAY QUE AVANZAR HASTA EL FINAL DEL COMENTARIO
                           NToken^.Tipo:=TT_OPERADOR;
                           NToken^.Token:='||';
                           posicionCodigo:=posicionCodigo + 1;
                           posicionLineaCodigo:=posicionLineaCodigo + 1;
                        end
                        else begin
                             NToken^.Token:='|';
                        end;
                     end
                     else begin
                          NToken^.Token:='|';
                     end;
                end;
          '!' : begin
                     NToken^.token:='!';
                end;
          '{' : begin
                     NToken^.Token:='{';
                end;
          '}' : begin
                     NToken^.Token:='}';
                end;
          #39 : begin
                     posicionCodigo:=posicionCodigo + 1;
                     posicionLineaCodigo:=posicionLineaCodigo + 1;
                     NToken^.Tipo:=TT_CADENA;
                     NToken^.Token:=ObtenCadenaHasta(#39,true);
                end;
          '<' : begin
                     NToken^.Tipo:=TT_OPERADOR;
                     if posicionCodigo < longitudCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '>' then begin
                           NToken^.Token:='<>';
                           posicionCodigo:=posicionCodigo + 1;
                           posicionLineaCodigo:=posicionLineaCodigo + 1;
                        end
                        else if codigo[posicionCodigo + 1] = '=' then begin
                             NToken^.Token:='<=';
                             posicionCodigo:=posicionCodigo + 1;
                             posicionLineaCodigo:=posicionLineaCodigo + 1;
                        end
                        else begin
                             NToken^.Token:='<';
                        end;
                     end
                     else begin
                          NToken^.Token:='<';
                     end;
                end;
          '>' : begin
                     NToken^.Tipo:=TT_OPERADOR;
                     if posicionCodigo < lineaCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '=' then begin
                           NToken^.Token:='>=';
                           posicionCodigo:=posicionCodigo + 1;
                           posicionLineaCodigo:=posicionLineaCodigo + 1;
                        end
                        else begin
                             NToken^.Token:='>';
                        end;
                     end
                     else begin
                          NToken^.Token:='>';
                     end;
                end;
          '=' : begin
                     NToken^.Tipo:=TT_OPERADOR;
                     NToken^.Token:='=';
                end;
          '+' : begin
                     NToken^.Tipo:=TT_OPERADOR;
                     NToken^.Token:='+';
                end;
          '-' : begin
                     NToken^.Tipo:=TT_OPERADOR;
                     NToken^.Token:='-';
                end;
          '*' : begin
                     NToken^.Tipo:=TT_OPERADOR;
                     NToken^.Token:='*';
                end;
          '^' : begin
                     NToken^.Tipo:=TT_OPERADOR;
                     NToken^.Token:='^';
                end;
          '/' : begin
                     // PUEDE SER TAMBIEN UN COMENTARIO
                     if posicionCodigo < longitudCodigo then begin
                        // SI CABE, HAZ LA PRUEBA
                        if codigo[posicionCodigo + 1] = '/' then begin
                           // ES UN COMENTARIO
                           // HAY QUE AVANZAR HASTA EL FINAL DEL COMENTARIO
                           NToken^.Tipo:=TT_Comentario;
                           NToken^.Token:=obtenCadenaHasta(#10,true);
                        end
                        else if codigo[posicionCodigo + 1] = '*' then begin
                             NToken^.Tipo:=TT_COMENTARIO;
                             NToken^.Token:=ObtenCadenaHasta('*/',false);
                             if NToken^.Token = 'ERR' then begin
                                // SI REGRESO ERROR QUIERE DECIR QUE EL COMENTARIO ESTA SIN TERMINAR
                                raise Exception.Create('Comentario sin terminar iniciado en la linea ' + IntToStr(NToken^.Linea) + ' posicion ' + IntToStr(NToken^.Posicion));
                             end;
                        end
                        else begin
                             NToken^.Tipo:=TT_OPERADOR;
                             NToken^.Token:='/';
                        end;
                     end
                     else begin
                          NToken^.Tipo:=TT_OPERADOR;
                          NToken^.Token:='/';
                     end;
                end;
          else begin
               // FUE UN OPERADOR DESCONOCIDO
               raise Exception('Se detecto caracter invalido en la linea ' + IntToStr(lineaCodigo + 1) + ' posicion ' + IntToStr(posicionLineaCodigo));
          end;
     end;
     // INSERTA EL TOKEN SIEMPRE Y CUANDO NO SEA UN COMENTARIO
     if NToken^.Tipo <> TT_COMENTARIO then begin
        listaTokens.Add(NToken);
     end;

     // AVANZA EL CURSOR
     posicionCodigo:=posicionCodigo + 1;
     posicionLineaCodigo:=posicionLineaCodigo + 1;
end;

function TKCompilador.retrocedeToken: integer;
begin
     indiceTokenActual:=indiceTokenActual - 1;
     if indiceTokenActual >= 0 then begin
        Result:=RES_OK;
     end
     else begin
          indiceTokenActual:=0;
          Result:=RES_ERR;
     end;
end;

function TKCompilador.SeparaEnTokens: integer;
var
   res : integer;
   NToken : PToken;
   EOFToken : boolean;
   caracterActual : char;
begin
     // SEPARA EL CODIGO EN TOKENS PARA PODER CHECAR LA GRAMATICA Y COMPILAR
     res:=RES_OK;
     EOFToken:=false;

     while Not(EOFToken) do begin
           try
              if EliminaEspaciosEnBlanco = RES_OK then begin
                 caracterActual:=codigo[posicionCodigo];
                 if caracterActual in ChrSeparadores then begin
                    ObtenSeparador;
                 end
                 else if caracterActual in ChrLetras then begin
                      ObtenIdentificador;
                 end
                 else if caracterActual in ChrNumeros then begin
                      ObtenNumero;
                 end
                 else if caracterActual = #39 then begin
                      New(NToken);
                      NToken^.Tipo:=TT_CADENA;
                      NToken^.Linea:=lineaCodigo;
                      NToken^.Posicion:=posicionLineaCodigo;
                      NToken^.Token:=ObtenCadenaHasta(#39,true);
                 end
              else begin
                   raise Exception('Se detecto el caracter invalido ' + codigo[posicionCodigo] + ' en la linea ' + IntToStr(lineaCodigo) +
                           ' posicion ' + IntToStr(posicionLineaCodigo));
              end;
           end
           else begin
                // SI DEVUELVE ERROR EN ELIMINAESPACIOSENBLANCO ES PORQUE LLEGO AL FINAL DEL ARCHIVO
                EOFToken:=true;
           end;
        except
              On E : Exception do begin
                 res:=RES_ERR;
                 ErrMsg:=E.Message;
                 EOFToken:=true;
              end;
        end;
     end;

     result:=res;
end;

function TKCompilador.tokenActual: PToken;
begin
     result:=PToken(listaTokens.Items[indiceTokenActual]);
end;

end.
