unit FastStrings;

interface

uses
   SysUtils;


function FastCharPos(const aSource : string; const C: Char; StartPos : Integer): Integer;
function FastCharPosNoCase(const aSource : string; C: Char; StartPos : Integer): Integer;
function FastPos(const aSourceString, aFindString : string; const aSourceLen, aFindLen, StartPos : Integer) : Integer;
function FastPosNoCase(const aSourceString, aFindString : string; const aSourceLen, aFindLen, StartPos : Integer) : Integer;
function FastReplace(const aSourceString : string; const aFindString, aReplaceString : string;
  CaseSensitive : Boolean = False) : string;

implementation



//NOTE : FastCharPos and FastCharPosNoCase do not require you to pass the length
//       of the string, this was only done in FastPos and FastPosNoCase because
//       they are used by FastReplace many times over, thus saving a LENGTH()
//       operation each time.  I can't see you using these two routines for the
//       same purposes so I didn't do that this time !
function FastCharPos(const aSource : string; const C: Char; StartPos : Integer) : Integer;
var
  aTmp : string;
  aRes : integer;
begin
  aTmp:=Copy(aSource,StartPos,Length(aSource) - StartPos + 1);
  aRes:=Pos(C,aTmp);
  if aRes <> 0 then begin
     result:=aRes + StartPos - 1;
  end
  else begin
       result:=0;
  end;
end;

function FastCharPosNoCase(const aSource : string; C: Char; StartPos : Integer) : Integer;
var
  aTmp : string;
  aRes : integer;
begin
  aTmp:=UpperCase(Copy(aSource,StartPos,Length(aSource) - StartPos + 1));
  aRes:=Pos(UpCase(C),aTmp);
  if aRes <> 0 then begin
     result:=aRes + StartPos - 1;
  end
  else begin
       result:=0;
  end;
end;

//The first thing to note here is that I am passing the SourceLength and FindLength
//As neither Source or Find will alter at any point during FastReplace there is
//no need to call the LENGTH subroutine each time !
function FastPos(const aSourceString, aFindString : string; const aSourceLen, aFindLen, StartPos : Integer) : Integer;
var
  aTmp : string;
  aRes : integer;
begin
     if StartPos <> 1 then
        aTmp:=Copy(aSourceString,StartPos,Length(aSourceString) - StartPos + 1)
     else
         aTmp:=aSourceString;

     aRes:=Pos(aFindString,aTmp);
     if aRes <> 0 then begin
        result:=aRes + StartPos - 1;
     end
     else begin
          result:=0;
     end;
end;

function FastPosNoCase(const aSourceString, aFindString : string; const aSourceLen, aFindLen, StartPos : Integer) : Integer;
var
  aTmp : string;
  aRes : integer;
begin
  aTmp:=UpperCase(Copy(aSourceString,StartPos,Length(aSourceString) - StartPos + 1));
  aRes:=Pos(UpperCase(aFindString),aTmp);
  if aRes <> 0 then begin
     result:=aRes + StartPos - 1;
  end
  else begin
       result:=0;
  end;
end;


function FastReplace(const aSourceString : string; const aFindString, aReplaceString : string;
   CaseSensitive : Boolean = False) : string;
var
  rf : TReplaceFlags;
begin
  if CaseSensitive then begin
    rf:=[rfReplaceAll];
  end
  else begin
    rf:=[rfReplaceAll,rfIgnoreCase];
  end;

  result:=StringReplace(aSourceString,aFindString,aReplaceString,rf);
end;


end.
