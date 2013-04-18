{
  Unit sdSortedLists

  Author: Nils Haeck M.Sc.
  Copyright (c) 2003-2010 SimDesign B.V.
  Creation Date: 02Aug2003

  This software may ONLY be used or replicated in accordance with
  the LICENSE found in this source distribution.

  Description:
  Implements a TObjectList descendant that can sort items using
  the quicksort algorithm, as well as find items using a binary
  tree algorithm.

  Version: 1.0

}
unit sdSortedLists;

interface

uses
  Contnrs, SysUtils, sdDebug;

type

  TItemCompareEvent = function(Item1, Item2: TObject; Info: pointer): integer of object;
  TItemCompareMethod = function(Item1, Item2: TObject; Info: pointer): integer;
  TPointerCompareMethod = function(Ptr1, Ptr2: pointer): integer;

  TCustomObjectList = class(TObjectList)
  public
    procedure Append(AItem: TObject);
  end;

  // Keep a sorted list of objects, sort them by the object's globally unique ID (Guid).
  // Override method GetGuid, it should return the guid of object AItem.
  TGuidList = class(TCustomObjectList)
  protected
    function GetGuid(AItem: TObject): TGuid; virtual; abstract;
    function IndexByGuid(const AGuid: TGuid; out Index: integer): boolean;
  public
    function HasGuid(const AGuid: TGuid): boolean;
    procedure RemoveByGuid(const AGuid: TGuid);
    function Add(AItem: TObject): integer;
  end;

  // TCustomSortedList is a TObjectList descendant providing easy sorting
  // capabilities, while keeping simplicity. Override the DoCompare method
  // to compare two items.
  TCustomSortedList = class(TCustomObjectList)
  private
    FSorted: boolean;
    procedure SetSorted(AValue: boolean);
  protected
    // Override this method to implement the object comparison between two
    // items. The default just compares the item pointers
    function DoCompare(Item1, Item2: TObject): integer; virtual;
  public
    constructor Create(AOwnsObjects: boolean = true);
    function Add(AItem: TObject): integer;
    // AddUnique behaves just like Add but checks if the item to add is unique
    // by checking the result of the Find function. If the item is found it is
    // replaced by the new item (old item removed), unless RaiseError = True, in
    // that case an exception is raised.
    function AddUnique(Item: TObject; RaiseError: boolean = false): integer; virtual;
    function Find(Item: TObject; out Index: integer): boolean; virtual;
    // Find (multiple) items equal to Item, and return Index of first equal
    // item and the number of multiples in Count
    procedure FindMultiple(Item: TObject; out AIndex, ACount: integer); virtual;
    procedure Sort; virtual;
    property Sorted: boolean read FSorted write SetSorted default true;
  end;

  // TSortedList is an object list that provides an events or method template
  // to compare items. Assign either OnCompare (for an event) or CompareMethod
  // (for a method template) to do the comparison of items. Additional information
  // required for the compare method can be passed with the CompareInfo pointer.
  TSortedList = class(TCustomSortedList)
  private
    FCompareInfo: pointer;
    FOnCompare: TItemCompareEvent;
    FCompareMethod: TItemCompareMethod;
  protected
    function DoCompare(Item1, Item2: TObject): integer; override;
  public
    property CompareInfo: pointer read FCompareInfo write FCompareInfo;
    // Use CompareMethod if you want to specify a compare method as stand-alone method
    property CompareMethod: TItemCompareMethod read FCompareMethod write FCompareMethod;
    // Use OnCompare if you want to specify a compare method as a method of a class
    property OnCompare: TItemCompareEvent read FOnCompare write FOnCompare;
  end;

// Some basic compare routines
function CompareCardinal(C1, C2: Cardinal): integer;
function CompareInteger(Int1, Int2: integer): integer;
function CompareLongWord(LW1, LW2: longword): integer;
function CompareInt64(const Int1, Int2: int64): integer;
function ComparePointer(Item1, Item2: pointer): integer;
function CompareBool(Bool1, Bool2: boolean): integer;
function CompareSingle(const Single1, Single2: single): integer;
function CompareDouble(const Double1, Double2: double): integer;
// Compare globally unique ID (TGUID, defined in SysUtils)
function CompareGuid(const Guid1, Guid2: TGUID): integer;

// GUID methods
function IsEqualGuid(const Guid1, Guid2: TGUID): boolean;
function IsEmptyGuid(const AGuid: TGUID): boolean;
function NewGuid: TGUID;

type

  TCustomSorter = class
  private
    FCompareMethod: TPointerCompareMethod;
    FFirst: pointer;
    FStride: integer;
    FCount: integer;
  public
    property CompareMethod: TPointerCompareMethod read FCompareMethod write FCompareMethod;
    property First: pointer read FFirst write FFirst;
    property Stride: integer read FStride write FStride;
    property Count: integer read FCount write FCount;
    procedure Sort;
  end;

procedure sdSortArraySingle(AFirst: PSingle; ACount: integer);
procedure sdSortArrayDouble(AFirst: PDouble; ACount: integer);
procedure sdSortArrayInteger(AFirst: PInteger; ACount: integer);

function sdAverageOfArrayInteger(AFirst: PInteger; ACount: integer): double;
function sdMinimumOfArrayInteger(AFirst: PInteger; ACount: integer): integer;
function sdMaximumOfArrayInteger(AFirst: PInteger; ACount: integer): integer;

function sdMinimumOfArrayDouble(AFirst: PDouble; ACount: integer): double;
function sdMaximumOfArrayDouble(AFirst: PDouble; ACount: integer): double;

// Walking average of array in SFirst, put result in DFirst. Both must be of
// length ACount. The average is done with a window of AWindowSize, and the
// center pixel in ACenter (e.g. ACenter = 1, AWindowSize = 3 for 3 values w.a.).
// After running, the values must still be divided by AWindowSize
procedure sdWalkingAverageArrayInteger(SFirst, DFirst: PInteger; ACount, ACenter, AWindowSize: integer);

// Walking median of array in SFirst, put result in DFirst. Both must be of
// length ACount. The median is done with a window of AWindowSize, and the
// center pixel in ACenter (e.g. ACenter = 1, AWindowSize = 3 for 3 values w.m.).
procedure sdWalkingMedianArrayInteger(SFirst, DFirst: PInteger; ACount, ACenter, AWindowSize: integer);

procedure sdWalkingAverageArrayDouble(SFirst, DFirst: PDouble; ACount, ACenter, AWindowSize: integer);
procedure sdWalkingMedianArrayDouble(SFirst, DFirst: PDouble; ACount, ACenter, AWindowSize: integer);

resourcestring

  sAddingNonUniqueObject = 'Adding non-unique object to list is not allowed';
  sListMustBeSorted = 'List must be sorted';

const
  cEmptyGuid: TGUID = (D1: 0; D2: 0; D3: 0; D4: (0, 0, 0, 0, 0, 0, 0, 0));

implementation

function CompareCardinal(C1, C2: Cardinal): integer;
begin
  if C1 < C2 then
    Result := -1
  else
    if C1 > C2 then
      Result := 1
    else
      Result := 0;
end;

function CompareInteger(Int1, Int2: integer): integer;
begin
  if Int1 < Int2 then
    Result := -1
  else
    if Int1 > Int2 then
      Result := 1
    else
      Result := 0;
end;

function CompareLongWord(LW1, LW2: longword): integer;
begin
  if LW1 < LW2 then
    Result := -1
  else
    if LW1 > LW2 then
      Result := 1
    else
      Result := 0;
end;

function CompareInt64(const Int1, Int2: int64): integer;
begin
  if Int1 < Int2 then
    Result := -1
  else
    if Int1 > Int2 then
      Result := 1
    else
      Result := 0;
end;

function ComparePointer(Item1, Item2: pointer): integer;
begin
  if integer(Item1) < integer(Item2) then
    Result := -1
  else
    if integer(Item1) > integer(Item2) then
      Result := 1
    else
      Result := 0;
end;

function CompareBool(Bool1, Bool2: boolean): integer;
begin
  if Bool1 < Bool2 then
    Result := -1
  else
    if Bool1 > Bool2 then
      Result := 1
    else
      Result := 0;
end;

function CompareSingle(const Single1, Single2: single): integer;
begin
  if Single1 < Single2 then
    Result := -1
  else
    if Single1 > Single2 then
      Result := 1
    else
      Result := 0;
end;

function CompareDouble(const Double1, Double2: double): integer;
begin
  if Double1 < Double2 then
    Result := -1
  else
    if Double1 > Double2 then
      Result := 1
    else
      Result := 0;
end;

function CompareGuid(const Guid1, Guid2: TGUID): integer;
var
  i: integer;
  a, b: PCardinal;
begin
  a := PCardinal(@Guid1);
  b := PCardinal(@Guid2);
  i := 0;
  Result := CompareCardinal(a^, b^);
  while (Result = 0) and (i < 3) do
  begin
    inc(i);
    inc(a);
    inc(b);
    Result := CompareCardinal(a^, b^);
  end;
end;


function IsEqualGuid(const Guid1, Guid2: TGUID): boolean;
begin
  Result := CompareGuid(Guid1, Guid2) = 0;
end;

function IsEmptyGuid(const AGuid: TGUID): boolean;
begin
  Result := CompareGuid(AGuid, cEmptyGuid) = 0;
end;

function NewGuid: TGUID;
var
  Guid: TGUID;
begin
  CreateGUID(Guid);
  Result := Guid;
end;

// For use with custom sorter and procedures

function ComparePSingle(Ptr1, Ptr2: pointer): integer;
begin
  Result := CompareSingle(PSingle(Ptr1)^, PSingle(Ptr2)^);
end;

function ComparePDouble(Ptr1, Ptr2: pointer): integer;
begin
  Result := CompareDouble(PDouble(Ptr1)^, PDouble(Ptr2)^);
end;

function ComparePInteger(Ptr1, Ptr2: pointer): integer;
begin
  Result := CompareInteger(PInteger(Ptr1)^, PInteger(Ptr2)^);
end;

{ TCustomObjectList }

procedure TCustomObjectList.Append(AItem: TObject);
begin
  Insert(Count, AItem);
end;

{ TGuidList }

function TGuidList.Add(AItem: TObject): integer;
begin
  // do we have AItem?
  if IndexByGuid(GetGuid(AItem), Result) then

    // Replace existing
    Put(Result, AItem)

  else
  begin
    // Insert
    Insert(Result, AItem);
  end;
end;

function TGuidList.HasGuid(const AGuid: TGuid): boolean;
var
  Index: integer;
begin
  Result := IndexByGuid(AGuid, Index);
end;

function TGuidList.IndexByGuid(const AGuid: TGuid;
  out Index: integer): boolean;
var
  Min, Max: integer;
begin
  Result := False;

  // Find position for insert - binary method
  Index := 0;
  Min := 0;
  Max := Count;
  while Min < Max do
  begin
    Index := (Min + Max) div 2;
    case CompareGuid(GetGuid(List[Index]), AGuid) of
    -1: Min := Index + 1;
     0: begin
          Result := True;
          exit;
        end;
     1: Max := Index;
    end;
  end;
  Index := Min;
end;

procedure TGuidList.RemoveByGuid(const AGuid: TGuid);
var
  Index: integer;
begin
  if IndexByGuid(AGuid, Index) then
    Delete(Index);
end;

{ TCustomSortedList }

function TCustomSortedList.Add(AItem: TObject): integer;
begin
  if Sorted then
  begin

    Find(AItem, Result);
    Insert(Result, AItem);

  end else

    Result := inherited Add(AItem);
end;

function TCustomSortedList.AddUnique(Item: TObject; RaiseError: boolean): integer;
begin
  if Find(Item, Result) then
  begin
    if RaiseError then
      raise Exception.Create(sAddingNonUniqueObject);
    Delete(Result);
  end;
  Insert(Result, Item);
end;

constructor TCustomSortedList.Create(AOwnsObjects: boolean);
begin
  inherited Create(AOwnsObjects);
  FSorted := True;
end;

function TCustomSortedList.DoCompare(Item1, Item2: TObject): integer;
begin
  Result := ComparePointer(Item1, Item2);
end;

function TCustomSortedList.Find(Item: TObject; out Index: integer): boolean;
var
  AMin, AMax: integer;
begin
  Result := False;

  if Sorted then
  begin

    // Find position for insert - binary method
    Index := 0;
    AMin := 0;
    AMax := Count;
    while AMin < AMax do
    begin
      Index := (AMin + AMax) div 2;
      case DoCompare(List[Index], Item) of
      -1: AMin := Index + 1;
       0: begin
            Result := True;
            exit;
          end;
       1: AMax := Index;
      end;
    end;
    Index := AMin;

  end else
  begin

    // If not a sorted list, then find it with the IndexOf() method
    Index := IndexOf(Item);
    if Index >= 0 then
    begin
      Result := True;
      exit;
    end;

    // Not found: set it to Count
    Index := Count;
  end;
end;

procedure TCustomSortedList.FindMultiple(Item: TObject; out AIndex, ACount: integer);
var
  IdxStart: integer;
  IdxClose: integer;
begin
  if not Sorted then
    raise Exception.Create(sListMustBeSorted);

  ACount := 0;

  // Find one
  if not Find(Item, AIndex) then
    exit;

  // Check upward from item
  IdxStart := AIndex;
  while (IdxStart > 0) and (DoCompare(List[IdxStart - 1], Item) = 0) do
    dec(IdxStart);

  // Check downward from item
  IdxClose := AIndex;
  while (IdxClose < Count - 1) and (DoCompare(List[IdxClose + 1], Item) = 0) do
    inc(IdxClose);

  // Result
  AIndex := IdxStart;
  ACount := IdxClose - IdxStart + 1;
end;

procedure TCustomSortedList.SetSorted(AValue: boolean);
begin
  if AValue <> FSorted then
  begin
    FSorted := AValue;
    if FSorted then
      Sort;
  end;
end;

procedure TCustomSortedList.Sort;
  //local
  procedure QuickSort(iLo, iHi: Integer);
  var
    Lo, Hi, Mid: longint;
  begin
    Lo := iLo;
    Hi := iHi;
    Mid:= (Lo + Hi) div 2;
    repeat
      while DoCompare(List[Lo], List[Mid]) < 0 do
        Inc(Lo);
      while DoCompare(List[Hi], List[Mid]) > 0 do
        Dec(Hi);
      if Lo <= Hi then
      begin
        // Swap pointers;
        Exchange(Lo, Hi);
        if Mid = Lo then
          Mid := Hi
        else
          if Mid = Hi then
            Mid := Lo;
        Inc(Lo);
        Dec(Hi);
      end;
    until Lo > Hi;

    if Hi > iLo then
      QuickSort(iLo, Hi);

    if Lo < iHi then
      QuickSort(Lo, iHi);
  end;
// main
begin
  if Count > 1 then
  begin
    QuickSort(0, Count - 1);
  end;
  FSorted := True;
end;

{ TSortedList }

function TSortedList.DoCompare(Item1, Item2: TObject): integer;
begin
  if assigned(FOnCompare) then
    Result := FOnCompare(Item1, Item2, FCompareInfo)
  else if assigned(FCompareMethod) then
    Result := FCompareMethod(Item1, Item2, FCompareInfo)
  else
    Result := ComparePointer(Item1, Item2);
end;

{ TCustomSorter }

procedure TCustomSorter.Sort;
var
  Buf: array of byte;
  PB: Pbyte;
  // local
  function DoCompare(Idx1, Idx2: integer): integer;
  var
    P1, P2: PByte;
  begin
    P1 := First;
    inc(P1, FStride * Idx1);
    P2 := First;
    inc(P2, FStride * Idx2);
    Result := FCompareMethod(P1, P2);
  end;
  // local
  procedure Exchange(Idx1, Idx2: integer);
  var
    P1, P2: PByte;
  begin
    P1 := First;
    inc(P1, FStride * Idx1);
    P2 := First;
    inc(P2, FStride * Idx2);
    Move(P1^, PB^, Stride);
    Move(P2^, P1^, Stride);
    Move(PB^, P2^, Stride);
  end;
  //local
  procedure QuickSort(iLo, iHi: Integer);
  var
    Lo, Hi, Mid: longint;
  begin
    Lo := iLo;
    Hi := iHi;
    Mid:= (Lo + Hi) div 2;
    repeat
      while DoCompare(Lo, Mid) < 0 do
        Inc(Lo);
      while DoCompare(Hi, Mid) > 0 do
        Dec(Hi);
      if Lo <= Hi then
      begin
        // Swap pointers;
        Exchange(Lo, Hi);
        if Mid = Lo then
          Mid := Hi
        else
          if Mid = Hi then
            Mid := Lo;
        Inc(Lo);
        Dec(Hi);
      end;
    until Lo > Hi;

    if Hi > iLo then
      QuickSort(iLo, Hi);

    if Lo < iHi then
      QuickSort(Lo, iHi);
  end;
// main
begin
  if Count > 1 then
  begin
    SetLength(Buf, Stride);
    PB := @Buf[0];
    QuickSort(0, Count - 1);
  end;
end;

procedure sdSortArraySingle(AFirst: PSingle; ACount: integer);
var
  Sorter: TCustomSorter;
begin
  Sorter := TCustomSorter.Create;
  try
    Sorter.CompareMethod := ComparePSingle;
    Sorter.First := AFirst;
    Sorter.Stride := SizeOf(Single);
    Sorter.Count := ACount;
    Sorter.Sort;
  finally
    Sorter.Free;
  end;
end;

procedure sdSortArrayDouble(AFirst: PDouble; ACount: integer);
var
  Sorter: TCustomSorter;
begin
  Sorter := TCustomSorter.Create;
  try
    Sorter.CompareMethod := ComparePDouble;
    Sorter.First := AFirst;
    Sorter.Stride := SizeOf(Double);
    Sorter.Count := ACount;
    Sorter.Sort;
  finally
    Sorter.Free;
  end;
end;

procedure sdSortArrayInteger(AFirst: PInteger; ACount: integer);
var
  Sorter: TCustomSorter;
begin
  Sorter := TCustomSorter.Create;
  try
    Sorter.CompareMethod := ComparePInteger;
    Sorter.First := AFirst;
    Sorter.Stride := SizeOf(integer);
    Sorter.Count := ACount;
    Sorter.Sort;
  finally
    Sorter.Free;
  end;
end;

function sdAverageOfArrayInteger(AFirst: PInteger; ACount: integer): double;
var
  i: integer;
  Total: int64;
begin
  Total := 0;
  for i := 0 to ACount - 1 do
  begin
    inc(Total, AFirst^);
    inc(AFirst);
  end;

  Result := Total / ACount;
end;

function sdMinimumOfArrayInteger(AFirst: PInteger; ACount: integer): integer;
begin
  if ACount = 0 then
  begin
    Result := 0;
    exit;
  end;

  Result := AFirst^;

  while ACount > 0 do
  begin
    if AFirst^ < Result then
      Result := AFirst^;
    dec(ACount);
    inc(AFirst);
  end;
end;

function sdMaximumOfArrayInteger(AFirst: PInteger; ACount: integer): integer;
begin
  if ACount = 0 then
  begin
    Result := 0;
    exit;
  end;

  Result := AFirst^;

  while ACount > 0 do
  begin
    if AFirst^ > Result then
      Result := AFirst^;
    dec(ACount);
    inc(AFirst);
  end;
end;

function sdMinimumOfArrayDouble(AFirst: PDouble; ACount: integer): double;
begin
  if ACount = 0 then
  begin
    Result := 0;
    exit;
  end;

  Result := AFirst^;

  while ACount > 0 do
  begin
    if AFirst^ > Result then
      Result := AFirst^;
    dec(ACount);
    inc(AFirst);
  end;
end;

function sdMaximumOfArrayDouble(AFirst: PDouble; ACount: integer): double;
begin
  if ACount = 0 then
  begin
    Result := 0;
    exit;
  end;

  Result := AFirst^;

  while ACount > 0 do
  begin
    if AFirst^ > Result then
      Result := AFirst^;
    dec(ACount);
    inc(AFirst);
  end;
end;

procedure sdWalkingAverageArrayInteger(SFirst, DFirst: PInteger; ACount, ACenter, AWindowSize: integer);
var
  i: integer;
  SLast: PInteger;
  Cum: integer; // cumulative
begin
  // Only process if we have enough values
  if (ACount < AWindowSize) or (AWindowSize < ACenter) or (AWindowSize <= 0) then
    exit;

  // Start area
  SLast := SFirst;

  // Initialize cumulative
  Cum := SLast^ * ACenter;

  // Collect values into window
  for i := 0 to (AWindowSize - ACenter) - 1 do
  begin
    Cum := Cum + SFirst^;
    inc(SFirst);
  end;

  // Do first part
  for i := 0 to ACenter - 1 do
  begin
    DFirst^ := Cum;
    inc(DFirst);
    Cum := Cum + SFirst^;
    Cum := Cum - SLast^;
    inc(SFirst);
  end;

  // Bulk
  for i := 0 to ACount - AWindowSize - 1 do
  begin
    DFirst^ := Cum;
    inc(DFirst);
    inc(Cum, SFirst^);
    dec(Cum, SLast^);
    inc(SFirst);
    inc(SLast);
  end;

  // make sure we're at the last element, not one beyond
  dec(SFirst);

  // Close area
  for i := ACenter to AWindowSize - 1 do
  begin
    DFirst^ := Cum;
    inc(DFirst);
    Cum := Cum + SFirst^;
    Cum := Cum - SLast^;
    inc(SLast);
  end;
end;

procedure sdWalkingAverageArrayDouble(SFirst, DFirst: PDouble; ACount, ACenter, AWindowSize: integer);
var
  i: integer;
  SLast: PDouble;
  Cum, Scale: double;
begin
  // Only process if we have enough values
  if (ACount < AWindowSize) or (AWindowSize < ACenter) or (AWindowSize <= 0) then
    exit;

  // Start area
  Scale := 1 / AWindowSize;
  SLast := SFirst;

  // Initialize cumulative
  Cum := SLast^ * ACenter;

  // Collect values into window
  for i := 0 to (AWindowSize - ACenter) - 1 do
  begin
    Cum := Cum + SFirst^;
    inc(SFirst);
  end;

  // Do first part
  for i := 0 to ACenter - 1 do
  begin
    DFirst^ := Cum * Scale;
    inc(DFirst);
    Cum := Cum + SFirst^;
    Cum := Cum - SLast^;
    inc(SFirst);
  end;

  // Bulk
  for i := 0 to ACount - AWindowSize - 1 do
  begin
    DFirst^ := Cum * Scale;
    inc(DFirst);
    Cum := Cum + SFirst^;
    Cum := Cum - SLast^;
    inc(SFirst);
    inc(SLast);
  end;

  // make sure we're at the last element, not one beyond
  dec(SFirst);

  // Close area
  for i := ACenter to AWindowSize - 1 do
  begin
    DFirst^ := Cum * Scale;
    inc(DFirst);
    Cum := Cum + SFirst^;
    Cum := Cum - SLast^;
    inc(SLast);
  end;
end;

procedure sdWalkingMedianArrayInteger(SFirst, DFirst: PInteger; ACount, ACenter, AWindowSize: integer);
var
  W, Ws: array of integer;
  WsCenter, WEnd: PInteger;
  i, WSize, WM1Size: integer;
  Sorter: TCustomSorter;
  // local, add SFirst^ value to the unsorted array
  procedure AddToCumulative;
  begin
    Move(W[1], W[0], WM1Size);
    WEnd^ := SFirst^;
  end;
  // local, this sorts the values, and gets the median
  function GetCumulative: integer;
  begin
    Move(W[0], Ws[0], WSize);
    Sorter.Sort;
    Result := WsCenter^;
  end;
// main
begin
  // Only process if we have enough values
  if (ACount < AWindowSize) or (AWindowSize < ACenter) or (AWindowSize <= 0) then
    exit;

  // Initialization
  SetLength(W, AWindowSize);
  SetLength(Ws, AWindowSize);
  WsCenter := @Ws[ACenter];
  WEnd := @W[AWindowSize - 1];
  WSize := AWindowSize * SizeOf(integer);
  WM1Size := (AWindowSize - 1) * SizeOf(integer);

  Sorter := TCustomSorter.Create;
  try
    Sorter.CompareMethod := ComparePInteger;
    Sorter.First := @Ws[0];
    Sorter.Stride := SizeOf(integer);
    Sorter.Count := AWindowSize;

    // Initialize cumulative
    for i := 0 to ACenter - 1 do
      AddToCumulative;

    // Collect values into window
    for i := 0 to (AWindowSize - ACenter) - 1 do
    begin
      AddToCumulative;
      inc(SFirst);
    end;

    // Do first part
    for i := 0 to ACenter - 1 do
    begin
      DFirst^ := GetCumulative;
      inc(DFirst);
      AddToCumulative;
      inc(SFirst);
    end;

    // Bulk
    for i := 0 to ACount - AWindowSize - 1 do
    begin
      DFirst^ := GetCumulative;
      inc(DFirst);
      AddToCumulative;
      inc(SFirst);
    end;

    // make sure we're at the last element, not one beyond
    dec(SFirst);

    // Close area
    for i := ACenter to AWindowSize - 1 do
    begin
      DFirst^ := GetCumulative;
      inc(DFirst);
      AddToCumulative;
    end;

  finally
    Sorter.Free;
  end;
end;

procedure sdWalkingMedianArrayDouble(SFirst, DFirst: PDouble; ACount, ACenter, AWindowSize: integer);
var
  W, Ws: array of double;
  WsCenter, WEnd: PDouble;
  i, WSize, WM1Size: integer;
  Sorter: TCustomSorter;
  // local, add SFirst^ value to the unsorted array
  procedure AddToCumulative;
  begin
    Move(W[1], W[0], WM1Size);
    WEnd^ := SFirst^;
  end;
  // local, this sorts the values, and gets the median
  function GetCumulative: double;
  begin
    Move(W[0], Ws[0], WSize);
    Sorter.Sort;
    Result := WsCenter^;
  end;
// main
begin
  // Only process if we have enough values
  if (ACount < AWindowSize) or (AWindowSize < ACenter) or (AWindowSize <= 0) then
    exit;

  // Initialization
  SetLength(W, AWindowSize);
  SetLength(Ws, AWindowSize);
  WsCenter := @Ws[ACenter];
  WEnd := @W[AWindowSize - 1];
  WSize := AWindowSize * SizeOf(double);
  WM1Size := (AWindowSize - 1) * SizeOf(double);

  Sorter := TCustomSorter.Create;
  try
    Sorter.CompareMethod := ComparePDouble;
    Sorter.First := @Ws[0];
    Sorter.Stride := SizeOf(double);
    Sorter.Count := AWindowSize;

    // Initialize cumulative
    for i := 0 to ACenter - 1 do
      AddToCumulative;

    // Collect values into window
    for i := 0 to (AWindowSize - ACenter) - 1 do
    begin
      AddToCumulative;
      inc(SFirst);
    end;

    // Do first part
    for i := 0 to ACenter - 1 do
    begin
      DFirst^ := GetCumulative;
      inc(DFirst);
      AddToCumulative;
      inc(SFirst);
    end;

    // Bulk
    for i := 0 to ACount - AWindowSize - 1 do
    begin
      DFirst^ := GetCumulative;
      inc(DFirst);
      AddToCumulative;
      inc(SFirst);
    end;

    // make sure we're at the last element, not one beyond
    dec(SFirst);

    // Close area
    for i := ACenter to AWindowSize - 1 do
    begin
      DFirst^ := GetCumulative;
      inc(DFirst);
      AddToCumulative;
    end;

  finally
    Sorter.Free;
  end;
end;

end.

