//==================================================
//All code herein is copyrighted by
//Peter Morris
//-----
//No copying, alteration, or use is permitted without
//prior permission from myself.
//------
//Do not alter / remove this copyright notice
//Email me at : support@droopyeyes.com
//
//The homepage for this library is http://www.droopyeyes.com
//
//(Check out www.HowToDoThings.com for Delphi articles !)
//(Check out www.stuckindoors.com if you need a free events page on your site !)
//==================================================
//Ps
//Permission can be obtained very easily, and there is no ££££ involved,
//please email me for permission, this way you can be included on the
//email list to be notififed of updates / fixes etc.

//(It just includes sending my kids a postcard, nothing more !)

//Modifications
//==============================================================================
//Date  : 26 June, 2000
//Found : NEW FEATURE
//Fixed : Pete M
//Change: Someone asked for a StringCount function, to count how many times a
//        sub string exists within a string.
//        Don't know if it is fast or not, so you'll just have to try it out.
//==============================================================================
//Date  : 3 July, 2000
//Found : NEW FEATURE
//Fixed : Pete M
//Change: After using ASP for a short while I have become quite fond of the
//        LEFT and RIGHT functions.  So I added them.
//==============================================================================
//Date  : 3 July, 2000
//Found : Pete M + Ozz Nixon (Brain patchwork DX)
//Fixed : Pete M
//Change: changed Left to LeftStr (so as not to get confused with TForm.Left)
//        changed RIGHT to RightStr to comply with LEFT
//        Added CopyStr (quicker than COPY)
//        Used SetLength method as pointed out by Ozz Nixon
//==============================================================================
//Date  : 10 July, 2000
//Found : NEW FEATURE
//Fixed : Pete M
//Change: Routine to convert HTML RGB to TColor,
//        HEX to INT
//        URL to plain text
//        Decrypt and Encrypt
//        StringMatches
//        MissingText
//        ExtractHTML
//        ExtractNonHTML
//        RandomStr
//        RandomFilename
//        UniqueFilename
//        WordAt
//==============================================================================
//Date  : 28 July, 2000
//Found : Pete M
//Fixed : Pete M
//Change: Some people have requested ReverseStr.
//        Personally I have no idea what you would use it for, but it was simple
//        enough to write so I did.
//        Ps, Oliver's 1st ever birthday tomorrow :-)
//==============================================================================
//Date  : 11 Sept, 2001
//Found : Misc
//Fixed : Pete M
//Change: StringCount caused unit to not compile
//==============================================================================
//Date  : 14 March, 2001
//Found : NEW FEATURE
//Fixed : Pete M
//Change: Soundex is a very useful tool for searching in databases, I found a
//        very interesting piece of code on www.interbase.com.  This soundex
//        code returns an integer instead of a 4 digit string, which is most
//        likely quicker when searching, and a more useful format to store.
//==============================================================================
//Date  : 1 August, 2002
//Found : NEW FEATURE
//Fixed : Marc Bir
//Change: Marc Bir (www.delphihome.com) has kindly donated 2 routines.
//        Base64Encode and Base64Decode
//==============================================================================
//Date  : 21 August, 2002
//Found : Otto Csatari <dreaml@freemail.hu>
//Fixed : Otto Csatari
//Change: Split routine created "Result" if it was nil, but this was never passed
//        back as I had omitted the "var" keyword.
//==============================================================================
//Date  : 27 October, 2002
//Found : Claus H. Karstensen <chk@hipsomhap.dk>
//Fixed : Claus H. Karstensen / Peter Morris
//Change: Claus- Improved the speed of StripHTMLorNonHTML by setting the result
//        buffer in advance.
//        Pete M- Used PChar for source + dest chars so that Delphi doesn't need
//        to calculate the character address of string[X] each time.  Also changed
//        the HTML result to include the < and > tags.

unit FastStringFuncs;

interface

uses
  {$IFDEF LINUX}
    QGraphics,
  {$ELSE}
    Graphics,
  {$ENDIF}
  FastStrings, Sysutils, Classes;

const
  cHexChars = '0123456789ABCDEF';
  cSoundexTable: array[65..122] of Byte =
    ({A}0, {B}1, {C}2, {D}3, {E}0, {F}1, {G}2, {H}0, {I}0, {J}2, {K}2, {L}4, {M}5,
     {N}5, {O}0, {P}1, {Q}2, {R}6, {S}2, {T}3, {U}0, {V}1, {W}0, {X}2, {Y}0, {Z}2,
     0, 0, 0, 0, 0, 0,
     {a}0, {b}1, {c}2, {d}3, {e}0, {f}1, {g}2, {h}0, {i}0, {j}2, {k}2, {l}4, {m}5,
     {n}5, {o}0, {p}1, {q}2, {r}6, {s}2, {t}3, {u}0, {v}1, {w}0, {x}2, {y}0, {z}2);


function Base64Encode(const Source: AnsiString): AnsiString;
function Base64Decode(const Source: string): string;
function CopyStr(const aSourceString : string; aStart, aLength : Integer) : string;
function Decrypt(const S: string; Key: Word): string;
function Encrypt(const S: string; Key: Word): string;
function ExtractHTML(S : string) : string;
function ExtractNonHTML(S : string) : string;
function HexToInt(aHex : string) : int64;
function LeftStr(const aSourceString : string; Size : Integer) : string;
function StringMatches(Value, Pattern : string) : Boolean;
function MissingText(Pattern, Source : string; SearchText : string = '?') : string;
function RandomFileName(aFilename : string) : string;
function RandomStr(aLength : Longint) : string;
function ReverseStr(const aSourceString: string): string;
function RightStr(const aSourceString : string; Size : Integer) : string;
function RGBToColor(aRGB : string) : TColor;
function StringCount(const aSourceString, aFindString : string; Const CaseSensitive : Boolean = TRUE) : Integer;
function SoundEx(const aSourceString: string): Integer;
function UniqueFilename(aFilename : string) : string;
function URLToText(aValue : string) : string;
function WordAt(Text : string; Position : Integer) : string;

procedure Split(aValue : string; aDelimiter : Char; var Result : TStrings);

implementation
const
  cKey1 = 52845;
  cKey2 = 22719;
  Base64_Table : shortstring = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

function StripHTMLorNonHTML(const S : string; WantHTML : Boolean) : string; forward;

//Encode to Base64
function Base64Encode(const Source: AnsiString): AnsiString;
var
  NewLength: Integer;
begin
  NewLength := ((2 + Length(Source)) div 3) * 4;
  SetLength( Result, NewLength);

  asm
    Push  ESI
    Push  EDI
    Push  EBX
    Lea   EBX, Base64_Table
    Inc   EBX                // Move past String Size (ShortString)
    Mov   EDI, Result
    Mov   EDI, [EDI]
    Mov   ESI, Source
    Mov   EDX, [ESI-4]        //Length of Input String
@WriteFirst2:
    CMP EDX, 0
    JLE @Done
    MOV AL, [ESI]
    SHR AL, 2
{$IFDEF VER140} // Changes to BASM in D6
    XLATB
{$ELSE}
    XLAT
{$ENDIF}
    MOV [EDI], AL
    INC EDI
    MOV AL, [ESI + 1]
    MOV AH, [ESI]
    SHR AX, 4
    AND AL, 63
{$IFDEF VER140} // Changes to BASM in D6
    XLATB
{$ELSE}
    XLAT
{$ENDIF}
    MOV [EDI], AL
    INC EDI
    CMP EDX, 1
    JNE @Write3
    MOV AL, 61                        // Add ==
    MOV [EDI], AL
    INC EDI
    MOV [EDI], AL
    INC EDI
    JMP @Done
@Write3:
    MOV AL, [ESI + 2]
    MOV AH, [ESI + 1]
    SHR AX, 6
    AND AL, 63
{$IFDEF VER140} // Changes to BASM in D6
    XLATB
{$ELSE}
    XLAT
{$ENDIF}
    MOV [EDI], AL
    INC EDI
    CMP EDX, 2
    JNE @Write4
    MOV AL, 61                        // Add =
    MOV [EDI], AL
    INC EDI
    JMP @Done
@Write4:
    MOV AL, [ESI + 2]
    AND AL, 63
{$IFDEF VER140} // Changes to BASM in D6
    XLATB
{$ELSE}
    XLAT
{$ENDIF}
    MOV [EDI], AL
    INC EDI
    ADD ESI, 3
    SUB EDX, 3
    JMP @WriteFirst2
@done:
    Pop EBX
    Pop EDI
    Pop ESI
  end;
end;


//Decode Base64
function Base64Decode(const Source: string): string;
var
  NewLength: Integer;
begin
{
  NB: On invalid input this routine will simply skip the bad data, a
better solution would probably report the error


  ESI -> Source String
  EDI -> Result String

  ECX -> length of Source (number of DWords)
  EAX -> 32 Bits from Source
  EDX -> 24 Bits Decoded

  BL -> Current number of bytes decoded
}

  SetLength( Result, (Length(Source) div 4) * 3);
  NewLength := 0;
  asm
    Push  ESI         
    Push  EDI
    Push  EBX

    Mov   ESI, Source

    Mov   EDI, Result //Result address
    Mov   EDI, [EDI]

    Or    ESI,ESI   // Nil Strings
    Jz    @Done

    Mov   ECX, [ESI-4]
    Shr   ECX,2       // DWord Count

    JeCxZ @Error      // Empty String

    Cld

    jmp   @Read4

  @Next:
    Dec   ECX
    Jz   @Done

  @Read4:
    lodsd

    Xor   BL, BL
    Xor   EDX, EDX

    Call  @DecodeTo6Bits
    Shl   EDX, 6
    Shr   EAX,8
    Call  @DecodeTo6Bits
    Shl   EDX, 6
    Shr   EAX,8
    Call  @DecodeTo6Bits
    Shl   EDX, 6
    Shr   EAX,8
    Call  @DecodeTo6Bits


  // Write Word

    Or    BL, BL
    JZ    @Next  // No Data

    Dec   BL
    Or    BL, BL
    JZ    @Next  // Minimum of 2 decode values to translate to 1 byte

    Mov   EAX, EDX

    Cmp   BL, 2
    JL    @WriteByte

    Rol   EAX, 8

    BSWAP EAX

    StoSW

    Add NewLength, 2

  @WriteByte:
    Cmp BL, 2
    JE  @Next
    SHR EAX, 16
    StoSB

    Inc NewLength
    jmp   @Next

  @Error:
    jmp @Done

  @DecodeTo6Bits:

  @TestLower:
    Cmp AL, 'a'
    Jl @TestCaps
    Cmp AL, 'z'
    Jg @Skip
    Sub AL, 71
    Jmp @Finish

  @TestCaps:
    Cmp AL, 'A'
    Jl  @TestEqual
    Cmp AL, 'Z'
    Jg  @Skip
    Sub AL, 65
    Jmp @Finish

  @TestEqual:
    Cmp AL, '='
    Jne @TestNum
    // Skip byte
    ret

  @TestNum:
    Cmp AL, '9'
    Jg @Skip
    Cmp AL, '0'
    JL  @TestSlash
    Add AL, 4
    Jmp @Finish

  @TestSlash:
    Cmp AL, '/'
    Jne @TestPlus
    Mov AL, 63
    Jmp @Finish

  @TestPlus:
    Cmp AL, '+'
    Jne @Skip
    Mov AL, 62

  @Finish:
    Or  DL, AL
    Inc BL

  @Skip:
    Ret

  @Done:
    Pop   EBX
    Pop   EDI
    Pop   ESI

  end;

  SetLength( Result, NewLength); // Trim off the excess
end;


//Encrypt a string
function Encrypt(const S: string; Key: Word): string;
var
I: byte;
begin
 SetLength(result,length(s));
 for I := 1 to Length(S) do
    begin
        Result[I] := char(byte(S[I]) xor (Key shr 8));
        Key := (byte(Result[I]) + Key) * cKey1 + cKey2;
    end;
end;

//Return only the HTML of a string
function ExtractHTML(S : string) : string;
begin
  Result := StripHTMLorNonHTML(S, True);
end;

function CopyStr(const aSourceString : string; aStart, aLength : Integer) : string;
var
  L                           : Integer;
begin
  L := Length(aSourceString);
  if L=0 then Exit;
  if (aStart < 1) or (aLength < 1) then Exit;

  if aStart + (aLength-1) > L then aLength := L - (aStart-1);

  if (aStart <1) then exit;

  SetLength(Result,aLength);
  FastCharMove(aSourceString[aStart], Result[1], aLength);
end;

//Take all HTML out of a string
function ExtractNonHTML(S : string) : string;
begin
  Result := StripHTMLorNonHTML(S,False);
end;

//Decrypt a string encoded with Encrypt
function Decrypt(const S: string; Key: Word): string;
var
  I: byte;
begin
 SetLength(result,length(s));
 for I := 1 to Length(S) do
    begin
        Result[I] := char(byte(S[I]) xor (Key shr 8));
        Key := (byte(S[I]) + Key) * cKey1 + cKey2;
    end;
end;

//Convert a text-HEX value (FF0088 for example) to an integer
function  HexToInt(aHex : string) : int64;
var
  Multiplier      : Int64;
  Position        : Byte;
  Value           : Integer;
begin
  Result := 0;
  Multiplier := 1;
  Position := Length(aHex);
  while Position >0 do begin
    Value := FastCharPosNoCase(cHexChars, aHex[Position], 1)-1;
    if Value = -1 then
      raise Exception.Create('Invalid hex character ' + aHex[Position]);

    Result := Result + (Value * Multiplier);
    Multiplier := Multiplier * 16;
    Dec(Position);
  end;
end;

//Get the left X amount of chars
function LeftStr(const aSourceString : string; Size : Integer) : string;
begin
  if Size > Length(aSourceString) then
    Result := aSourceString
  else begin
    SetLength(Result, Size);
    Move(aSourceString[1],Result[1],Size);
  end;
end;

//Do strings match with wildcards, eg
//StringMatches('The cat sat on the mat', 'The * sat * the *') = True
function StringMatches(Value, Pattern : string) : Boolean;
var
  NextPos,
  Star1,
  Star2       : Integer;
  NextPattern   : string;
begin
  Star1 := FastCharPos(Pattern,'*',1);
  if Star1 = 0 then
    Result := (Value = Pattern)
  else
  begin
    Result := (Copy(Value,1,Star1-1) = Copy(Pattern,1,Star1-1));
    if Result then
    begin
      if Star1 > 1 then Value := Copy(Value,Star1,Length(Value));
      Pattern := Copy(Pattern,Star1+1,Length(Pattern));

      NextPattern := Pattern;
      Star2 := FastCharPos(NextPattern, '*',1);
      if Star2 > 0 then NextPattern := Copy(NextPattern,1,Star2-1);

      //pos(NextPattern,Value);
      NextPos := FastPos(Value, NextPattern, Length(Value), Length(NextPattern), 1);
      if (NextPos = 0) and not (NextPattern = '') then
        Result := False
      else
      begin
        Value := Copy(Value,NextPos,Length(Value));
        if Pattern = '' then
          Result := True
        else
          Result := Result and StringMatches(Value,Pattern);
      end;
    end;
  end;
end;

//Missing text will tell you what text is missing, eg
//MissingText('the ? sat on the mat','the cat sat on the mat','?') = 'cat'
function MissingText(Pattern, Source : string; SearchText : string = '?') : string;
var
  Position                    : Longint;
  BeforeText,
  AfterText                   : string;
  BeforePos,
  AfterPos                     : Integer;
  lSearchText,
  lBeforeText,
  lAfterText,
  lSource                     : Longint;
begin
  Result := '';
  Position := Pos(SearchText,Pattern);
  if Position = 0 then exit;

  lSearchText := Length(SearchText);
  lSource := Length(Source);
  BeforeText := Copy(Pattern,1,Position-1);
  AfterText := Copy(Pattern,Position+lSearchText,lSource);

  lBeforeText := Length(BeforeText);
  lAfterText := Length(AfterText);

  AfterPos := lBeforeText;
  repeat
    AfterPos := FastPosNoCase(Source,AfterText,lSource,lAfterText,AfterPos+lSearchText);
    if AfterPos > 0 then begin
      BeforePos := FastPosBackNoCase(Source,BeforeText,AfterPos-1,lBeforeText,AfterPos - (lBeforeText-1));
      if (BeforePos > 0) then begin
        Result := Copy(Source,BeforePos + lBeforeText, AfterPos - (BeforePos + lBeforeText));
        Break;
      end;
    end;
  until AfterPos = 0;
end;

//Generates a random filename but preserves the original path + extension
function RandomFilename(aFilename : string) : string;
var
  Path,
  Filename,
  Ext               : string;
begin
  Result := aFilename;
  Path := ExtractFilepath(aFilename);
  Ext := ExtractFileExt(aFilename);
  Filename := ExtractFilename(aFilename);
  if Length(Ext) > 0 then
    Filename := Copy(Filename,1,Length(Filename)-Length(Ext));
  repeat
    Result := Path + RandomStr(32) + Ext;
  until not FileExists(Result);
end;

//Makes a string of aLength filled with random characters
function RandomStr(aLength : Longint) : string;
var
  X                           : Longint;
begin
  if aLength <= 0 then exit;
  SetLength(Result, aLength);
  for X:=1 to aLength do
    Result[X] := Chr(Random(26) + 65);
end;

function ReverseStr(const aSourceString: string): string;
var
  L                           : Integer;
  S,
  D                           : Pointer;
begin
  L := Length(aSourceString);
  SetLength(Result,L);
  if L = 0 then exit;

  S := @aSourceString[1];
  D := @Result[L];

  asm
    push ESI
    push EDI

    mov  ECX, L
    mov  ESI, S
    mov  EDI, D

  @Loop:
    mov  Al, [ESI]
    inc  ESI
    mov  [EDI], Al
    dec  EDI
    dec  ECX
    jnz  @Loop

    pop  EDI
    pop  ESI
  end;
end;

//Returns X amount of chars from the right of a string
function RightStr(const aSourceString : string; Size : Integer) : string;
begin
  if Size > Length(aSourceString) then
    Result := aSourceString
  else begin
    SetLength(Result, Size);
    FastCharMove(aSourceString[Length(aSourceString)-(Size-1)],Result[1],Size);
  end;
end;

//Converts a typical HTML RRGGBB color to a TColor
function RGBToColor(aRGB : string) : TColor;
begin
  if Length(aRGB) < 6 then raise EConvertError.Create('Not a valid RGB value');
  if aRGB[1] = '#' then aRGB := Copy(aRGB,2,Length(aRGB));
  if Length(aRGB) <> 6 then raise EConvertError.Create('Not a valid RGB value');

  Result := HexToInt(aRGB);
  asm
    mov   EAX, Result
    BSwap EAX
    shr   EAX, 8
    mov   Result, EAX
  end;
end;

//Splits a delimited text line into TStrings (does not account for stuff in quotes but it should)
procedure Split(aValue : string; aDelimiter : Char; var Result : TStrings);
var
  X : Integer;
  S : string;
begin
  if Result = nil then Result := TStringList.Create;
  Result.Clear;
  S := '';
  for X:=1 to Length(aValue) do begin
    if aValue[X] <> aDelimiter then
      S:=S + aValue[X]
    else begin
      Result.Add(S);
      S := '';
    end;
  end;
  if S <> '' then Result.Add(S);
end;

//counts how many times a substring exists within a string
//StringCount('XXXXX','XX') would return 2
function StringCount(const aSourceString, aFindString : string; Const CaseSensitive : Boolean = TRUE) : Integer;
var
  Find,
  Source,
  NextPos                     : PChar;
  LSource,
  LFind                       : Integer;
  Next                        : TFastPosProc;
  JumpTable                   : TBMJumpTable;
begin
  Result := 0;
  LSource := Length(aSourceString);
  if LSource = 0 then exit;

  LFind := Length(aFindString);
  if LFind = 0 then exit;

  if CaseSensitive then
  begin
    Next := BMPos;
    MakeBMTable(PChar(aFindString), Length(aFindString), JumpTable);
  end else
  begin
    Next := BMPosNoCase;
    MakeBMTableNoCase(PChar(aFindString), Length(aFindString), JumpTable);
  end;

  Source := @aSourceString[1];
  Find := @aFindString[1];

  repeat
    NextPos := Next(Source, Find, LSource, LFind, JumpTable);
    if NextPos <> nil then
    begin
      Dec(LSource, (NextPos - Source) + LFind);
      Inc(Result);
      Source := NextPos + LFind;
    end;
  until NextPos = nil;
end;

function SoundEx(const aSourceString: string): Integer;
var
  CurrentChar: PChar;
  I, S, LastChar, SoundexGroup: Byte;
  Multiple: Word;
begin
  if aSourceString = '' then
    Result := 0
  else
  begin
    //Store first letter immediately
    Result := Ord(Upcase(aSourceString[1]));

    //Last character found = 0
    LastChar := 0;
    Multiple := 26;

    //Point to first character
    CurrentChar := @aSourceString[1];

    for I := 1 to Length(aSourceString) do
    begin
      Inc(CurrentChar);

      S := Ord(CurrentChar^);
      if (S > 64) and (S < 123) then
      begin
        SoundexGroup := cSoundexTable[S];
        if (SoundexGroup <> LastChar) and (SoundexGroup > 0) then
        begin
          Inc(Result, SoundexGroup * Multiple);
          if Multiple = 936 then Break; {26 * 6 * 6}
          Multiple := Multiple * 6;
          LastChar := SoundexGroup;
        end;
      end;
    end;
  end;
end;

//Used by ExtractHTML and ExtractNonHTML
function StripHTMLorNonHTML(const S : string; WantHTML : Boolean) : string;
var
  X: Integer;
  TagCnt: Integer;
  ResChar: PChar;
  SrcChar: PChar;
begin
  TagCnt := 0;
  SetLength(Result, Length(S));
  if Length(S) = 0 then Exit;

  ResChar := @Result[1];
  SrcChar := @S[1];
  for X:=1 to Length(S) do
  begin
    case SrcChar^ of
      '<':
        begin
          Inc(TagCnt);
          if WantHTML and (TagCnt = 1) then
          begin
            ResChar^ := '<';
            Inc(ResChar);
          end;
        end;
      '>':
        begin
          Dec(TagCnt);
          if WantHTML and (TagCnt = 0) then
          begin
            ResChar^ := '>';
            Inc(ResChar);
          end;
        end;
    else
      case WantHTML of
        False:
          if TagCnt <= 0 then
          begin
            ResChar^ := SrcChar^;
            Inc(ResChar);
            TagCnt := 0;
          end;
        True:
          if TagCnt >= 1 then
          begin
            ResChar^ := SrcChar^;
            Inc(ResChar);
          end else
            if TagCnt < 0 then TagCnt := 0;
      end;
    end;
    Inc(SrcChar);
  end;
  SetLength(Result, ResChar - PChar(@Result[1]));
  Result := FastReplace(Result, '&nbsp;', ' ', False);
  Result := FastReplace(Result,'&amp;','&', False);
  Result := FastReplace(Result,'&lt;','<', False);
  Result := FastReplace(Result,'&gt;','>', False);
  Result := FastReplace(Result,'&quot;','"', False);
end;

//Generates a UniqueFilename, makes sure the file does not exist before returning a result
function UniqueFilename(aFilename : string) : string;
var
  Path,
  Filename,
  Ext               : string;
  Index             : Integer;
begin
  Result := aFilename;
  if FileExists(aFilename) then begin
    Path := ExtractFilepath(aFilename);
    Ext := ExtractFileExt(aFilename);
    Filename := ExtractFilename(aFilename);
    if Length(Ext) > 0 then
      Filename := Copy(Filename,1,Length(Filename)-Length(Ext));
    Index := 2;
    repeat
      Result := Path + Filename + IntToStr(Index) + Ext;
      Inc(Index);
    until not FileExists(Result);
  end;
end;

//Decodes all that %3c stuff you get in a URL
function  URLToText(aValue : string) : string;
var
  X     : Integer;
begin
  Result := '';
  X := 1;
  while X <= Length(aValue) do begin
    if aValue[X] <> '%' then
      Result := Result + aValue[X]
    else begin
      Result := Result + Chr( HexToInt( Copy(aValue,X+1,2) ) );
      Inc(X,2);
    end;
    Inc(X);
  end;
end;

//Returns the whole word at a position
function  WordAt(Text : string; Position : Integer) : string;
var
  L,
  X : Integer;
begin
  Result := '';
  L := Length(Text);

  if (Position > L) or (Position < 1) then Exit; 
  for X:=Position to L do begin
    if Upcase(Text[X]) in ['A'..'Z','0'..'9'] then
      Result := Result + Text[X]
    else
      Break;
  end;

  for X:=Position-1 downto 1 do begin
    if Upcase(Text[X]) in ['A'..'Z','0'..'9'] then
      Result := Text[X] + Result
    else
      Break;
  end;
end;



end.
