{ ALL-OS bare emulations of win32 functions so
  NativeXml can compile with codepages in other OS'es (eg linux)

  philo.de does have a few tools to convert the codepages.
  see CodecUtilsWin32 and EncodingUtils (MPL or GPL)

  These functions are not yet tested! Work in progress, and anyone knowing about
  exotic encodings please come forward (eg Japanese, Chinese, Korean, etc).

  copyright (c) 2011 Nils Haeck (www.simdesign.nl)
}
unit NativeXmlWin32Compat;

{$define LINUX}

interface

{int MultiByteToWideChar(

    UINT CodePage,	// code page
    DWORD dwFlags,	// character-type options
    LPCSTR lpMultiByteStr,	// address of string to map
    int cchMultiByte,	// number of characters in string
    LPWSTR lpWideCharStr,	// address of wide-character buffer
    int cchWideChar 	// size of buffer
   );	}

function MultiByteToWideChar(CodePage: word; dwFlags: longword; lpMultiByteStr: pansichar;
  cchMultiByte: integer; lpWideCharStr: pwidechar; cchWideChar: integer): integer;

{int WideCharToMultiByte(

    UINT CodePage,	// code page
    DWORD dwFlags,	// performance and mapping flags
    LPCWSTR lpWideCharStr,	// address of wide-character string
    int cchWideChar,	// number of characters in string
    LPSTR lpMultiByteStr,	// address of buffer for new string
    int cchMultiByte,	// size of buffer
    LPCSTR lpDefaultChar,	// address of default for unmappable characters
    LPBOOL lpUsedDefaultChar 	// address of flag set when default char. used
   ); }

function WideCharToMultiByte(CodePage: word; dwFlags: longword; lpWideCharStr: pwidechar;
  cchWideChar: integer; lpMultiByteStr: pansichar; cchMultiByte: integer;
  lpDefaultChar: pansichar; lpUsedDefaultChar: pointer): integer;

const
  // default to ANSI code page 
  CP_ACP = 0;

implementation

uses
  // code from philo.de
  CodecUtilsWin32, EncodingUtils,
  SysUtils, NativeXml;

type

  TUnicodeCodecClass = class of TUnicodeCodec;
  TCodepageToEncodingInfo = packed record
    Name: AnsiString;
    Codepage: integer;
    Codec: TUnicodeCodecClass;
  end;

const

  // Using the codecs from CodecUtilsWin32 here. Many have been added, and guessed, but must verify!
  // Trying to bake up 30 years of *shite* of M$WINDOWS into UTF8.

  // Encodings, Codepages and Codecs
  cCodepageToEncodingInfoCount = 143;
  cCodePageToEncodingInfo: array[0..cCodepageToEncodingInfoCount - 1] of TCodepageToEncodingInfo =
  ( (Name: 'IBM037';                  Codepage:    37; Codec: TIBM037Codec), //1
    (Name: 'IBM437';                  Codepage:   437; Codec: TIBM437Codec),
    (Name: 'IBM500';                  Codepage:   500; Codec: TIBM500Codec),
    (Name: 'ASMO-708';                Codepage:   708; Codec: TWindows708Codec),
    (Name: 'ASMO-449+';               Codepage:   709),
    (Name: 'BCON V4';                 Codepage:   709),
    (Name: 'Arabic';                  Codepage:   710),
    (Name: 'DOS-720';                 Codepage:   720),
    (Name: 'ibm737';                  Codepage:   737; Codec: TWindows737Codec),
    (Name: 'ibm775';                  Codepage:   775; Codec: TWindows775Codec),
    (Name: 'ibm850';                  Codepage:   850; Codec: TIBM850Codec),
    (Name: 'ibm852';                  Codepage:   852; Codec: TIBM852Codec),
    (Name: 'IBM855';                  Codepage:   855; Codec: TIBM855Codec),
    (Name: 'ibm857';                  Codepage:   857; Codec: TIBM857Codec),
    (Name: 'IBM00858';                Codepage:   858; Codec: TWindows858Codec),
    (Name: 'IBM860';                  Codepage:   860; Codec: TIBM860Codec),
    (Name: 'ibm861';                  Codepage:   861; Codec: TIBM861Codec),
    (Name: 'DOS-862';                 Codepage:   862; Codec: TIBM862Codec),
    (Name: 'IBM863';                  Codepage:   863; Codec: TIBM863Codec),
    (Name: 'IBM864';                  Codepage:   864; Codec: TIBM864Codec),
    (Name: 'IBM865';                  Codepage:   865; Codec: TIBM865Codec),
    (Name: 'cp866';                   Codepage:   866; Codec: TIBM866Codec),
    (Name: 'ibm869';                  Codepage:   869; Codec: TWindows869Codec),
    (Name: 'IBM870';                  Codepage:   870; Codec: TWindows870Codec),
    (Name: 'windows-874';             Codepage:   874; Codec: TWindows874Codec),
    (Name: 'cp875';                   Codepage:   875; Codec: TWindows875Codec), //guess
    (Name: 'shift_jis';               Codepage:   932; Codec: TJIS_X0201Codec), //guess
    (Name: 'gb2312';                  Codepage:   936),
    (Name: 'ks_c_5601-1987';          Codepage:   949),
    (Name: 'big5';                    Codepage:   950),
    (Name: 'IBM1026';                 Codepage:  1026; Codec: TIBM1026Codec),
    (Name: 'IBM01047';                Codepage:  1047; Codec: TIBM1047Codec),
    (Name: 'IBM01140';                Codepage:  1140; Codec: TWindows1140Codec),
    (Name: 'IBM01141';                Codepage:  1141; Codec: TWindows1141Codec),
    (Name: 'IBM01142';                Codepage:  1142; Codec: TWindows1142Codec),
    (Name: 'IBM01143';                Codepage:  1143; Codec: TWindows1143Codec),
    (Name: 'IBM01144';                Codepage:  1144; Codec: TWindows1144Codec),
    (Name: 'IBM01145';                Codepage:  1145; Codec: TWindows1145Codec),
    (Name: 'IBM01146';                Codepage:  1146; Codec: TWindows1146Codec),
    (Name: 'IBM01147';                Codepage:  1147; Codec: TWindows1147Codec),
    (Name: 'IBM01148';                Codepage:  1148; Codec: TWindows1148Codec),
    (Name: 'IBM01149';                Codepage:  1149; Codec: TWindows1149Codec),
    (Name: 'utf-16';                  Codepage:  1200; Codec: TUTF16LECodec),
    (Name: 'unicodeFFFE';             Codepage:  1201; Codec: TUTF16BECodec),
    (Name: 'windows-1250';            Codepage:  1250; Codec: TWindows1250Codec),
    (Name: 'windows-1251';            Codepage:  1251; Codec: TWindows1251Codec),
    (Name: 'windows-1252';            Codepage:  1252; Codec: TWindows1252Codec),
    (Name: 'windows-1253';            Codepage:  1253; Codec: TWindows1253Codec),
    (Name: 'windows-1254';            Codepage:  1254; Codec: TWindows1254Codec),
    (Name: 'windows-1255';            Codepage:  1255; Codec: TWindows1255Codec),
    (Name: 'windows-1256';            Codepage:  1256; Codec: TWindows1256Codec),
    (Name: 'windows-1257';            Codepage:  1257; Codec: TWindows1257Codec),
    (Name: 'windows-1258';            Codepage:  1258; Codec: TWindows1258Codec),
    (Name: 'Johab';                   Codepage:  1361),
    (Name: 'macintosh';               Codepage: 10000; Codec: TMacLatin2Codec),  //guess, maybe also TMacRomanCodec
    (Name: 'x-mac-japanese';          Codepage: 10001),
    (Name: 'x-mac-chinesetrad';       Codepage: 10002),
    (Name: 'x-mac-korean';            Codepage: 10003),
    (Name: 'x-mac-arabic';            Codepage: 10004),
    (Name: 'x-mac-hebrew';            Codepage: 10005),
    (Name: 'x-mac-greek';             Codepage: 10006; Codec: TMacGreekCodec),
    (Name: 'x-mac-cyrillic';          Codepage: 10007; Codec: TMacCyrillicCodec),
    (Name: 'x-mac-chinesesimp';       Codepage: 10008),
    (Name: 'x-mac-romanian';          Codepage: 10010),
    (Name: 'x-mac-ukrainian';         Codepage: 10017),
    (Name: 'x-mac-thai';              Codepage: 10021),
    (Name: 'x-mac-ce';                Codepage: 10029),
    (Name: 'x-mac-icelandic';         Codepage: 10079; Codec: TMacIcelandicCodec),
    (Name: 'x-mac-turkish';           Codepage: 10081; Codec: TMacTurkishCodec),
    (Name: 'x-mac-croatian';          Codepage: 10082),
    (Name: 'utf-32';                  Codepage: 12000; Codec: TUCS4_3412Codec),  //guess
    (Name: 'utf-32BE';                Codepage: 12001; Codec: TUCS4_2143Codec),  //guess
    (Name: 'x-Chinese_CNS';           Codepage: 20000),
    (Name: 'x-cp20001';               Codepage: 20001),
    (Name: 'x_Chinese-Eten';          Codepage: 20002),
    (Name: 'x-cp20003';               Codepage: 20003),
    (Name: 'x-cp20004';               Codepage: 20004),
    (Name: 'x-cp20005';               Codepage: 20005),
    (Name: 'x-IA5';                   Codepage: 20105),
    (Name: 'x-IA5-German';            Codepage: 20106),
    (Name: 'x-IA5-Swedish';           Codepage: 20107),
    (Name: 'x-IA5-Norwegian';         Codepage: 20108),
    (Name: 'us-ascii';                Codepage: 20127; Codec: TUSASCIICodec),
    (Name: 'x-cp20261';               Codepage: 20261),
    (Name: 'x-cp20269';               Codepage: 20269),
    (Name: 'IBM273';                  Codepage: 20273; Codec: TIBM273Codec),
    (Name: 'IBM277';                  Codepage: 20277; Codec: TIBM277Codec),
    (Name: 'IBM278';                  Codepage: 20278),
    (Name: 'IBM280';                  Codepage: 20280; Codec: TIBM280Codec),
    (Name: 'IBM284';                  Codepage: 20284; Codec: TIBM284Codec),
    (Name: 'IBM285';                  Codepage: 20285; Codec: TIBM285Codec),
    (Name: 'IBM290';                  Codepage: 20290; Codec: TIBM290Codec),
    (Name: 'IBM297';                  Codepage: 20297; Codec: TIBM297Codec),
    (Name: 'IBM420';                  Codepage: 20420; Codec: TIBM420Codec),
    (Name: 'IBM423';                  Codepage: 20423; Codec: TIBM423Codec),
    (Name: 'IBM424';                  Codepage: 20424; Codec: TIBM424Codec),
    (Name: 'x-EBCDIC-KoreanExtended'; Codepage: 20833; Codec: TEBCDIC_USCodec), //guess
    (Name: 'IBM-Thai';                Codepage: 20838),
    (Name: 'koi8-r';                  Codepage: 20866; Codec: TKOI8_RCodec),
    (Name: 'IBM871';                  Codepage: 20871; Codec: TIBM871Codec),
    (Name: 'IBM880';                  Codepage: 20880; Codec: TIBM880Codec),
    (Name: 'IBM905';                  Codepage: 20905; Codec: TIBM905Codec),
    (Name: 'IBM00924';                Codepage: 20924),
    (Name: 'EUC-JP';                  Codepage: 20932),
    (Name: 'x-cp20936';               Codepage: 20936),
    (Name: 'x-cp20949';               Codepage: 20949),
    (Name: 'cp1025';                  Codepage: 21025),
    (Name: 'koi8-u';                  Codepage: 21866),
    (Name: 'iso-8859-1';              Codepage: 28591; Codec: TISO8859_1Codec),
    (Name: 'iso-8859-2';              Codepage: 28592; Codec: TISO8859_2Codec),
    (Name: 'iso-8859-3';              Codepage: 28593; Codec: TISO8859_3Codec),
    (Name: 'iso-8859-4';              Codepage: 28594; Codec: TISO8859_4Codec),
    (Name: 'iso-8859-5';              Codepage: 28595; Codec: TISO8859_5Codec),
    (Name: 'iso-8859-6';              Codepage: 28596; Codec: TISO8859_6Codec),
    (Name: 'iso-8859-7';              Codepage: 28597; Codec: TISO8859_7Codec),
    (Name: 'iso-8859-8';              Codepage: 28598; Codec: TISO8859_8Codec),
    (Name: 'iso-8859-9';              Codepage: 28599; Codec: TISO8859_9Codec),
    (Name: 'iso-8859-13';             Codepage: 28603; Codec: TISO8859_13Codec),
    (Name: 'iso-8859-15';             Codepage: 28605; Codec: TISO8859_15Codec),
    (Name: 'x-Europa';                Codepage: 29001),
    (Name: 'iso-8859-8-i';            Codepage: 38598; Codec: TISO8859_8Codec), //guess
    (Name: 'iso-2022-jp';             Codepage: 50220),
    (Name: 'csISO2022JP';             Codepage: 50221),
    (Name: 'iso-2022-jp';             Codepage: 50222),
    (Name: 'iso-2022-kr';             Codepage: 50225),
    (Name: 'x-cp50227';               Codepage: 50227),
    (Name: 'euc-jp';                  Codepage: 51932),
    (Name: 'EUC-CN';                  Codepage: 51936),
    (Name: 'euc-kr';                  Codepage: 51949),
    (Name: 'hz-gb-2312';              Codepage: 52936),
    (Name: 'GB18030';                 Codepage: 54936),
    (Name: 'x-iscii-de';              Codepage: 57002),
    (Name: 'x-iscii-be';              Codepage: 57003),
    (Name: 'x-iscii-ta';              Codepage: 57004),
    (Name: 'x-iscii-te';              Codepage: 57005),
    (Name: 'x-iscii-as';              Codepage: 57006),
    (Name: 'x-iscii-or';              Codepage: 57007),
    (Name: 'x-iscii-ka';              Codepage: 57008),
    (Name: 'x-iscii-ma';              Codepage: 57009),
    (Name: 'x-iscii-gu';              Codepage: 57010),
    (Name: 'x-iscii-pa';              Codepage: 57011),
    (Name: 'utf-7';                   Codepage: 65000),
    (Name: 'utf-8';                   Codepage: 65001; Codec: TUTF8Codec) ); //143


function sdDefaultWideToAnsiBuffer(const WideBuf; var AnsiBuf; WideCount: integer;
  ADefaultChar: PAnsiChar; var DefaultCharUsed: boolean): integer;
// Convert an Unicode (UTF16 LE) memory block to Ansi. 
var
  W: word;
  WideIdx, AnsiIdx: integer;
  DefaultChar: AnsiChar;
begin
  DefaultCharUsed := False;
  DefaultChar := '?';
  if assigned(ADefaultChar) then
    DefaultChar := ADefaultChar^;
  WideIdx := 0;
  AnsiIdx := 0;
  while WideIdx < WideCount do
  begin
    W := TWordArray(WideBuf)[WideIdx];
    if W < $A0 then
    begin
      TByteArray(AnsiBuf)[AnsiIdx] := byte(W);
      inc(AnsiIdx);
    end else
    begin
      TByteArray(AnsiBuf)[AnsiIdx] := byte(DefaultChar);
      inc(AnsiIdx);
      DefaultCharUsed := True;
    end;
    inc(WideIdx);
  end;
  Result := AnsiIdx;
end;

function sdDefaultAnsiToWideBuffer(const AnsiBuf; var WideBuf; AnsiCount: integer;
  ADefaultChar: PAnsiChar; var DefaultCharUsed: boolean): integer;
// Convert ansi to an Unicode (UTF16 LE) memory block
var
  A: byte;
  WideIdx, AnsiIdx: integer;
  DefaultChar: AnsiChar;
begin
  DefaultCharUsed := False;
  DefaultChar := '?';
  if assigned(ADefaultChar) then
    DefaultChar := ADefaultChar^;
  AnsiIdx := 0;
  WideIdx := 0;
  while AnsiIdx < AnsiCount do
  begin
    A := TByteArray(AnsiBuf)[AnsiIdx];
    if A < $A0 then
    begin
      TWordArray(WideBuf)[WideIdx] := word(A);
      inc(WideIdx);
    end else
    begin
      TWordArray(WideBuf)[WideIdx] := word(DefaultChar);
      inc(WideIdx);
      DefaultCharUsed := True;
    end;
    inc(AnsiIdx);
  end;
  Result := AnsiIdx;
end;

// MultiByteToWideChar emulation

function MultiByteToWideChar(CodePage: word; dwFlags: longword; lpMultiByteStr: pansichar;
  cchMultiByte: integer; lpWideCharStr: pwidechar; cchWideChar: integer): integer;
var
  i: integer;
  CodecClass: TUnicodeCodecClass;
  Codec: TUnicodeCodec;
  ProcessedBytes, DestLength: integer;
  DefaultUsed: boolean;
  DefaultChar: PAnsiChar;
begin
  if CodePage = 65001 then
    // utf-8
    Result := sdUtf8ToWideBuffer(lpMultiByteStr^, lpWideCharStr^, cchMultiByte)
  else
  begin
    CodecClass := nil;
    for i := 0 to cCodepageToEncodingInfoCount - 1 do
    begin
      if cCodepageToEncodingInfo[i].Codepage = Codepage then
      begin
        CodecClass := cCodepageToEncodingInfo[i].Codec;
        break;
      end;
    end;

    // found a codec class?
    if assigned(CodecClass) then
    begin
      Codec := CodecClass.Create;
      try
        Codec.Decode(lpMultiByteStr, cchMultibyte, lpWideCharStr, cchWideChar * 2, ProcessedBytes, DestLength);
        Result := DestLength;
      finally
        Codec.Free;
      end;
    end else
    begin

      // no special codec found, so use default
      DefaultChar := '?';
      Result := sdDefaultAnsiToWideBuffer(lpMultiByteStr, lpWideCharStr, cchMultiByte,
        DefaultChar, DefaultUsed);
    end;
  end;
end;

// WideCharToMultiByte emulation

function WideCharToMultiByte(CodePage: word; dwFlags: longword; lpWideCharStr: pwidechar;
  cchWideChar: integer; lpMultiByteStr: pansichar; cchMultiByte: integer;
  lpDefaultChar: pansichar; lpUsedDefaultChar: pointer): integer;
var
  i: integer;
  CodecClass: TUnicodeCodecClass;
  Codec: TUnicodeCodec;
  ProcessedChars: integer;
  DefaultUsed: boolean;
  DefaultChar: PAnsiChar;
  Res: AnsiString;
  correcto : boolean;
begin
  if CodePage = 65001 then
    // utf-8
    Result := sdWideToUtf8Buffer(lpWideCharStr^, lpMultiByteStr^, cchWideChar,correcto)
  else
  begin
    CodecClass := nil;
    for i := 0 to cCodepageToEncodingInfoCount - 1 do
    begin
      if cCodepageToEncodingInfo[i].Codepage = Codepage then
      begin
        CodecClass := cCodepageToEncodingInfo[i].Codec;
        break;
      end;
    end;

    // found a codec class?
    if assigned(CodecClass) then
    begin
      Codec := CodecClass.Create;
      try
        Res := Codec.Encode(lpWideCharStr, cchWideChar, ProcessedChars);
        Result := length(Res);
        lpMultiByteStr := PAnsiChar(Res);
      finally
        Codec.Free;
      end;
    end else
    begin

      // no special codec found, so use default
      DefaultChar := '?';
      Result := sdDefaultWideToAnsiBuffer(lpMultiByteStr, lpWideCharStr, cchMultiByte,
        DefaultChar, DefaultUsed);
    end;
  end;
end;

end.