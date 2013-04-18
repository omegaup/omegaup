{ unit sdDebug

  universal method for debugging

  Exceptions often are a hindrance, so instead use these classes
  to give important info to the application or user with these
  three basic classes

  Besides debug methods, this unit also defines a few compatibility  types:
  The include file simdesign.inc defines $D5UP and after the
  uses-clause these types for D5 are defined. This way, many simdesign
  projects are compatible with Delphi 5.
  fpc: if lazarus + freepascal is defined, Utf8String just reverts to "string".

  Author: Nils Haeck M.Sc.
  Original Date: 08nov2010
  copyright (c) SimDesign BV (www.simdesign.nl)
}
unit sdDebug;

{$i simdesign.inc}

interface

uses
  Classes;

{$ifdef D5UP}
// D5 compatibility types
const
  MinsPerHour = 60;
  MinsPerDay = MinsPerHour * 24;
  soCurrent = soFromCurrent;
  soBeginning = soFromBeginning;
  soEnd = soFromEnd;
  
type
  Utf8String = AnsiString;
  TSeekOrigin = word;

  PIntegerArray = ^TIntegerArray;
  TIntegerArray = array of Integer;

  PByte = ^Byte;
  PInteger = ^Integer;
  PSingle = ^Single;
  PDouble = ^Double;

  // TFormatSettings stub
  TFormatSettings = record
  end;

  PWord = ^Word;

  function StrToFloatDef(S: AnsiString; Default: Double; AFormatSettings: TFormatSettings): Double;
  function StrToBool(S: AnsiString): Boolean;
  function StrToBoolDef(S: AnsiString; Default: Boolean): Boolean;
{$endif}

// lazarus compatibility
{$ifdef fpc}
type
  Utf8String = string;
{$endif fpc}

// Delphi unicode compatibility
{$ifndef UNICODE}
type
  UnicodeString = WideString;
  RawByteString = AnsiString;
{$endif UNICODE}

type
  TsdWarnStyle = (wsInfo, wsHint, wsWarn, wsFail);

const
  cWarnStyleNames: array[TsdWarnStyle] of Utf8String = ('info', 'hint', 'warn', 'fail');

type
  // event with debug data
  TsdDebugEvent = procedure(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String) of object;

  // simple update event
  TsdUpdateEvent = procedure(Sender: TObject) of object;

  TDebugComponent = class(TComponent)
  protected
    FOnDebugOut: TsdDebugEvent;
  public
    procedure DoDebugOut(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String); virtual;
    // Connect to OnDebugOut to get debug information in the client application
    property OnDebugOut: TsdDebugEvent read FOnDebugOut write FOnDebugOut;
  end;

  TDebugObject = class(TObject)
  protected
    FOnDebugOut: TsdDebugEvent;
    procedure DoDebugOut(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String); virtual;
  public
    property OnDebugOut: TsdDebugEvent read FOnDebugOut write FOnDebugOut;
  end;

  TDebugPersistent = class(TPersistent)
  protected
    FOwner: TDebugComponent;
    procedure DoDebugOut(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String); virtual;
  public
    constructor CreateDebug(AOwner: TDebugComponent); virtual;
  end;

{ Functions }

function sdDebugMessageToString(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String): Utf8String;

function sdClassName(AObject: TObject): Utf8String;

implementation

{$ifdef D5UP}
// D5 compatibility types
uses
  SysUtils;

function StrToFloatDef(S: AnsiString; Default: Double; AFormatSettings: TFormatSettings): Double;
begin
  try
    Result:= StrToFloat(S);
  except
    Result:= Default;
  end;
end;

// Only basic support
function StrToBool(S: AnsiString): Boolean;
begin
  S := LowerCase(S);
  if (S = 'no') or (S = '0') or (S = 'false') then
    Result := False
  else
    if (S = 'yes') or (S = '1') or (S = 'true') then
      Result:= True
    else
      raise EConvertError.Create('');
end;

function StrToBoolDef(S: AnsiString; Default: Boolean): Boolean;
begin
  try
    Result := StrToBool(S);
  except
    Result := Default;
  end;
end;
{$endif}

{ TDebugComponent }

procedure TDebugComponent.DoDebugOut(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String);
var
  AOwner: TComponent;
begin
  AOwner := Self;
  while AOwner is TDebugComponent do
  begin
    if assigned(TDebugComponent(AOwner).FOnDebugOut) then
    begin
      TDebugComponent(AOwner).FOnDebugOut(Sender, WarnStyle, AMessage);
      exit;
    end;
    AOwner := AOwner.Owner;
  end;
end;

{ TDebugObject }

procedure TDebugObject.DoDebugOut(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String);
begin
  if assigned(FOnDebugOut) then
    FOnDebugOut(Sender, WarnStyle, AMessage);
end;

{ TDebugPersistent }

constructor TDebugPersistent.CreateDebug(AOwner: TDebugComponent);
begin
  inherited Create;
  FOwner := AOwner;
end;

procedure TDebugPersistent.DoDebugOut(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String);
begin
  if FOwner is TDebugComponent then
    TDebugComponent(FOwner).DoDebugOut(Sender, WarnStyle, AMessage);
end;

{ Functions }

function sdDebugMessageToString(Sender: TObject; WarnStyle: TsdWarnStyle; const AMessage: Utf8String): Utf8String;
var
  SenderString: Utf8String;
begin
  if assigned(Sender)  then
    SenderString := Utf8String(Sender.ClassName)
  else
    SenderString := '';
  Result := '[' + cWarnStyleNames[WarnStyle] + '] ' + SenderString + ': ' + AMessage;
end;

function sdClassName(AObject: TObject): Utf8String;
begin
  Result := 'nil';
  if assigned(AObject) then
    Result := Utf8String(AObject.ClassName);
end;

end.


