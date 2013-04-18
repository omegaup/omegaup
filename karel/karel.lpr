program karel;

uses
  cthreads, cmem,
  Classes, SysUtils,
  NativeXml,
  UKMundo,
  UKEntorno,
  UKProgramaCompilado;

const
     version = '1.0';

     // MAXIMOS
     MAXENTORNOS = 100;

     // TIPOS DE EJECUCION
     TE_PASOAPASO         = 1;
     TE_BREAKPOINT        = 2;
     TE_CONTINUA          = 3;

type
    ThEjecucion = class (TThread)
    private
      procedure ejecutaPasoEntornos;
    public
      procedure Execute; override;
    end;

    ThLecturaComandos = class (TThread)
    public
      procedure Execute; override;
    end;

var
   xmlEsp : TNativeXML;
   nodo, nodoa, nodob : TXMLNode;

   i, j, k, x, y : integer;
   stCmd, stNombre, st : AnsiString;
   stParams : array [1..50] of string;
   _finEjecucion : boolean = false;
   breakPoint : boolean = false;
   continuaEjecucion : boolean = false;
   desatendido : boolean = false;
   tmpIn, tmpOut : Text;

   mundo : TKMundo;
   programa : TKProgramaCompilado;
   entorno : TKEntornoEjecucion;

   // VARIABLES PARA PARAMETROS
   _archivoXMLCondiciones : string;
   _archivoResultado : string = '';
   _verbose : boolean = false;

   // VARIABLES PARA CONDICIONES DE EJECUCION
   _tamStack : integer;
   _maxInstrucciones : integer;
   _maxEjecucionesComando : array [MINCMD..MAXCMD] of integer;
   _tipoEjecucion, _instruccionesCambioContexto, _msPasoAutomatico : integer;
   _sTipoEjecucion : string;
   slTemporal : TStringList;

   // LISTAS PARA CONTENER MUNDOS Y PROGRAMAS
   _listaMundos, _listaEntornos : TStringList;

   // VARIABLES PARA LEER MUNDOS Y PROGRAMAS
   _nombre, _ruta : string;
   _xKarel, _yKarel, _iDirKarel : integer;
   _dirKarel, _mochilaKarel : string;
   _tipoDespliega : string;

   // VARIABLES PARA EJECUCION
   hiloEjecucion : ThEjecucion;
   hiloLecturaComandos : ThLecturaComandos;

   // VARIABLES PARA DUMP
   dumpMundo : boolean = false;
   dumpPosicionKarel : boolean = false;
   dumpDireccionKarel : boolean = false;
   dumpInstrucciones : boolean = false;


procedure escribeBitacora(const msg : string);
begin
     if _verbose then
        writeln(msg);
end;


procedure ejecuta;
begin
     hiloEjecucion:=ThEjecucion.Create(true);
     hiloEjecucion.FreeOnTerminate:=true;
     hiloEjecucion.Resume;

     if not desatendido then begin
          hiloLecturaComandos:=ThLecturaComandos.Create(true);
          hiloLecturaComandos.FreeOnTerminate:=true;
          hiloLecturaComandos.Resume;
     end;
end;

{ ThEjecucion }

procedure ThEjecucion.ejecutaPasoEntornos;
var
   i, j : integer;
   entorno : TKEntornoEjecucion;
   resEntornos : array [0..MAXENTORNOS] of integer;
begin
     for i:=0 to _listaEntornos.Count - 1 do begin
         entorno:=TKEntornoEjecucion(_listaEntornos.Objects[i]);
         for j:=1 to _instruccionesCambioContexto do begin
             resEntornos[i]:=entorno.ejecutaComando;
             if resEntornos[i] <> RESEJE_OK then break;

             if entorno.breakPoint then begin
                entorno.breakPoint:=false;
                breakPoint:=true;
             end;
         end;
     end;

     for i:=0 to _listaEntornos.Count - 1 do begin
         if resEntornos[i] <> RESEJE_OK then begin
            _finEjecucion:=true;
            break;
         end;
     end;
end;

procedure ThEjecucion.Execute;
var
   i : integer;
   xml : TNativeXML;
   nodo : TXMLNode;
   entorno : TKEntornoEjecucion;
begin
     repeat
           case _tipoEjecucion of
                TE_PASOAPASO : begin
                end;
                TE_BREAKPOINT : begin
                  ejecutaPasoEntornos;
                  if breakPoint then begin
                     breakPoint:=false;
                     xml:=TNativeXML.Create(nil);
                     xml.Root.Name:='estado';

                     for i:=0 to _listaEntornos.Count - 1 do begin
                         entorno:=TKEntornoEjecucion(_listaEntornos.Objects[i]);
                         nodo:=xml.Root.NodeNew('programa');
                         nodo.writeAttributeString('nombre',_listaEntornos[i]);
                         entorno.dumpEstadoEntorno(nodo,BD_KAREL or BD_STACK or BD_CALLSTACK);
                     end;

                     xml.XmlFormat:=xfReadable;
                     writeln(xml.WriteToString);
                     xml.Free;

                     while not(continuaEjecucion) do sleep(100);
                     continuaEjecucion:=false;
                  end;
                end;
                TE_CONTINUA : begin
                  ejecutaPasoEntornos;
                end;
           end;
     until _finEjecucion;
end;

procedure hazDumpResultados(const archivo : string);
var
   xml : TNativeXML;
   nodo, nodoa : TXMLNode;
   i, j : integer;
   mundo : TKMundo;
   entorno : TKEntornoEjecucion;
   banderasDump : integer;
begin
     try
        xml:=TNativeXML.Create(nil);
        try
           xml.XmlFormat:=xfReadable;
           xml.Root.Name:='resultados';

           if dumpMundo then begin
              nodo:=xml.Root.NodeNew('mundos');

              for i:=0 to _listaMundos.Count - 1 do begin
                  mundo:=TKMundo(_listaMundos.Objects[i]);
                  mundo.escribeAXML(nodo,true,_listaMundos[i],true);
              end;
           end;

           nodo:=xml.Root.NodeNew('programas');
           for i:=0 to _listaEntornos.Count - 1 do begin
               entorno:=TKEntornoEjecucion(_listaEntornos.Objects[i]);
               nodoa:=nodo.NodeNew('programa');
               nodoa.writeAttributeString('nombre',_listaEntornos[i]);

               banderasDump:=0;
               if dumpPosicionKarel then banderasDump:=banderasDump or BD_POSICIONKAREL;
               if dumpDireccionKarel then banderasDump:=banderasDump or BD_DIRECCIONKAREL;
               if dumpInstrucciones then banderasDump:=banderasDump or BD_CUENTAINSTRUCCIONES;
               entorno.dumpEstadoEntorno(nodoa,banderasDump);
           end;

           if _verbose or (_archivoResultado = '') then begin
              writeln(xml.WriteToString);
           end;
           if _archivoResultado <> '' then begin
              xml.SaveToFile(_archivoResultado);
           end;
        finally
               xml.Free;
        end;
     except
           On E : Exception do begin
              writeln(StdErr, 'ERROR|Error al hacer dump de resultados: ' + E.Message);
           end;
     end;
end;

{ ThLecturaComandos }

procedure ThLecturaComandos.Execute;
var
   st : string;
begin
     while not(terminated) do begin
           readln(st);
           if (st = 'c') or (st = 'continua') then begin
              continuaEjecucion:=true;
           end;
     end;
end;

begin
  { TODO -oUser -cConsole Main : Insert code here }
     {TODO : Despues de cada ejecucion, valida que no se hayan excedido las condiciones de ejecucion}

     _listaMundos:=TStringList.Create;
     _listaMundos.sorted:=true;

     _listaEntornos:=TStringList.Create;
     _listaEntornos.Sorted:=true;

     // LEE LAS VARIABLES DE LOS PARAMETROS
     _archivoXMLCondiciones:=ParamStr(1);
     i:=2;
     while i <= ParamCount do begin
         stCmd:=ParamStr(i);
         if stCmd = '-o' then begin
            Inc(i);
            _archivoResultado:=ParamStr(i);
         end
         else if stCmd = '-v' then begin
              _verbose:=true;
         end
         else if stCmd = '-q' then begin
              desatendido:=true;
         end
         else if stCmd = '-om' then begin
              dumpMundo:=false;
         end
         else if stCmd = '-opk' then begin
              dumpPosicionKarel:=false;
         end
         else if stCmd = '-odk' then begin
              dumpDireccionKarel:=false;
         end
         else if stCmd = '-oi' then begin
              dumpInstrucciones:=false;
         end
         else if stCmd = '-p1' then begin
              Inc(i);
              stParams[1]:=ParamStr(i);
         end
         else if stCmd = '-p2' then begin
              Inc(i);
              stParams[2]:=ParamStr(i);
         end
         else if stCmd = '-p3' then begin
              Inc(i);
              stParams[3]:=ParamStr(i);
         end
         else if stCmd = '-p4' then begin
              Inc(i);
              stParams[4]:=ParamStr(i);
         end
         else if stCmd = '-p5' then begin
              Inc(i);
              stParams[5]:=ParamStr(i);
         end
         else if stCmd = '-p6' then begin
              Inc(i);
              stParams[6]:=ParamStr(i);
         end
         else if stCmd = '-p7' then begin
              Inc(i);
              stParams[7]:=ParamStr(i);
         end
         else if stCmd = '-p8' then begin
              Inc(i);
              stParams[8]:=ParamStr(i);
         end
         else if stCmd = '-p9' then begin
              Inc(i);
              stParams[9]:=ParamStr(i);
         end;
         Inc(i);
     end;

     if not desatendido then begin
        // SACA LA FIRMA
        writeln('KAREL OS Version - ' + version);
        writeln('');
     end;

     // LEE EL XML DE ESPECIFICACIONES
     try
        xmlEsp:=TNativeXML.Create(nil);
        try
           // SUSTITUYE LAS VARIABLES
           Assign(tmpIn, _archivoXMLCondiciones);
           Reset(tmpIn);
           Assign(tmpOut, '__input.tmp');
           Rewrite(tmpOut);
           while not eof(tmpIn) do begin
                 Readln(tmpIn,st);

                 for i:=1 to 9 do begin
                     st := StringReplace(st,'{$' + IntToStr(i) + '$}',stParams[i],[rfReplaceAll]);
                 end;

                 writeln(tmpOut, st);
           end;

           Close(tmpIn);
           Close(tmpOut);

           xmlEsp.LoadFromFile('__input.tmp');

           // LEE LAS CONDICIONES DE EJECUCION
           nodo:=xmlEsp.Root.NodeByName('condiciones');
           if nodo <> nil then begin
              escribeBitacora('LEYENDO condiciones ...');

              _tamStack:=nodo.ReadAttributeInteger('longitudStack',10000);
              escribeBitacora('... longitud stack: ' + IntToStr(_tamStack));

              _maxInstrucciones:=nodo.ReadAttributeInteger('instruccionesMaximasAEjecutar',10000000);
              escribeBitacora('... maximo de instrucciones a ejecutar: ' + IntToStr(_maxInstrucciones));
              
              for i:=MINCMD to MAXCMD do _maxEjecucionesComando[i]:=_maxInstrucciones;
              
              for i:=0 to nodo.NodeCount - 1 do begin
                  nodoa:=nodo.Nodes[i];
                  if nodoa.Name = 'comando' then begin
                     stCmd:=UpperCase(nodoa.ReadAttributeString('nombre',''));
                     k:=nodoa.ReadAttributeInteger('maximoNumeroDeEjecuciones',0);
                     if (stCmd <> '') and (k <> 0) then begin
                        for j:=MINCMD to MAXCMD do begin
                            if CMD_NOMBRES[j] = stCmd then begin
                               _maxEjecucionesComando[j]:=k;
                               escribeBitacora('... maximo de ejecuciones para el comando ' + stCmd + ': ' + IntToStr(k));
                               break;
                            end;
                        end;
                     end;
                  end;
              end;
           end
           else begin
                escribeBitacora('LEYENDO condiciones ... DEFAULT');
                _tamStack:=10000;
                _maxInstrucciones:=10000000;
                for i:=MINCMD to MAXCMD do _maxEjecucionesComando[i]:=_maxInstrucciones;
           end;

           // LEE Y CARGA LOS MUNDOS
           nodo:=xmlEsp.Root.NodeByName('mundos');
           if nodo <> nil then begin
              escribeBitacora('LEYENDO mundos ...');
              for i:=0 to nodo.NodeCount - 1 do begin
                  nodoa:=nodo.Nodes[i];
                  if nodoa.name = 'mundo' then begin
                     _nombre:=nodoa.ReadAttributeString('nombre','');
                     if (_nombre <> '') then begin
                        mundo:=TKMundo.Create;
                        if mundo.leeDeNodo(nodoa) then begin
                           _listaMundos.AddObject(_nombre,mundo);

                           for j:=0 to nodoa.NodeCount - 1 do begin
                               nodob:=nodoa.nodes[j];
                               if nodob.name = 'posicionDump' then begin
                                  x:=nodob.ReadAttributeInteger('x',-1);
                                  y:=nodob.ReadAttributeInteger('y',-1);
                                  if (x <> -1) and (y <> -1) then begin
                                     mundo.dumpSelectivo:=true;
                                     mundo.agregaDumpSelectivo(x,y);
                                  end;
                               end;
                           end;

                           escribeBitacora('... se agrego mundo ' + _nombre);
                        end
                        else begin
                             mundo.Free;
                             escribeBitacora('... desechando mundo ' + _nombre + ' porque el formato del archivo es invalido');
                        end;
                     end
                     else begin
                          escribeBitacora('... desechando mundo ' + _nombre + ' por datos invalidos (' + _nombre + ',' + _ruta + ')');
                     end;
                  end;
              end;
           end;

           if _listaMundos.Count = 0 then begin
              writeln(StdErr, 'ERROR|No se especifico ningun mundo valido');
              halt;
           end;

           // LEE Y CARGA LOS PROGRAMAS
           nodo:=xmlEsp.Root.NodeByName('programas');
           if nodo <> nil then begin
              escribeBitacora('LEYENDO programas ...');

              _sTipoEjecucion:=nodo.ReadAttributeString('tipoEjecucion','CONTINUA');
              if _sTipoEjecucion = 'PASO_A_PASO' then _tipoEjecucion:=TE_PASOAPASO
              else if _sTipoEjecucion = 'BREAKPOINT' then _tipoEjecucion:=TE_BREAKPOINT
              else _tipoEjecucion:=TE_CONTINUA;

              _instruccionesCambioContexto:=nodo.ReadAttributeInteger('instruccionesCambioContexto',1);
              _msPasoAutomatico:=nodo.ReadAttributeInteger('milisegundosParaPasoAutomatico',0);

              escribeBitacora('... Tipo de ejecucion: ' + _sTipoEjecucion);
              escribeBitacora('... Instrucciones ejecutadas cada cambio de contexto: ' + IntToStr(_instruccionesCambioContexto));
              escribeBitacora('... Milisegundos para dar un paso automatico: ' + IntToStr(_msPasoAutomatico));

              for i:=0 to nodo.NodeCount - 1 do begin
                  nodoa:=nodo.Nodes[i];
                  if nodoa.name = 'programa' then begin
                     _nombre:=nodoa.ReadAttributeString('nombre','');
                     _ruta:=nodoa.ReadAttributeString('ruta','');
                     if (_nombre <> '') and FileExists(_ruta) then begin
                        programa:=TKProgramaCompilado.Create;
                        if programa.leeDeArchivo(_ruta) then begin
                           stNombre:=nodoa.ReadAttributeString('mundoDeEjecucion','');
                           if (stNombre <> '') and _listaMundos.Find(stNombre,j) then begin
                              mundo:=TKMundo(_listaMundos.Objects[j]);
                              _xKarel:=nodoa.ReadAttributeInteger('xKarel',1);
                              _yKarel:=nodoa.ReadAttributeInteger('yKarel',1);
                              _dirKarel:=nodoa.ReadAttributeString('direccionKarel','NORTE');
                              _mochilaKarel:=nodoa.ReadAttributeString('mochilaKarel','0');

                              if _dirKarel = 'SUR' then _iDirKarel:=DIR_SUR
                              else if _dirKarel = 'OESTE' then _iDirKarel:=DIR_OESTE
                              else if _dirKarel = 'ESTE' then _iDirKarel:=DIR_ESTE
                              else _iDirKarel:=DIR_NORTE;

                              for j:=0 to nodoa.NodeCount - 1 do begin
                                  nodob:=nodoa.Nodes[j];
                                  if nodob.Name = 'breakpoint' then begin
                                     k:=nodob.ReadAttributeInteger('linea',-1);
                                     if k <> -1 then
                                        programa.ToggleBreakPoint(k);
                                  end else if nodob.Name = 'despliega' then begin
                                      _tipoDespliega:=nodob.ReadAttributeString('tipo');
                                      if _tipoDespliega = 'MUNDO' then begin
                                          dumpMundo:=true;
                                      end else if _tipoDespliega = 'POSICION' then begin
                                          dumpPosicionKarel:=true;
                                      end else if _tipoDespliega = 'ORIENTACION' then begin
                                          dumpDireccionKarel:=true;
                                      end else if _tipoDespliega = 'INSTRUCCIONES' then begin
                                          dumpInstrucciones:=true;
                                      end;
                                  end;
                              end;

                              entorno:=TKEntornoEjecucion.Create(programa,mundo,_xKarel,_yKarel,
                                                                 _iDirKarel,StrToIntDef(_mochilaKarel,$FFFF),
                                                                 _tamStack, _maxInstrucciones, _maxEjecucionesComando);
                              _listaEntornos.AddObject(_nombre,entorno);
                              escribeBitacora('... se agrego entorno de ejecucion para programa ' + _nombre);
                           end
                           else begin
                                programa.Free;
                                escribeBitacora('... desechando programa ' + _nombre + ' porque el mundo especificado no existe');
                           end;

                        end
                        else begin
                             programa.Free;
                             escribeBitacora('... desechando programa ' + _nombre + ' porque el formato del archivo es invalido');
                        end;
                     end
                     else begin
                          escribeBitacora('... desechando programa ' + _nombre + ' por datos invalidos');
                     end;
                  end;
              end;
           end;

           if _listaEntornos.Count = 0 then begin
              writeln(StdErr, 'ERROR|No se especifico ningun programa valido');
              halt;
           end;

           // INICIA LA EJECUCION
           ejecuta;

           hiloEjecucion.WaitFor();
           if not desatendido then begin
              hiloLecturaComandos.WaitFor();
           end;

           writeln(StdErr, 'OK|' + descEjecucion(TKEntornoEjecucion(_listaEntornos.Objects[0]).resultadoUltimaEjecucion));
           hazDumpResultados(_archivoResultado);

        finally
               xmlEsp.Free;
        end;
     except
           On E : Exception do begin
              writeln(StdErr, 'ERROR|Error al cargar XML de especificaciones (' + E.Message + ')');
           end;
     end;
end.


