program kcl;

uses
  SysUtils,
  NativeXml,
  UCompilador_V3,
  UKProgramaCompilado;


{
 Esta aplicacion permite compilar un codigo de Karel, ya sea en pascal o en Java y guardar el
 resultado compilado en un archivo .kx para poder utilizarlo posteriormente en entornos de
 ejecucion.

 La respuesta se maneja en la linea de comandos, sea positiva o error.

 Ademas permite aplicar condiciones de compilacion que limitan el numero de instrucciones
 de un cierto tipo.

}

const

     strAyuda = 'Utilizacion: kcl -c Codigo [-d][-lp | -lj][-o Salida]' + #13#10#13#10 +
                '-c' + #9 + 'Especifica la ruta completa del archivo de codigo.' + #13#10 +
                '-d' + #9 + 'Incluye informacion de depuracion en el programa compilado.' + #13#10 +
                '-lp' + #9 + 'Especifica que el codigo esta en lenguaje PASCAL.' + #13#10 +
                '-lj' + #9 + 'Especifica que el codigo esta en lenguaje JAVA.' + #13#10 +
                '-o' + #9 + 'Especifica la ruta completa del archivo compilado de salida.' + #13#10#13#10 +
                'Para los nombres de archivo, si la ruta contiene espacios debera encerrarse el nombre del archivo entre comillas (").' + #13#10 +
                'Si no se especifica archivo de salida, se utilizara la ruta y nombre del archivo de entrada pero con extension .kx' + #13#10 +
                'Si no se especifica lenguaje se decidira en base a la extension. .PAS para Pascal y .JS para Java';

     prefijoError = 'ERROR AL COMPILAR' + #13#10;
     prefijoOK = 'COMPILACION EXITOSA' + #13#10;

     version = '1.1';

var
   param, aCodigo, aSalida, aCondiciones : string;
   lenguaje : integer = LANG_NODEFINIDO;
   depuracion : boolean = false;
   i, j : integer;
   st : AnsiString;
   sCodigo : AnsiString;
   comillas : boolean;

   fCodigo : Text;

   xml : TNativeXML;
   nodoa, nodob : TXMLNode;

   compilador : TKCompilador;
   programa : TKProgramaCompilado;


function resumenParametros : AnsiString;
begin
     result:='Codigo: ' + aCodigo + #13#10 +
             'Salida: ' + aSalida + #13#10 +
             'Lenguaje: ' + descripcionLenguajes[lenguaje] + #13#10 +
             'Condiciones: ' + aCondiciones + #13#10#13#10;
end;

procedure salConError(const msg : string);
begin
     writeln(StdErr, prefijoError +
             resumenParametros +
             'Error: ' +  msg);
     Halt(1);
end;

function obtenNombreDeArchivo(var i : integer) : string;
var
   st : string;
begin
     Inc(i);
     st:=ParamStr(i);
     if (st <> '') and (st[1] = '"') then begin
        while (st[Length(st)] <> '"') and (i <= ParamCount) do begin
              Inc(i);
              st:=st + ' ' + ParamStr(i);
        end;

        if st[Length(st)] <> '"' then st:=st + '"';

        st:=Copy(st,2,Length(st) - 2);
     end;
     result:=st;
end;

begin
     writeln('Compilador de linea para Karel OMI version - ' + version);
     writeln('');

     // LEE LOS PARAMETROS DE ENTRADA
     i:=1;
     while i <= ParamCount do begin
           param:=ParamStr(i);
           if param = '-c' then begin
              aCodigo:=obtenNombreDeArchivo(i);
           end
           else if param = '-d' then begin
                depuracion:=true;
           end
           else if param = '-lj' then begin
                lenguaje:=LANG_JAVA;
           end
           else if param = '-lp' then begin
                lenguaje:=LANG_PASCAL;
           end
           else if param = '-o' then begin
                aSalida:=obtenNombreDeArchivo(i);
           end
           else if param = '-cond' then begin
                aCondiciones:=obtenNombreDeArchivo(i);
           end;
           Inc(i);
     end;

     if aCodigo = '' then begin
        writeln(strAyuda);
        exit;
     end;

     st:=UpperCase(ExtractFileExt(aCodigo));
     if aSalida = '' then begin
        aSalida:=Copy(aCodigo,1,Length(aCodigo) - Length(st)) + '.kx';
     end;

     if lenguaje = LANG_NODEFINIDO then begin
        if st = '.PAS' then
           lenguaje:=LANG_PASCAL
        else if st = '.JS' then
             lenguaje:=LANG_JAVA
        else
            salConError('Falta especificar el lenguaje de compilacion');
     end;

     // REVISA QUE TODO EXISTA Y SEA VALIDO
     if Not(FileExists(aCodigo)) then begin
        salConError('No fue posible encontrar el archivo de codigo (' + aCodigo + ')');
     end;

     if aCondiciones <> '' then begin
        if FileExists(aCondiciones) then begin
           xml:=TNativeXML.Create(nil);
           try
              xml.LoadFromFile(aCondiciones);
           except
                 On E : Exception do begin
                    xml.Free;
                    salConError('Error al cargar el archivo de condiciones (' + E.Message + ')');
                 end;
           end;
        end
        else begin
             salConError('No fue posible encontrar el archivo de condiciones (' + aCondiciones + ')');
        end;
     end;

     // EJECUTA EL COMPILADOR.
     compilador:=TKCompilador.Create;
     compilador.infoDepuracion:=depuracion;

     // LEE EL CODIGO
     sCodigo:='';
     try
        AssignFile(fCodigo,aCodigo);
        try
           Reset(fCodigo);
           while not(eof(fCodigo)) do begin
                 readln(fCodigo,st);
                 sCodigo:=sCodigo + st + #13#10;
           end;
        finally
               CloseFile(fCodigo);
        end;
     except
           On E : Exception do begin
              salConError('Error al cargar archivo de codigo (' + E.Message + ')');
           end;
     end;

     programa:=compilador.compilaPrograma(lenguaje,sCodigo);
     if programa <> nil then begin
        // APLICA LAS CONDICIONES DE COMPILACION SI ERA NECESARIO
        {TODO : Aplicacion de condiciones de compilacion}

        // INTENTA GRABAR EL CODIGO COMPILADO
        if Not(programa.guardaAArchivo(aSalida)) then begin
           salConError('Error al guardar codigo compilado (' + programa.errMsg + ')');
        end;

        writeln(prefijoOK + resumenParametros + programa.resumenCompilacion);
     end
     else begin
          salConError(compilador.errMsg);
     end;

     programa.Free;
     compilador.Free;
     if Assigned(xml) then
        xml.Free;


end.

