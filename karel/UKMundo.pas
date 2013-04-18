unit UKMundo;

interface

uses
    Classes, SysUtils,
    NativeXML, FastStrings;

const
     ALTOMAXIMO = 256;
     ANCHOMAXIMO = 256;

     DIR_HORIZONTAL  = 0;
     DIR_VERTICAL    = 1;

     DIR_NORTE   = 0;
     DIR_ESTE    = 1;
     DIR_SUR     = 2;
     DIR_OESTE   = 3;

     PARED_NORTE = 1;
     PARED_ESTE  = 2;
     PARED_SUR   = 4;
     PARED_OESTE = 8;


type
    TfcbCambioMundo = procedure (x, y, cambio : integer);

    TKCasillaMundo = record
      case Integer of
           0 : (valor : Cardinal);
           1 : (zumbadores : WORD);
           2 : (pRes : WORD;
                paredes : BYTE);
           3 : (x : WORD; y : WORD);
    end;


    TKMundo = class
    private

      _errMsg : string;
      _fcbCambioZumbador, _fcbCambioPared : TfcbCambioMundo;

      _alto, _ancho : integer;
      _mundo : array [0..ANCHOMAXIMO + 1,0..ALTOMAXIMO + 1] of TKCasillaMundo;

      _dumpSelectivo : boolean;
      _arrayDumpSelectivo : array [0..ANCHOMAXIMO + 1,0..ALTOMAXIMO + 1] of boolean;

      _slErrores : TStringList;

      function getParedesEnCoordenada(cX, cY: integer): integer; // OK
      function getZumbadoresEnCoordenada(cX, cY: integer): integer; // OK
      procedure setZumbadoresEnCoordenada(cX, cY: integer; const Value: integer); // OK
      function getListaErrores: string; // OK
      function getNumErrores: integer; // OK

    public
      constructor Create;
      destructor Destroy; override;

      function leeDeArchivo(const archivo : string) : boolean;
      function leeDeNodo(raiz : TXMLNode) : boolean;
      function escribeAXML(nodo : TXMLNode; compresionDeCeros : boolean = true; const nombre : string = ''; usaDumpSelectivo : boolean = false) : boolean;

      procedure limpiaMundo; // OK
      procedure limpiaErrores;

      function agregaPared(xI, yI : integer; xF : integer = -1; yF : integer = -1) : boolean; // OK
      function agregaLinea(xI, yI, xF, yF : integer) : boolean;
      function agregaDumpSelectivo(x, y : integer) : boolean;

      function cogeZumbador(x, y : integer; cuantos : integer = 1; contabiliza : boolean = true) : boolean; // OK
      function dejaZumbador(x, y : integer; cuantos : integer = 1) : boolean; // OK
      function avanceValido(xI, yI, direccion : integer) : boolean; // OK

      procedure agregaAErrores(const msg : string);

      property alto : integer read _alto;
      property ancho : integer read _ancho;
      property errMsg : string read _errMsg write _errMsg;
      property cambioPared : TfcbCambioMundo read _fcbCambioPared write _fcbCambioPared;
      property cambioZumbador : TfcbCambioMundo read _fcbCambioZumbador write _fcbCambioZumbador;
      property zumbadoresEnCoordenada[cX, cY : integer] : integer read getZumbadoresEnCoordenada write setZumbadoresEnCoordenada;
      property paredesEnCoordenada[cX, cY : integer] : integer read getParedesEnCoordenada;
      property listaErrores : string read getListaErrores;
      property numeroDeErrores : integer read getNumErrores;
      property dumpSelectivo : boolean read _dumpSelectivo write _dumpSelectivo;
    end;

    {TODO : Seria bueno llevar un control de cambios}

implementation

{ TKMundo }

procedure TKMundo.agregaAErrores(const msg: string);
begin
     _slErrores.Add(msg);
end;

function TKMundo.agregaDumpSelectivo(x, y: integer): boolean;
begin
     _arrayDumpSelectivo[x,y]:=true;
end;

function TKMundo.agregaLinea(xI, yI, xF, yF: integer): boolean;
var
   i, j, dir, inicio, fin : integer;
begin
     result:=false;

     // OBTEN LA DIRECCION
     if xI = xF then begin
        dir:=DIR_VERTICAL;
        if yI > yF then begin
           inicio:=yF;
           fin:=yI;
        end
        else begin
             inicio:=yI;
             fin:=yF;
        end;
     end
     else if yI = yF then begin
          dir:=DIR_HORIZONTAL;
          if xI > xF then begin
             inicio:=xF;
             fin:=xI;
          end
          else begin
               inicio:=xI;
               fin:=xF;
          end;
     end
     else begin
          exit;
     end;

     // CONSTRUYE LA PARED
     result:=true;
     case dir of
          DIR_VERTICAL : begin
            j:=xI + 1;
            for i:=inicio + 1 to fin do begin
                _mundo[xI,i].paredes:=_mundo[xI,i].paredes or PARED_ESTE;
                _mundo[j,i].paredes:=_mundo[j,i].paredes or PARED_OESTE;
            end;
          end;

          DIR_HORIZONTAL : begin
            j:=yI + 1;
            for i:=inicio + 1 to fin do begin
                _mundo[i,yI].paredes:=_mundo[i,yI].paredes or PARED_NORTE;
                _mundo[i,j].paredes:=_mundo[i,j].paredes or PARED_SUR;
            end;
          end;
     end;
end;

function TKMundo.agregaPared(xI, yI : integer; xF : integer = -1; yF : integer = -1) : boolean;
var
   i, j, dir, inicio, fin : integer;

begin
     result:=false;

     if xF = -1 then xF:=xI;
     if yF = -1 then yF:=yI;

     if (xI = xF) or (yI = yF) then begin
        result:=agregaLinea(xI,yI,xF,yF);
     end
     else begin
          result:=agregaLinea(xI,yI,xI,yF) and agregaLinea(xI,yF,xF,yF);
     end;
end;

function TKMundo.avanceValido(xI, yI, direccion: integer): boolean;
var
   p : integer;
begin
     p:=paredesEnCoordenada[xI,yI];
     if p <> -1 then begin
        case direccion of
             DIR_NORTE : result:=(p and PARED_NORTE) = 0;
             DIR_ESTE : result:=(p and PARED_ESTE) = 0;
             DIR_SUR : result:=(p and PARED_SUR) = 0;
             DIR_OESTE : result:=(p and PARED_OESTE) = 0;
             else
                 result:=false;
        end;
     end
     else
         result:=false;
end;

function TKMundo.cogeZumbador(x, y: integer; cuantos : integer = 1; contabiliza : boolean = true): boolean;
var
   z : integer;
begin
     z:=zumbadoresEnCoordenada[x,y];
     if z >= cuantos then begin
        result:=true;
        if contabiliza then
           zumbadoresEnCoordenada[x,y]:=z - cuantos;
     end
     else
         result:=false;
end;

constructor TKMundo.Create;
begin
     _slErrores:=TStringList.Create;
     _dumpSelectivo:=false;
end;

function TKMundo.dejaZumbador(x, y: integer; cuantos : integer = 1): boolean;
begin
     if (x > 0) and (y > 0) and (x <= ancho) and (y <= alto) then begin
        _mundo[x,y].zumbadores:=_mundo[x,y].zumbadores + cuantos;
        result:=true;
     end
     else
         result:=false;
end;

destructor TKMundo.Destroy;
begin
     _slErrores.Free;
     inherited;
end;

function TKMundo.escribeAXML(nodo : TXMLNode; compresionDeCeros : boolean = true; const nombre : string = ''; usaDumpSelectivo : boolean = false): boolean;
var
   nodoMundo, nodoLinea : TXMLNode;
   i, j, z : integer;
   sLinea : string;
   ponCoordenada : boolean;
begin
     // ESCRIBE A UN XML EL RESULTADO DE LOS ZUMBADORES EN UN MUNDO
     nodoMundo:=nodo.NodeNew('mundo');
     nodoMundo.WriteAttributeString('nombre',nombre);
     for j:=alto downto 1 do begin
         ponCoordenada:=true;
         sLinea:='';
         for i:=1 to ancho do begin
             if not(usaDumpSelectivo) or (dumpSelectivo and _arrayDumpSelectivo[i,j]) then begin
                 z:=zumbadoresEnCoordenada[i,j];
                 if compresionDeCeros then begin
                    if z <> 0 then begin
                       if ponCoordenada then begin
                          sLinea:=sLinea + '(' + IntToStr(i) + ') ';
                       end;
                       sLinea:=sLinea + IntToStr(z) + ' ';
                    end;
                    ponCoordenada:=z = 0;
                 end
                 else begin
                      sLinea:=sLinea + IntToStr(z) + ' ';
                 end;
             end;
         end;

         if sLinea <> '' then begin
            nodoLinea:=nodoMundo.NodeNew('linea');
            nodoLinea.Value:=sLinea;
            nodoLinea.WriteAttributeInteger('fila',j);
            nodoLinea.WriteAttributeBool('compresionDeCeros',compresionDeCeros);
         end;
     end;
end;

function TKMundo.getListaErrores: string;
begin
     result:=_slErrores.Text;
end;

function TKMundo.getNumErrores: integer;
begin
     result:=_slErrores.Count;
end;

function TKMundo.getParedesEnCoordenada(cX, cY: integer): integer;
begin
     if (cX > 0) and (cY > 0) and (cX <= ancho) and (cY <= alto) then begin
        result:=_mundo[cX,cY].paredes;
     end
     else
         result:=-1;
end;

function TKMundo.getZumbadoresEnCoordenada(cX, cY: integer): integer;
begin
     if (cX > 0) and (cY > 0) and (cX <= ancho) and (cY <= alto) then begin
        result:=_mundo[cX,cY].zumbadores;
     end
     else
         result:=-1;
end;


function TKMundo.leeDeArchivo(const archivo: string): boolean;
var
   xml : TNativeXML;
   nodoa : TXMLNode;
   i, j, x1, y1, x2, y2, xo, yo, z : integer;
   sNombreNodo : string;
   sLista, sTupla : string;
   APos, BPos : integer;
   abierta : boolean;
begin
     result:=false;
     if Not(FileExists(archivo)) then begin
        errMsg:='No fue posible encontrar el archivo de mundo (' + archivo + ')';
        exit;
     end;

     try
        xml:=TNativeXML.Create(nil);
        try
           xml.LoadFromFile(archivo);
           result := leeDeNodo(xml.Root);
        finally
               xml.Free;
        end;
     except
           On E : Exception do begin
              errMsg:='Error al cargar archivo de mundo (' + E.Message + ')';
           end;
     end;
end;


function TKMundo.leeDeNodo(raiz: TXMLNode): boolean;
var
   xml : TNativeXML;
   nodoa : TXMLNode;
   i, j, x1, y1, x2, y2, xo, yo, z : integer;
   sNombreNodo : string;
   sLista, sTupla : string;
   APos, BPos : integer;
   abierta : boolean;
begin
     result:=false;

     try
        // LEE LAS DIMENSIONES
        _ancho:=raiz.ReadAttributeInteger('ancho',100);
        _alto:=raiz.ReadAttributeInteger('alto',100);
        if (ancho < 1) or (ancho > ANCHOMAXIMO) then begin
           errMsg:='El ancho del mundo debe ser un valor entre 1 y ' + IntToStr(ANCHOMAXIMO);
           exit;
        end;

        if (alto < 1) or (alto > ALTOMAXIMO) then begin
           errMsg:='El alto del mundo debe ser un valor entre 1 y ' + IntToStr(ALTOMAXIMO);
           exit;
        end;

           // INICIALIZA EL MARCO
           limpiaMundo;
           agregaPared(0,0,0,alto);
           agregaPared(0,alto,ancho,alto);
           agregaPared(ancho,alto,ancho,0);
           agregaPared(ancho,0,0,0);

           // LEE EL MUNDO.
           for i:=0 to raiz.NodeCount - 1 do begin
               nodoa:=raiz.Nodes[i];
               sNombreNodo:=UpperCase(nodoa.Name);
               if sNombreNodo = 'MONTON' then begin
                  // PONE UN MONTON DE ZUMBADORES EN UNA POSICION DEL MUNDO, LA SINTAXIS DEL NODO ES:
                  // <monton x="<coordenada_x>" y="<coordenada_y>" zumbadores="<numero_de_zumbadores>"/>
                  // UN VALOR DE -1 EN EL NUMERO DE ZUMBADORES IMPLICA UN NUMERO INFINITO DE ZUMBADORES
                  x1:=nodoa.ReadAttributeInteger('x',-1);
                  y1:=nodoa.ReadAttributeInteger('y',-1);
                  z:=nodoa.ReadAttributeInteger('zumbadores',0);
                  if not(dejaZumbador(x1,y1,z)) then begin
                     agregaAErrores('Imposible colocar el monton de zumbadores especificado en el nodo ' + IntToStr(i));
                  end;
               end
               else if sNombreNodo = 'LISTAMONTONES' then begin
                    // PERMITE DAR UNA LISTA DE MONTONES COMO TRIPLETAS {x,y,z} QUE INDICAN LAS COORDENADAS (x,y) DEL MONTON Y EL NUMERO DE ZUMBADORES
                    // LA SINTAXIS DEL NODO ES:
                    // <listamontones>lista_de_tripletas_separadas_por_;_entre_tripletas_y_por_,_entre_elementos</listamontones>
                    // EJEMPLO <listamontones>1,2,1;1,3,2;3,5,-1</listamontones>
                    // UN VALOR DE -1 EN EL NUMERO DE ZUMBADORES IMPLICA UN NUMERO INFINITO DE ZUMBADORES
                    sLista:=nodoa.Value;
                    j:=0;
                    repeat
                          sLista:=Trim(sLista);
                          APos:=FastCharPos(sLista,';',1);
                          if APos <> 0 then begin
                             sTupla:=Copy(sLista,1,APos - 1);
                             sLista:=Copy(sLista,APos + 1,Length(sLista) - APos);
                          end
                          else begin
                               sTupla:=sLista;
                               sLista:='';
                          end;

                          if sTupla <> '' then begin
                             Inc(j);
                             APos:=FastCharPos(sTupla,',',1);
                             BPos:=FastCharPos(sTupla,',',APos + 1);
                             x1:=StrToIntDef(Copy(sTupla,1,APos - 1),-1);
                             y1:=StrToIntDef(Copy(sTupla,APos + 1,BPos - APos - 1),-1);
                             z:=StrToIntDef(Copy(sTupla,BPos + 1,Length(sTupla) - BPos),0);

                             if not(dejaZumbador(x1,y1,z)) then begin
                                agregaAErrores('Imposible colocar el monton de zumbadores especificado en el nodo ' + IntToStr(i) +
                                               ', tupla ' + IntToStr(j));
                             end;

                          end;
                    until sLista = '';
               end
               else if sNombreNodo = 'PARED' then begin
                    // PERMITE PONER UNA PARED EN EL MUNDO, LA SINTAXIS DEL NODO ES:
                    // <pared x1="<coordenada_x_vertice_1>" y1="<coordenada_y_vertice_1>" x2="<coordenada_x2>" y2="<coordenada_y2>"/>
                    // ES IMPORTANTE NOTAR QUE LAS COORDENADAS PARA LAS PAREDES Y PARA LOS ZUMBADORES ESTAN DEFASADAS, MIENTRAS QUE LAS COORDENADAS
                    // POSIBLES PARA UN MONTON DE ZUMBADORES VAN DE 1-ancho O 1-alto, LAS DE LAS PAREDES VAN DESDE 0-ancho O desde 0-alto,
                    // SI LA PARED POR SUS COORDENADAS ES INCLINADA, SE AJUSTARA MENDIANTO DOS LINEAS INICIANDO SIEMPRE POR EL SEGMENTO VERTICAL
                    // POR ULTIMO SI ALGUNA DE LAS COORDENADAS _2 NO APARECE, YA SEA x O y, SE ASUME QUE ES IGUAL A LA COORDENADA _1
                    x1:=nodoa.ReadAttributeInteger('x1',-1);
                    x2:=nodoa.ReadAttributeInteger('x2',-1);
                    y1:=nodoa.ReadAttributeInteger('y1',-1);
                    y2:=nodoa.ReadAttributeInteger('y2',-1);
                    if (x1 <> -1) and (y1 <> -1) then begin
                       if Not(agregaPared(x1,y1,x2,y2)) then
                          agregaAErrores('Fue imposible agregar la pared especificada por el nodo ' + IntToStr(i));
                    end
                    else begin
                         agregaAErrores('Nodo ' + IntToStr(i) + ' tipo PARED no tiene atributos minimos x1 y y1');
                    end;
               end
               else if sNombreNodo = 'POLIGONAL' then begin
                    // PERMITE PONER UNA POLIGONAL ESTABLECIENDO UNICAMENTE LA SUCESION DE VERTICES. LA SINTAXIS DEL NODO ES:
                    // <poligonal abierta="[true|false]">lista_de_vertices_como_duplas_separadas_por_;_ y_por_,_entre_sus_elementos</poligonal>
                    // EJEMPLO <poligonal abierta="false">0,0;0,10;10,10;10,0</poligonal> <!-- TRAZA UN CUADRADO -->
                    // SI LA POLIGONAL SE DEFINE COMO CERRADA, SE TRAZARA UNA ULTIMA PARED ENTRE EL NODO INICIO Y EL FIN DE LA SUCESION.
                    abierta:=nodoa.ReadAttributeBool('abierta',false);
                    sLista:=nodoa.Value;
                    j:=0;
                    repeat
                          sLista:=Trim(sLista);
                          APos:=FastCharPos(sLista,';',1);
                          if APos <> 0 then begin
                             sTupla:=Copy(sLista,1,APos - 1);
                             sLista:=Copy(sLista,APos + 1,Length(sLista) - APos);
                          end
                          else begin
                               sTupla:=sLista;
                               sLista:='';
                          end;

                          if sTupla <> '' then begin
                             Inc(j);
                             APos:=FastCharPos(sTupla,',',1);

                             x1:=x2;
                             y1:=y2;

                             x2:=StrToIntDef(Copy(sTupla,1,APos - 1),-1);
                             y2:=StrToIntDef(Copy(sTupla,APos + 1,Length(sTupla) - APos),-1);

                             if j > 1 then begin
                                if not(agregaPared(x1,y1,x2,y2)) then begin
                                   agregaAErrores('Fue imposible agregar el segmento ' + IntToStr(j) +
                                                  ' de la poligonal especificada en el nodo ' + IntToStr(i));
                                end;
                             end
                             else begin
                                  xo:=x2;
                                  yo:=y2;
                             end;

                          end;
                    until sLista = '';

                    if Not(abierta) then begin
                       if Not(agregaPared(x2,y2,xo,yo)) then begin
                          agregaAErrores('Fue imposible agregar la pared que cierra la poligonal especificada en el nodo ' + IntToStr(i));
                       end;
                    end;
               end;
           end;

           result:=true;
     except
           On E : Exception do begin
              errMsg:='Error al cargar archivo de mundo (' + E.Message + ')';
           end;
     end;
end;

procedure TKMundo.limpiaErrores;
begin
     _slErrores.Clear;
end;

procedure TKMundo.limpiaMundo;
var
   i, j : integer;
begin
     for i:=0 to ancho + 1 do
         for j:=0 to alto + 1 do
             _mundo[i,j].valor:=0;
end;

procedure TKMundo.setZumbadoresEnCoordenada(cX, cY: integer;
  const Value: integer);
begin
     if (cX > 0) and (cY > 0) and (cX <= ancho) and (cY <= alto) then begin
        _mundo[cX,cY].zumbadores:=Value;
     end;
end;

end.
