{ sdStreams.pas

  - TsdFastMemStream with improved capacity setting
  - TsdStringStream
  - TsdBufferWriter

  Author: Nils Haeck M.Sc.
  copyright (c) 2002 - 2011 SimDesign BV (www.simdesign.nl)
}
unit sdStreams;

{$ifdef lcl}{$MODE Delphi}{$endif}

{$define simdesign.inc}

interface

uses
  Classes, SysUtils, sdDebug;

type

  // TsdFastMemStream deals differently with capacity compared to a normal
  // TMemoryStream; it increases the capacity with the natural growing function
  // (fibonacci) each time, and has an initial capacity of $1000. The initial
  // capacity is configurable with the create parameter.
  TsdFastMemStream = class(TStream)
  private
    FMemory: Pointer;
    FPosition: longint;
    FFib1: longint;
    FCapacity: longint;
    FSize: longint;
  protected
    procedure SetCapacity(Value: longint);
    procedure SetSize(NewSize: Longint); override;
  public
    constructor Create(InitialCapacity: longint = $1000);
    destructor Destroy; override;
    procedure Clear;
    function Read(var Buffer; Count: Longint): Longint; override;
    function Write(const Buffer; Count: Longint): Longint; override;
    function Seek(Offset: Longint; Origin: Word): Longint; override;
    procedure LoadFromFile(AFilename: string);
    procedure LoadFromStream(Stream: TStream);
    procedure SaveToFile(AFilename: string);
    procedure SaveToStream(Stream: TStream);
    property Memory: Pointer read FMemory;
    property Size: longint read FSize write SetSize;
  end;

  // Delphi's implementation of TStringStream is severely flawed, it does a SetLength
  // on each write, which slows down everything to a crawl. This implementation over-
  // comes this issue.
  TsdStringStream = class(TsdFastMemStream)
  public
    constructor Create(const S: Utf8String);
    function DataString: Utf8String;
  end;

  // TsdBufferWriter is a buffered stream that takes another stream (ASource)
  // and writes only buffer-wise to it, and writes to the stream are first
  // done to the buffer. This stream type can only support writing.
  TsdBufferWriter = class(TsdFastMemStream)
  private
    FSource: TStream;
    FChunkSize: integer;
    FRawBuffer: array of byte;
    FRawPosition: Integer;
  protected
    procedure WriteChunk(Count: integer);
  public
    // Create the buffered writer stream by passing the destination stream in ASource,
    // this destination stream must already be initialized.
    constructor Create(ASource: TStream; AChunkSize: integer);
    destructor Destroy; override;
    function Read(var Buffer; Count: Longint): Longint; override;
    function Write(const Buffer; Count: Longint): Longint; override;
  end;

implementation

{ TsdFastMemStream }

procedure TsdFastMemStream.Clear;
begin
  SetCapacity(0);
  FSize := 0;
  FPosition := 0;
end;

constructor TsdFastMemStream.Create(InitialCapacity: Integer);
begin
  inherited Create;
  FFib1 := InitialCapacity div 2;
  FCapacity := InitialCapacity;
  if FFib1 < 4 then
    FFib1 := 4;
  if FCapacity < 4 then
    FCapacity := 4;
  ReallocMem(FMemory, FCapacity);
end;

destructor TsdFastMemStream.Destroy;
begin
  ReallocMem(FMemory, 0);
  inherited;
end;

procedure TsdFastMemStream.LoadFromFile(AFilename: string);
var
  Stream: TStream;
begin
  Stream := TFileStream.Create(AFileName, fmOpenRead or fmShareDenyWrite);
  try
    LoadFromStream(Stream);
  finally
    Stream.Free;
  end;
end;

procedure TsdFastMemStream.LoadFromStream(Stream: TStream);
var
  Count: Longint;
begin
  Stream.Position := 0;
  Count := Stream.Size;
  SetSize(Count);
  if Count <> 0 then Stream.ReadBuffer(FMemory^, Count);
end;

function TsdFastMemStream.Read(var Buffer; Count: Integer): Longint;
begin
  if (FPosition >= 0) and (Count >= 0) then
  begin
    Result := FSize - FPosition;
    if Result > 0 then
    begin
      if Result > Count then
        Result := Count;
      Move(Pointer(Longint(FMemory) + FPosition)^, Buffer, Result);
      Inc(FPosition, Result);
      Exit;
    end;
  end;
  Result := 0;
end;

procedure TsdFastMemStream.SaveToFile(AFilename: string);
var
  Stream: TStream;
begin
  Stream := TFileStream.Create(AFileName, fmCreate);
  try
    SaveToStream(Stream);
  finally
    Stream.Free;
  end;
end;

procedure TsdFastMemStream.SaveToStream(Stream: TStream);
begin
  if FSize <> 0 then Stream.WriteBuffer(FMemory^, FSize);
end;

function TsdFastMemStream.Seek(Offset: Integer; Origin: Word): Longint;
begin
  case Origin of
    soFromBeginning: FPosition := Offset;
    soFromCurrent: Inc(FPosition, Offset);
    soFromEnd: FPosition := FSize + Offset;
  end;
  Result := FPosition;
end;

procedure TsdFastMemStream.SetCapacity(Value: longint);
// Fibonacci 0,1,1,2,3,5,8,...  FCapacity is Fib2.
// Fibonacci is a natural growing function where
// 0 + 1 = 1; 1 + 1 = 2; 1 + 2 = 3; 2 + 3 = 5; etc
var
  Fib3: longint;
begin
  while FCapacity < Value do
  begin
    Fib3 := FFib1 + FCapacity;
    FFib1 := FCapacity;
    FCapacity := Fib3;
  end;
  ReallocMem(FMemory, FCapacity);
end;

procedure TsdFastMemStream.SetSize(NewSize: longint);
var
  OldPosition: Longint;
begin
  OldPosition := FPosition;
  SetCapacity(NewSize);
  FSize := NewSize;
  if OldPosition > NewSize then
    Seek(0, soFromEnd);
end;

function TsdFastMemStream.Write(const Buffer; Count: Integer): Longint;
var
  NewPos: Longint;
begin
  if (FPosition >= 0) and (Count >= 0) then
  begin
    NewPos := FPosition + Count;
    if NewPos > 0 then
    begin
      if NewPos > FSize then
      begin
        if NewPos > FCapacity then
          SetCapacity(NewPos);
        FSize := NewPos;
      end;
      System.Move(Buffer, Pointer(Longint(FMemory) + FPosition)^, Count);
      FPosition := NewPos;
      Result := Count;
      Exit;
    end;
  end;
  Result := 0;
end;

{ TsdStringStream }

constructor TsdStringStream.Create(const S: Utf8String);
begin
  inherited Create;
  SetSize(length(S));
  if Size > 0 then
  begin
    Write(S[1], Size);
    Position := 0;
  end;
end;

function TsdStringStream.DataString: Utf8String;
begin
  SetLength(Result, Size);
  if Size > 0 then
  begin
    Position := 0;
    Read(Result[1], length(Result));
  end;
end;

{ TsdBufferWriter }

constructor TsdBufferWriter.Create(ASource: TStream; AChunkSize: integer);
begin
  inherited Create;
  FSource := ASource;
  FChunkSize := AChunkSize;
  SetLength(FRawBuffer, FChunkSize);
end;

destructor TsdBufferWriter.Destroy;
begin
  // write the last chunk, if any
  WriteChunk(FRawPosition);
  // free the rawbuffer
  SetLength(FRawBuffer, 0);
  inherited;
end;

function TsdBufferWriter.Read(var Buffer; Count: Integer): Longint;
begin
  // not implemented
  raise Exception.Create('not implemented');
end;

function TsdBufferWriter.Write(const Buffer; Count: Integer): Longint;
var
  Idx, Siz: integer;
begin
  // index in the source buffer
  Idx := 0;
  // remaining size
  Siz := Count;

  // surplus
  while FRawPosition + Siz >= FChunkSize do
  begin
    Move(TByteArray(Buffer)[Idx], FRawBuffer[FRawPosition], FChunkSize - FRawPosition);
    WriteChunk(FChunkSize);
    dec(Siz, FChunkSize - FRawPosition);
    inc(Idx, FChunkSize - FRawPosition);
    FRawPosition := 0;
  end;

  // copy the raw buffer
  Move(TByteArray(Buffer)[Idx], FRawBuffer[FRawPosition], Siz);
  inc(FRawPosition, Siz);

  Result := Count;
end;

procedure TsdBufferWriter.WriteChunk(Count: integer);
begin
  if Count > 0 then
  begin
    FSource.WriteBuffer(FRawBuffer[0], Count);
  end;
end;

end.
