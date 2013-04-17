{ unit NativeXmlObjectStorage

  This unit provides functionality to store any TObject descendant to an XML file
  or stream. Internally it makes full use of RTTI (RunTime Type Information) in
  order to store all published properties and events.

  It can even be used to copy forms, but form inheritance is not exploited, so
  child forms descending from parent forms store everything that the parent already
  stored.

  All published properties and events of objects are stored. This includes
  the "DefineProperties". These are stored in binary form in the XML, encoded
  as BASE64.

  Known limitations:
  - The method and event lookup will not work correctly across forms.

  Please see the "ObjectToXML" demo for example usage of this unit.

  New version made compatible with FPC by using conditional defines,
  so the unit NativeXmlStorageFPC.pas is no longer required.

  Original Author: Nils Haeck M.Sc.
  Copyright (c) 2003-2011 Simdesign B.V.

  Contributor(s):
  Adam Siwon:
  - fixes for stored properties
  - TCollection items
  Tim Sinaeve
  - FPC version of RTTI
  Rob Kits (RSK)
  - fixed attribute handling in some places (denoted by RSK)

  It is NOT allowed under ANY circumstances to publish or copy this code
  without accepting the license conditions in accompanying LICENSE.txt
  first!

  This software is distributed on an "AS IS" basis, WITHOUT WARRANTY OF
  ANY KIND, either express or implied.

  Please visit http://www.simdesign.nl/xml.html for more information.
}
unit NativeXmlObjectStorage;

{$i simdesign.inc}

// undefine 'useForms' to avoid including the forms and controls units (visual lib).
// This will reduce the app by several megabytes.
{$define useForms}

interface


uses
  Classes, SysUtils,
{$ifdef useForms}
  Forms, Controls,
{$endif}
  TypInfo, Variants, NativeXml, sdDebug;

type

  // Use TsdXmlObjectWriter to write any TPersistent descendant's published properties
  // to an XML node.
  TsdXmlObjectWriter = class(TDebugPersistent)
  protected
    procedure WriteProperty(ANode: TXmlNode; AObject: TObject; AParent: TComponent; PropInfo: PPropInfo);
  public
    // Call WriteObject to write the published properties of AObject to the TXmlNode
    // ANode. Specify AParent in order to store references to parent methods and
    // events correctly.
    procedure WriteObject(ANode: TXmlNode; AObject: TObject; AParent: TComponent = nil);
    // Call WriteComponent to write the published properties of AComponent to the TXmlNode
    // ANode. Specify AParent in order to store references to parent methods and
    // events correctly.
    procedure WriteComponent(ANode: TXmlNode; AComponent: TComponent; AParent: TComponent = nil);
  end;

  // Use TsdXmlObjectReader to read any TPersistent descendant's published properties
  // from an XML node.
  TsdXmlObjectReader = class(TDebugPersistent)
  private
    FSetDefaultValues: Boolean;
  protected
    function ReadProperty(ANode: TXmlNode; AObject: TObject; AParent: TComponent; PropInfo: PPropInfo): Boolean;
  public
    // Call CreateComponent to first create AComponent and then read its published
    // properties from the TXmlNode ANode. Specify AParent in order to resolve
    // references to parent methods and events correctly. In order to successfully
    // create the component from scratch, the component's class must be registered
    // beforehand with a call to RegisterClass. Specify Owner to add the component
    // as a child to Owner's component list. This is usually a form. Specify Name
    // as the new component name for the created component.
    function CreateComponent(ANode: TXmlNode; AOwner, AParent: TComponent; AName: string = ''): TComponent;
    // Call ReadObject to read the published properties of AObject from the TXmlNode
    // ANode. Specify AParent in order to resolve references to parent methods and
    // events correctly.
    function ReadObject(ANode: TXmlNode; AObject: TObject; AParent: TComponent = nil): Boolean;
    // Call ReadComponent to read the published properties of AComponent from the TXmlNode
    // ANode. Specify AParent in order to resolve references to parent methods and
    // events correctly.
    procedure ReadComponent(ANode: TXmlNode; AComponent: TComponent; AParent: TComponent);
    // The flag that determines whether the property that are not saved in the
    // XML file.
    property SetDefaultValues: Boolean read FSetDefaultValues write FSetDefaultValues;
  end;

// High-level create methods

// Create and read a component from the XML file with FileName. In order to successfully
// create the component from scratch, the component's class must be registered
// beforehand with a call to RegisterClass. Specify Owner to add the component
// as a child to Owner's component list. This is usually a form. Specify Name
// as the new component name for the created component.
function ComponentCreateFromXmlFile(const FileName: string; Owner: TComponent; const Name: string): TComponent;

// Create and read a component from the TXmlNode ANode. In order to successfully
// create the component from scratch, the component's class must be registered
// beforehand with a call to RegisterClass. Specify Owner to add the component
// as a child to Owner's component list. This is usually a form. Specify Name
// as the new component name for the created component.
function ComponentCreateFromXmlNode(ANode: TXmlNode; Owner: TComponent; const Name: string): TComponent;

// Create and read a component from the XML stream S. In order to successfully
// create the component from scratch, the component's class must be registered
// beforehand with a call to RegisterClass. Specify Owner to add the component
// as a child to Owner's component list. This is usually a form. Specify Name
// as the new component name for the created component.
function ComponentCreateFromXmlStream(S: TStream; Owner: TComponent; const Name: string): TComponent;

// Create and read a component from the XML in string in Value. In order to successfully
// create the component from scratch, the component's class must be registered
// beforehand with a call to RegisterClass. Specify Owner to add the component
// as a child to Owner's component list. This is usually a form. Specify Name
// as the new component name for the created component.
function ComponentCreateFromXmlString(const Value: string; Owner: TComponent; const Name: string): TComponent;

{$ifdef useForms}
// Create and read a form from the XML file with FileName. In order to successfully
// create the form from scratch, the form's class must be registered
// beforehand with a call to RegisterClass. Specify Owner to add the form
// as a child to Owner's component list. For forms this is usually Application.
// Specify Name as the new form name for the created form.
function FormCreateFromXmlFile(const FileName: string; Owner: TComponent; const Name: string): TForm;

// Create and read a form from the XML stream in S. In order to successfully
// create the form from scratch, the form's class must be registered
// beforehand with a call to RegisterClass. Specify Owner to add the form
// as a child to Owner's component list. For forms this is usually Application.
// Specify Name as the new form name for the created form.
function FormCreateFromXmlStream(S: TStream; Owner: TComponent; const Name: string): TForm;

// Create and read a form from the XML string in Value. In order to successfully
// create the form from scratch, the form's class must be registered
// beforehand with a call to RegisterClass. Specify Owner to add the form
// as a child to Owner's component list. For forms this is usually Application.
// Specify Name as the new form name for the created form.
function FormCreateFromXmlString(const Value: string; Owner: TComponent; const Name: string): TForm;
{$endif}

// High-level load methods

// Load all the published properties of AObject from the XML file in Filename.
// Specify AParent in order to resolve references to parent methods and
// events correctly.
procedure ObjectLoadFromXmlFile(AObject: TObject; const FileName: string; AParent: TComponent = nil);

// Load all the published properties of AObject from the TXmlNode ANode.
// Specify AParent in order to resolve references to parent methods and
// events correctly.
procedure ObjectLoadFromXmlNode(AObject: TObject; ANode: TXmlNode; AParent: TComponent = nil);

// Load all the published properties of AObject from the XML stream in S.
// Specify AParent in order to resolve references to parent methods and
// events correctly.
procedure ObjectLoadFromXmlStream(AObject: TObject; S: TStream; AParent: TComponent = nil);

// Load all the published properties of AObject from the XML string in Value.
// Specify AParent in order to resolve references to parent methods and
// events correctly.
procedure ObjectLoadFromXmlString(AObject: TObject; const Value: string; AParent: TComponent = nil);

// High-level save methods

// Save all the published properties of AObject as XML to the file in Filename.
// Specify AParent in order to store references to parent methods and
// events correctly.
procedure ObjectSaveToXmlFile(AObject: TObject; const FileName: string; AParent: TComponent = nil);

// Save all the published properties of AObject to the TXmlNode ANode.
// Specify AParent in order to store references to parent methods and
// events correctly.
procedure ObjectSaveToXmlNode(AObject: TObject; ANode: TXmlNode; AParent: TComponent = nil);

// Save all the published properties of AObject as XML in stream S.
// Specify AParent in order to store references to parent methods and
// events correctly.
procedure ObjectSaveToXmlStream(AObject: TObject; S: TStream; AParent: TComponent = nil);

// Save all the published properties of AObject as XML in string Value.
// Specify AParent in order to store references to parent methods and
// events correctly.
function ObjectSaveToXmlString(AObject: TObject; AParent: TComponent = nil): string;

// Save all the published properties of AComponent as XML in the file in Filename.
// Specify AParent in order to store references to parent methods and
// events correctly.
procedure ComponentSaveToXmlFile(AComponent: TComponent; const FileName: string; AParent: TComponent = nil);

// Save all the published properties of AComponent to the TXmlNode ANode.
// Specify AParent in order to store references to parent methods and
// events correctly.
procedure ComponentSaveToXmlNode(AComponent: TComponent; ANode: TXmlNode; AParent: TComponent = nil);

// Save all the published properties of AComponent as XML in the stream in S.
// Specify AParent in order to store references to parent methods and
// events correctly.
procedure ComponentSaveToXmlStream(AComponent: TComponent; S: TStream; AParent: TComponent = nil);

// Save all the published properties of AComponent as XML in the string Value.
// Specify AParent in order to store references to parent methods and
// events correctly.
function ComponentSaveToXmlString(AComponent: TComponent; AParent: TComponent = nil): string;

{$ifdef useForms}
// Save the form AForm as XML to the file in Filename. This method also stores
// properties of all child components on the form, and can therefore be used
// as a form-storage method.
procedure FormSaveToXmlFile(AForm: TForm; const FileName: string);

// Save the form AForm as XML to the stream in S. This method also stores
// properties of all child components on the form, and can therefore be used
// as a form-storage method.
procedure FormSaveToXmlStream(AForm: TForm; S: TStream);

// Save the form AForm as XML to a string. This method also stores
// properties of all child components on the form, and can therefore be used
// as a form-storage method.
function FormSaveToXmlString(AForm: TForm): string;
{$endif}

resourcestring

  sIllegalVarType        = 'illegal variant type';
  sUnregisteredClassType = 'unregistered classtype encountered in ';
  sInvalidPropertyValue  = 'invalid property value';
  sInvalidMethodName     = 'invalid method name';

implementation

type

  TPersistentAccess = class(TPersistent);
  TComponentAccess = class(TComponent)
  public
    procedure SetComponentState(const AState: TComponentState);
  published
    property ComponentState;
  end;

  TReaderAccess = class(TReader);

function ComponentCreateFromXmlFile(const FileName: string; Owner: TComponent; const Name: string): TComponent;
var
  S: TStream;
begin
  S := TFileStream.Create(FileName, fmOpenRead or fmShareDenyNone);
  try
    Result := ComponentCreateFromXmlStream(S, Owner, Name);
  finally
    S.Free;
  end;
end;

function ComponentCreateFromXmlNode(ANode: TXmlNode; Owner: TComponent; const Name: string): TComponent;
var
  AReader: TsdXmlObjectReader;
begin
  Result := nil;
  if not assigned(ANode) then
    exit;
  // Create reader
  AReader := TsdXmlObjectReader.Create;
  try
    // Read the component from the node
    Result := AReader.CreateComponent(ANode, Owner, nil, Name);
  finally
    AReader.Free;
  end;
end;

function ComponentCreateFromXmlStream(S: TStream; Owner: TComponent; const Name: string): TComponent;
var
  ADoc: TNativeXml;
begin
  Result := nil;
  if not assigned(S) then
    exit;
  // Create XML document
  ADoc := TNativeXml.Create(nil);
  try
    // Load XML
    ADoc.LoadFromStream(S);
    // Load from XML node
    Result := ComponentCreateFromXmlNode(ADoc.Root, Owner, Name);
  finally
    ADoc.Free;
  end;
end;

function ComponentCreateFromXmlString(const Value: string; Owner: TComponent; const Name: string): TComponent;
var
  S: TStream;
begin
  S := TStringStream.Create(Value);
  try
    Result := ComponentCreateFromXmlStream(S, Owner, Name);
  finally
    S.Free;
  end;
end;

{$ifdef useForms}
function FormCreateFromXmlFile(const FileName: string; Owner: TComponent; const Name: string): TForm;
var
  S: TStream;
begin
  S := TFileStream.Create(FileName, fmOpenRead or fmShareDenyNone);
  try
    Result := FormCreateFromXmlStream(S, Owner, Name);
  finally
    S.Free;
  end;
end;

function FormCreateFromXmlStream(S: TStream; Owner: TComponent; const Name: string): TForm;
var
  ADoc: TNativeXml;
begin
  Result := nil;
  if not assigned(S) then
    exit;
  // Create XML document
  ADoc := TNativeXml.Create(nil);
  try
    // Load XML
    ADoc.LoadFromStream(S);

    // Load from XML node
    Result := TForm(ComponentCreateFromXmlNode(ADoc.Root, Owner, Name));
  finally
    ADoc.Free;
  end;
end;

function FormCreateFromXmlString(const Value: string; Owner: TComponent; const Name: string): TForm;
var
  S: TStream;
begin
  S := TStringStream.Create(Value);
  try
    Result := FormCreateFromXmlStream(S, Owner, Name);
  finally
    S.Free;
  end;
end;
{$endif}

procedure ObjectLoadFromXmlFile(AObject: TObject; const FileName: string; AParent: TComponent = nil);
var
  S: TStream;
begin
  S := TFileStream.Create(FileName, fmOpenRead or fmShareDenyNone);
  try
    ObjectLoadFromXmlStream(AObject, S, AParent);
  finally
    S.Free;
  end;
end;

procedure ObjectLoadFromXmlNode(AObject: TObject; ANode: TXmlNode; AParent: TComponent = nil);
var
  AReader: TsdXmlObjectReader;
begin
  if not assigned(AObject) or not assigned(ANode) then
    exit;
  // Create writer
  AReader := TsdXmlObjectReader.Create;
  try
    // Write the object to the document
    if AObject is TComponent then
      AReader.ReadComponent(ANode, TComponent(AObject), AParent)
    else
      AReader.ReadObject(ANode, AObject, AParent);
  finally
    AReader.Free;
  end;
end;

procedure ObjectLoadFromXmlStream(AObject: TObject; S: TStream; AParent: TComponent = nil);
var
  ADoc: TNativeXml;
begin
  if not assigned(S) then
    exit;
  // Create XML document
  ADoc := TNativeXml.Create(nil);
  try
    // Load XML
    ADoc.LoadFromStream(S);
    // Load from XML node
    ObjectLoadFromXmlNode(AObject, ADoc.Root, AParent);
  finally
    ADoc.Free;
  end;
end;

procedure ObjectLoadFromXmlString(AObject: TObject; const Value: string; AParent: TComponent = nil);
var
  S: TStringStream;
begin
  S := TStringStream.Create(Value);
  try
    ObjectLoadFromXmlStream(AObject, S, AParent);
  finally
    S.Free;
  end;
end;

procedure ObjectSaveToXmlFile(AObject: TObject; const FileName: string; AParent: TComponent = nil);
var
  S: TStream;
begin
  S := TFileStream.Create(FileName, fmCreate);
  try
    ObjectSaveToXmlStream(AObject, S, AParent);
  finally
    S.Free;
  end;
end;

procedure ObjectSaveToXmlNode(AObject: TObject; ANode: TXmlNode; AParent: TComponent = nil);
var
  AWriter: TsdXmlObjectWriter;
begin
  if not assigned(AObject) or not assigned(ANode) then
    exit;
  // Create writer
  AWriter := TsdXmlObjectWriter.Create;
  try
    // Write the object to the document
    if AObject is TComponent then
      AWriter.WriteComponent(ANode, TComponent(AObject), AParent)
    else
    begin
      ANode.Name := UTF8String(AObject.ClassName);
      AWriter.WriteObject(ANode, AObject, AParent);
    end;
  finally
    AWriter.Free;
  end;
end;

procedure ObjectSaveToXmlStream(AObject: TObject; S: TStream; AParent: TComponent = nil);
var
  ADoc: TNativeXml;
begin
  if not assigned(S) then
    exit;
  // Create XML document
  ADoc := TNativeXml.Create(nil);
  try
    ADoc.XmlFormat := xfReadable;
    // Save to XML node
    ObjectSaveToXmlNode(AObject, ADoc.Root, AParent);
    // Save to stream
    ADoc.SaveToStream(S);
  finally
    ADoc.Free;
  end;
end;

function ObjectSaveToXmlString(AObject: TObject; AParent: TComponent = nil): string;
var
  S: TStringStream;
begin
  S := TStringStream.Create('');
  try
    ObjectSaveToXmlStream(AObject, S, AParent);
    Result := S.DataString;
  finally
    S.Free;
  end;
end;

procedure ComponentSaveToXmlFile(AComponent: TComponent; const FileName: string; AParent: TComponent = nil);
begin
  ObjectSaveToXmlFile(AComponent, FileName, AParent);
end;

procedure ComponentSaveToXmlNode(AComponent: TComponent; ANode: TXmlNode; AParent: TComponent = nil);
begin
  ObjectSaveToXmlNode(AComponent, ANode, AParent);
end;

procedure ComponentSaveToXmlStream(AComponent: TComponent; S: TStream; AParent: TComponent = nil);
begin
  ObjectSaveToXmlStream(AComponent, S, AParent);
end;

function ComponentSaveToXmlString(AComponent: TComponent; AParent: TComponent = nil): string;
begin
  Result := ObjectSaveToXmlString(AComponent, AParent);
end;

{$ifdef useForms}
procedure FormSaveToXmlFile(AForm: TForm; const FileName: string);
begin
  ComponentSaveToXmlFile(AForm, FileName, AForm);
end;

procedure FormSaveToXmlStream(AForm: TForm; S: TStream);
begin
  ComponentSaveToXmlStream(AForm, S, AForm);
end;

function FormSaveToXmlString(AForm: TForm): string;
begin
  Result := ComponentSaveToXmlString(AForm, AForm);
end;
{$endif}

{ TsdXmlObjectWriter }

procedure TsdXmlObjectWriter.WriteComponent(ANode: TXmlNode; AComponent, AParent: TComponent);
begin
  if not assigned(ANode) or not assigned(AComponent) then
    exit;
  ANode.Name := UTF8String(AComponent.ClassName);
  if length(AComponent.Name) > 0 then
    ANode.AttributeAdd('name', UTF8String(AComponent.Name));
  WriteObject(ANode, AComponent, AParent);
end;

procedure TsdXmlObjectWriter.WriteObject(ANode: TXmlNode; AObject: TObject; AParent: TComponent);
var
  i, Count: Integer;
  PropInfo: PPropInfo;
  PropList: PPropList;
  S: TStringStream;
  AWriter: TWriter;
  AChildNode: TXmlNode;
  AComponentNode: TXmlNode;
  C: TComponent;
begin
  if not assigned(ANode) or not assigned(AObject) then
    exit;

  // If this is a component, store child components
  if AObject is TComponent then
  begin
    C := TComponent(AObject);
    if C.ComponentCount > 0 then
    begin
      AChildNode := ANode.NodeNew('Components');
      for i := 0 to C.ComponentCount - 1 do
      begin
        AComponentNode := AChildNode.NodeNew(UTF8String(C.Components[i].ClassName));
        if length(C.Components[i].Name) > 0 then
          AComponentNode.AttributeAdd('name', UTF8String(C.Components[i].Name));
        WriteObject(AComponentNode, C.Components[i], TComponent(AObject));
      end;
    end;
  end;

  // If this is a collection, store collections items
  if AObject is TCollection then
    for i := 0 to TCollection(AObject).Count - 1 do
    begin
      AChildNode := ANode.NodeNew(UTF8String(TCollection(AObject).Items[i].ClassName));
      WriteObject(AChildNode, TCollection(AObject).Items[i], AParent);
    end;

  // Save all regular properties that need storing
  Count := GetTypeData(AObject.ClassInfo)^.PropCount;
  if Count > 0 then
  begin
    GetMem(PropList, Count * SizeOf(Pointer));
    try
      GetPropInfos(AObject.ClassInfo, PropList);
      for i := 0 to Count - 1 do
      begin
        PropInfo := PropList^[i];
        if PropInfo = nil then
          continue;
        if IsStoredProp(AObject, PropInfo) then
          WriteProperty(ANode, AObject, AParent, PropInfo);
      end;
    finally
      FreeMem(PropList, Count * SizeOf(Pointer));
    end;
  end;

  // Save defined properties
  if AObject is TPersistent then
  begin
    S := TStringStream.Create('');
    try
      AWriter := TWriter.Create(S, 4096);
      try
        TPersistentAccess(AObject).DefineProperties(AWriter);
      finally
        AWriter.Free;
      end;
      // Do we have data from DefineProperties?
      if S.Size > 0 then
      begin
        // Yes, add a node with binary data
        ANode.NodeNew('DefinedProperties').BinaryString := RawByteString(S.DataString);
      end;
    finally
      S.Free;
    end;
  end;
end;

procedure TsdXmlObjectWriter.WriteProperty(ANode: TXmlNode; AObject: TObject; AParent: TComponent; PropInfo: PPropInfo);
var
  PropType: PTypeInfo;
  AChildNode: TXmlNode;
  ACollectionNode: TXmlNode;

  //local
  procedure WritePropName;
  begin
    AChildNode := ANode.NodeNew(PPropInfo(PropInfo)^.Name);
  end;

  //local
  procedure WriteInteger(Value: Int64);
  begin
    AChildNode.Value := UTF8String(IntToStr(Value));
  end;

  //local
  procedure WriteString(Value: string);
  begin
    AChildNode.ValueUnicode := Value;
  end;

  //local
  procedure WriteSet(Value: Longint);
  var
    i: Integer;
    BaseType: PTypeInfo;
    S, Enum: string;
  begin
    {$ifdef FPC}
    BaseType := GetTypeData(PropType)^.CompType;
    {$else}
    BaseType := GetTypeData(PropType)^.CompType^;
    {$endif}
    for i := 0 to SizeOf(TIntegerSet) * 8 - 1 do
    begin
      if i in TIntegerSet(Value) then
      begin
        Enum := GetEnumName(BaseType, i);
        if i > 0 then
          S := S + ',' + Enum
        else
          S := Enum;
      end;
    end;
    AChildNode.Value := UTF8String(Format('[%s]', [S]));
  end;

  //local
  procedure WriteIntProp(IntType: PTypeInfo; Value: Longint);
  var
    Ident: string;
    IntToIdent: TIntToIdent;
  begin
    IntToIdent := FindIntToIdent(IntType);
    if Assigned(IntToIdent) and IntToIdent(Value, Ident) then
      WriteString(Ident)
    else
      WriteInteger(Value);
  end;

  //local
  procedure WriteCollectionProp(Collection: TCollection);
  var
    i: integer;
  begin
    if assigned(Collection) then
    begin
      for i := 0 to Collection.Count - 1 do
      begin
        ACollectionNode := AChildNode.NodeNew(UTF8String(Collection.Items[i].ClassName));
        WriteObject(ACollectionNode, Collection.Items[I], AParent);
      end;
    end;
  end;

  //local
  procedure WriteOrdProp;
  var
    Value: Longint;
  begin
    Value := GetOrdProp(AObject, PropInfo);
    if not (Value = PPropInfo(PropInfo)^.Default) then
    begin
      WritePropName;
      case PropType^.Kind of
      {$ifdef FPC}
      tkInteger:     WriteIntProp(PPropInfo(PropInfo)^.PropType, Value);
      {$else}
      tkInteger:     WriteIntProp(PPropInfo(PropInfo)^.PropType^, Value);
      {$endif}
      tkChar:        WriteString(Chr(Value));
      tkSet:         WriteSet(Value);
      tkEnumeration: WriteString(GetEnumName(PropType, Value));
      end;
    end;
  end;

  //local
  procedure WriteFloatProp;
  var
    Value: Extended;
  begin
    Value := GetFloatProp(AObject, PropInfo);
    if not (Value = 0) then
      ANode.WriteFloat(PPropInfo(PropInfo)^.Name, Value);
  end;

  //local
  procedure WriteInt64Prop;
  var
    Value: Int64;
  begin
    Value := GetInt64Prop(AObject, PropInfo);
    if not (Value = 0) then
      ANode.WriteInt64(PPropInfo(PropInfo)^.Name, Value);
  end;

  //local
  procedure WriteStrProp;
  var
    Value: Utf8String;
  begin
    Value := Utf8String(GetStrProp(AObject, PropInfo));
    if not (length(Value) = 0) then
      ANode.WriteString(PPropInfo(PropInfo)^.Name, Value);
  end;

  //local
  procedure WriteWideStrProp;
  var
    Value: Utf8String;
  begin
    Value := Utf8String(GetWideStrProp(AObject, PropInfo));
    if not (length(Value) = 0) then
      ANode.WriteString(PPropInfo(PropInfo)^.Name, Value);
  end;

  {$ifdef UNICODE}
  //local
  procedure WriteUnicodeStrProp;
  var
    Value: UnicodeString;
  begin
    Value := GetUnicodeStrProp(AObject, PropInfo);
    if not (length(Value) = 0) then
      ANode.WriteString(PPropInfo(PropInfo)^.Name, Value);
  end;
  {$endif UNICODE}

  //local
  procedure WriteObjectProp;
  var
    Value: TObject;
    ComponentName: string;
    //local-local
    function GetComponentName(Component: TComponent): string;
    begin
      if Component.Owner = AParent then
        Result := Component.Name
      else if Component = AParent then
        Result := 'owner'
      else if assigned(Component.Owner) and (length(Component.Owner.Name) > 0) and (length(Component.Name) > 0) then
        Result := Component.Owner.Name + '.' + Component.Name
      else if length(Component.Name) > 0 then
        Result := Component.Name + '.owner'
      else
        Result := '';
    end;
  begin
    Value := TObject(GetOrdProp(AObject, PropInfo));
    if not assigned(Value) then
      exit;
    WritePropName;
    if (Value is TComponent) and not (csSubComponent in TComponent(Value).ComponentStyle) then
    begin
      ComponentName := GetComponentName(TComponent(Value));
      if length(ComponentName) > 0 then
        WriteString(ComponentName);
    end
    else
    begin
      WriteString(Format('(%s)', [Value.ClassName]));
      if Value is TCollection then
        WriteCollectionProp(TCollection(Value))
      else
      begin
        if AObject is TComponent then
          WriteObject(AChildNode, Value, TComponent(AObject))
        else
          WriteObject(AChildNode, Value, AParent)
      end;
      // No need to store an empty child.. so check and remove
      if AChildNode.NodeCount = 0 then
        ANode.NodeRemove(AChildNode);
    end;
  end;

  //local
  procedure WriteMethodProp;
  var
    Value: TMethod;
    function IsDefaultValue: Boolean;
    begin
      Result := (Value.Code = nil) or ((Value.Code <> nil) and assigned(AParent) and (AParent.MethodName(Value.Code) = ''));
    end;
  begin
    Value := GetMethodProp(AObject, PropInfo);
    if not IsDefaultValue then
    begin
      if assigned(Value.Code) then
      begin
        WritePropName;
        if assigned(AParent) then
          WriteString(AParent.MethodName(Value.Code))
        else
          AChildNode.Value := '???';
      end;
    end;
  end;

  //local
  function WriteVariantProp: boolean;
  var
    AValue: Variant;
    ACurrency: Currency;
  var
    VType: Integer;
  begin
    Result := True;
    AValue := GetVariantProp(AObject, PropInfo);
    if not VarIsEmpty(AValue) or VarIsNull(AValue) then
    begin
      if VarIsArray(AValue) then
      begin
        DoDebugOut(Self, wsWarn, sIllegalVarType);
        Result := False;
        Exit;
      end;
      WritePropName;
      VType := VarType(AValue);
      AChildNode.AttributeAdd('vartype', UTF8String(IntToHex(VType, 4)));
      case VType and varTypeMask of
      varNull:    AChildNode.Value := '';
      varOleStr:  AChildNode.Value := Utf8String(AValue);
      varString:  AChildNode.Value := Utf8String(AValue);
      varByte,
      varSmallInt,
      varInteger: AChildNode.SetValueAsInteger(AValue);
      varSingle,
      varDouble:  AChildNode.SetValueAsFloat(AValue);
      varCurrency:
        begin
          ACurrency := AValue;
          AChildNode.BufferWrite(ACurrency, SizeOf(ACurrency));
        end;
      varDate:    AChildNode.SetValueAsDateTime(AValue);
      varBoolean: AChildNode.SetValueAsBool(AValue);
      else
        try
          ANode.Value := AValue;
        except
          DoDebugOut(Self, wsWarn, sIllegalVarType);
          Result := False;
          Exit;
        end;
      end;//case
    end;
  end;

//main
begin
  if (PPropInfo(PropInfo)^.SetProc <> nil) and (PPropInfo(PropInfo)^.GetProc <> nil) then
  begin
    {$ifdef FPC}
    PropType := PPropInfo(PropInfo)^.PropType;
    {$else}
    PropType := PPropInfo(PropInfo)^.PropType^;
    {$endif}
    case PropType^.Kind of
    tkInteger, tkChar, tkEnumeration, tkSet: WriteOrdProp;
    tkFloat:                                 WriteFloatProp;
    {$ifdef FPC}
    tkAString, tkString, tkLString:          WriteStrProp;
    {$else}
    tkString, tkLString:                     WriteStrProp;
    {$endif}
    {$ifdef D7UP}
    tkWString:                               WriteWideStrProp;
    {$endif}
    {$ifdef UNICODE}
    tkUString:                               WriteUnicodeStrProp;
    {$endif UNICODE}
    tkClass:                                 WriteObjectProp;
    tkMethod:                                WriteMethodProp;
    tkVariant:                               WriteVariantProp;
    tkInt64:                                 WriteInt64Prop;
    end;
  end;
end;

{ TsdXmlObjectReader }

function TsdXmlObjectReader.CreateComponent(ANode: TXmlNode; AOwner, AParent: TComponent; AName: string): TComponent;
var
  AClass: TComponentClass;
begin
  AClass := TComponentClass(GetClass(string(ANode.Name)));
  if not assigned(AClass) then
  begin
    DoDebugOut(Self, wsFail, Format(sUnregisteredClassType, [ANode.Name]));
    Result := nil;
    Exit;
  end;
  Result := AClass.Create(AOwner);
  if length(AName) = 0 then
    Result.Name := ANode.AttributeValueByName['name'] // RSK
  else
    Result.Name := AName;
  if not assigned(AParent) then
    AParent := Result;
  ReadComponent(ANode, Result, AParent);
end;

procedure TsdXmlObjectReader.ReadComponent(ANode: TXmlNode; AComponent, AParent: TComponent);
begin
  ReadObject(ANode, AComponent, AParent);
end;

function TsdXmlObjectReader.ReadObject(ANode: TXmlNode; AObject: TObject; AParent: TComponent): Boolean;
var
  i, Count: Integer;
  Item: TCollectionItem;
  PropInfo: PPropInfo;
  PropList: PPropList;
  S: TStringStream;
  AReader: TReader;
  AChildNode: TXmlNode;
  AComponentNode: TXmlNode;
  AClass: TComponentClass;
  AComponent: TComponent;
  C: TComponent;
  CA: TComponentAccess;
  Coll: TCollection;
begin
  Result := True;
  if not assigned(ANode) or not assigned(AObject) then
    exit;

  // Start loading
  if AObject is TComponent then
  begin
    CA := TComponentAccess(AObject);
    CA.Updating;
    CA.SetComponentState(CA.ComponentState + [csLoading, csReading]);
  end;
  try

    // If this is a component, load child components
    if AObject is TComponent then
    begin
      C := TComponent(AObject);
      AChildNode := ANode.NodeByName('Components');
      if assigned(AChildNode) then
      begin
        for i := 0 to AChildNode.ContainerCount - 1 do // RSK
        begin
          AComponentNode := AChildNode.Containers[i]; // RSK
          AComponent := C.FindComponent(AComponentNode.AttributeValueByName['name']); // RSK
          if not assigned(AComponent) then
          begin
            AClass := TComponentClass(GetClass(string(AComponentNode.Name)));
            if not assigned(AClass) then
            begin
              DoDebugOut(Self, wsFail, sUnregisteredClassType);
              Result := False;
              Exit;
            end;
            AComponent := AClass.Create(TComponent(AObject));
            AComponent.Name := AComponentNode.AttributeValueByName['name']; // RSK
{$ifdef useForms}
            // In case of new (visual) controls we set the parent
            if (AComponent is TControl) and (AObject is TWinControl) then
              TControl(AComponent).Parent := TWinControl(AObject);
{$endif}              
          end;
          ReadComponent(AComponentNode, AComponent, TComponent(AObject));
        end;
      end;
    end;

    // If this is a collection, load collections items
    if AObject is TCollection then
    begin
      Coll := TCollection(AObject);
      Coll.BeginUpdate;
      try
        Coll.Clear;
        for i := 0 to ANode.ContainerCount - 1 do // RSK
        begin
          Item := Coll.Add;
          ReadObject(ANode.Containers[i], Item, AParent); // RSK
        end;
      finally
        Coll.EndUpdate;
      end;
    end;

    // Load all loadable regular properties
    Count := GetTypeData(AObject.ClassInfo)^.PropCount;
    if Count > 0 then
    begin
      GetMem(PropList, Count * SizeOf(Pointer));
      try
        GetPropInfos(AObject.ClassInfo, PropList);
        for i := 0 to Count - 1 do
        begin
          PropInfo := PropList^[i];
          if PropInfo = nil then
            continue;
          ReadProperty(ANode, AObject, AParent, PropInfo);
        end;
      finally
        FreeMem(PropList, Count * SizeOf(Pointer));
      end;
    end;

    // Load defined properties
    if AObject is TPersistent then
    begin
      AChildNode := ANode.NodeByName('DefinedProperties');
      if assigned(AChildNode) then
      begin
        S := TStringStream.Create(AChildNode.BinaryString);
        try
          AReader := TReader.Create(S, 4096);
          try
            {$ifdef FPC}
            while not AReader.EndOfList do
            {$else}
            while AReader.Position < S.Size do
            {$endif}
              TReaderAccess(AReader).ReadProperty(TPersistent(AObject));
          finally
            AReader.Free;
          end;
        finally
          S.Free;
        end;
      end;
    end;

  finally
    // End loading
    if AObject is TComponent then
    begin
      CA := TComponentAccess(AObject);
      CA.SetComponentState(CA.ComponentState - [csReading]);
      CA.Loaded;
      CA.Updated;
    end;
  end;
end;

function TsdXmlObjectReader.ReadProperty(ANode: TXmlNode; AObject: TObject; AParent: TComponent; PropInfo: PPropInfo): boolean;
var
  PropType: PTypeInfo;
  AChildNode: TXmlNode;
  Method: TMethod;
  PropObject: TObject;
  //local
  function SetSetProp(const AValue: string): boolean;
  var
    S: string;
    P: integer;
    ASet: integer;
    EnumType: PTypeInfo;
    // local local
    function AddToEnum(const EnumName: string): boolean;
    var
      V: integer;
    begin
      Result := True;
      if length(EnumName) = 0 then
        exit;
      V := GetEnumValue(EnumType, EnumName);
      if V = -1 then
      begin
        DoDebugOut(Self, wsFail, sInvalidPropertyValue);
        Result := False;
        Exit;
      end;
      Include(TIntegerSet(ASet), V);
    end;
  begin
    Result := True;
    ASet := 0;
    {$ifdef FPC}
    EnumType := GetTypeData(PropType)^.CompType;
    {$else}
    EnumType := GetTypeData(PropType)^.CompType^;
    {$endif}
    S := copy(AValue, 2, length(AValue) - 2);
    repeat
      P := Pos(',', S);
      if P > 0 then
      begin
        AddToEnum(copy(S, 1, P - 1));
        S := copy(S, P + 1, length(S));
      end
      else
      begin
        Result := AddToEnum(S);
        break;
      end;
    until False;
    SetOrdProp(AObject, PropInfo, ASet);
  end;

  procedure SetIntProp(const AValue: string);
  var
    V: Longint;
    IdentToInt: TIdentToInt;
  begin
    IdentToInt := FindIdentToInt(PropType);
    if Assigned(IdentToInt) and IdentToInt(AValue, V) then
      SetOrdProp(AObject, PropInfo, V)
    else
      SetOrdProp(AObject, PropInfo, StrToInt(AValue));
  end;

  function SetCharProp(const AValue: string): boolean;
  begin
    Result := True;
    if length(AValue) <> 1 then
    begin
      DoDebugOut(Self, wsFail, sInvalidPropertyValue);
      Result := False;
      Exit;
    end;
    SetOrdProp(AObject, PropInfo, Ord(AValue[1]));
  end;

  function SetEnumProp(const AValue: string): boolean;
  var
    V: integer;
  begin
    Result := True;
    V := GetEnumValue(PropType, AValue);
    if V = -1 then
    begin
      DoDebugOut(Self, wsFail, sInvalidPropertyValue);
      Result := False;
      Exit;
    end;
    SetOrdProp(AObject, PropInfo, V)
  end;

  procedure ReadCollectionProp(ACollection: TCollection);
  var
    i: integer;
    Item: TPersistent;
  begin
    ACollection.BeginUpdate;
    try
      ACollection.Clear;
      for i := 0 to AChildNode.ContainerCount - 1 do
      begin
        Item := ACollection.Add;
        ReadObject(AChildNode.Containers[i], Item, AParent);
      end;
    finally
      ACollection.EndUpdate;
    end;
  end;

  function SetObjectProp(const AValue: string): boolean;
  var
    AClassName: string;
    PropObject: TObject;
    Reference: TComponent;
  begin
    Result := True;
    if length(AValue) = 0 then
      exit;
    if AValue[1] = '(' then
    begin
      // Persistent class
      AClassName := Copy(AValue, 2, length(AValue) - 2);
      PropObject := TObject(GetOrdProp(AObject, PropInfo));
      if assigned(PropObject) and (PropObject.ClassName = AClassName) then
      begin
        if PropObject is TCollection then
          ReadCollectionProp(TCollection(PropObject))
        else
        begin
          if AObject is TComponent then
            ReadObject(AChildNode, PropObject, TComponent(AObject))
          else
            ReadObject(AChildNode, PropObject, AParent);
        end;
      end
      else
      begin
        DoDebugOut(Self, wsFail, sUnregisteredClassType);
        Result := False;
        Exit;
      end;
    end
    else
    begin
      // Component reference
      if assigned(AParent) then
      begin
        Reference := FindNestedComponent(AParent, AValue);
        SetOrdProp(AObject, PropInfo, Longint(Reference));
      end;
    end;
  end;

  function SetMethodProp(const AValue: string): boolean;
  var
    Method: TMethod;
  begin
    Result := True;
    // to do: add OnFindMethod
    if not assigned(AParent) then
      exit;
    Method.Code := AParent.MethodAddress(AValue);
    if not assigned(Method.Code) then
    begin
      DoDebugOut(Self, wsFail, sInvalidMethodName);
      Result := False;
      Exit;
    end;
    Method.Data := AParent;
    TypInfo.SetMethodProp(AObject, PropInfo, Method);
  end;

  function SetVariantProp(const AValue: string): boolean;
  var
    VType: integer;
    Value: Variant;
    ACurrency: Currency;
  begin
    Result := True;
    VType := StrToInt('$' + AChildNode.AttributeValueByName['vartype']); // RSK

    case VType and varTypeMask of
    varNull:    Value := Null;
    varOleStr:  Value := AChildNode.ValueUnicode;
    varString:  Value := AChildNode.Value;
    varByte,
    varSmallInt,
    varInteger: Value := AChildNode.GetValueAsInteger;
    varSingle,
    varDouble:  Value := AChildNode.GetValueAsFloat;
    varCurrency:
      begin
        AChildNode.BufferRead(ACurrency, SizeOf(ACurrency));
        Value := ACurrency;
      end;
    varDate:    Value := AChildNode.GetValueAsDateTime;
    varBoolean: Value := AChildNode.GetValueAsBool;
    else
      try
        Value := ANode.Value;
      except
        DoDebugOut(Self, wsFail, sIllegalVarType);
        Result := False;
        Exit;
      end;
    end;//case

    TVarData(Value).VType := VType;
    TypInfo.SetVariantProp(AObject, PropInfo, Value);
  end;

begin
  Result := True;
  if (PPropInfo(PropInfo)^.SetProc <> nil) and (PPropInfo(PropInfo)^.GetProc <> nil) then
  begin
    {$ifdef FPC}
    PropType := PPropInfo(PropInfo)^.PropType;
    {$else}
    PropType := PPropInfo(PropInfo)^.PropType^;
    {$endif}
    AChildNode := ANode.NodeByName(PPropInfo(PropInfo)^.Name);
    if assigned(AChildNode) then
    begin
      // Non-default values from XML
      case PropType^.Kind of
      tkInteger:     SetIntProp(AChildNode.Value);
      tkChar:        SetCharProp(AChildNode.Value);
      tkSet:         SetSetProp(AChildNode.Value);
      tkEnumeration: SetEnumProp(AChildNode.Value);
      tkFloat:       SetFloatProp(AObject, PropInfo, AChildNode.GetValueAsFloat);
      {$ifdef FPC}
      tkAString,
      {$endif}
      tkString,
      tkLString:     SetStrProp(AObject, PropInfo, AChildNode.Value);
      {$endif}
      {$ifdef UNICODE}
      tkWString:     SetWideStrProp(AObject, PropInfo, UTF8ToWideString(AChildNode.Value));
      {$else UNICODE}
      tkWString:     SetWideStrProp(AObject, PropInfo, UTF8Decode(AChildNode.Value));
      {$endif UNICODE}
      {$ifdef UNICODE}
      tkUString:     SetUnicodeStrProp(AObject, PropInfo, AChildNode.Value);
      {$endif UNICODE}
      tkClass:       SetObjectProp(AChildNode.Value);
      tkMethod:      SetMethodProp(AChildNode.Value);
      tkVariant:     SetVariantProp(AChildNode.Value);
      tkInt64:       SetInt64Prop(AObject, PropInfo, AChildNode.GetValueAsInt64);
      end;//case
    end else
    begin
      if SetDefaultValues then
      begin
        // Set Default value
        case PropType^.Kind of
        tkInteger:     SetOrdProp(AObject, PropInfo, PPropInfo(PropInfo)^.Default);
        tkChar:        SetOrdProp(AObject, PropInfo, PPropInfo(PropInfo)^.Default);
        tkSet:         SetOrdProp(AObject, PropInfo, PPropInfo(PropInfo)^.Default);
        tkEnumeration: SetOrdProp(AObject, PropInfo, PPropInfo(PropInfo)^.Default);
        tkFloat:       SetFloatProp(AObject, PropInfo, 0);
        {$ifdef FPC}
        tkAString,
        {$endif}
        tkString,
        tkLString,
        tkWString:     SetStrProp(AObject, PropInfo, '');
        {$ifdef UNICODE}
        tkUString:     SetStrProp(AObject, PropInfo, '');
        {$endif UNICODE}
        tkClass:
          begin
            PropObject := TObject(GetOrdProp(AObject, PropInfo));
            if PropObject is TComponent then
              SetOrdProp(AObject, PropInfo, 0);
          end;
        tkMethod:
          begin
            Method := TypInfo.GetMethodProp(AObject, PropInfo);
            Method.Code := nil;
            TypInfo.SetMethodProp(AObject, PropInfo, Method);
          end;
        tkInt64:       SetInt64Prop(AObject, PropInfo, 0);
        end;//case
      end;
    end;
  end;
end;

{ TComponentAccess }

procedure TComponentAccess.SetComponentState(const AState: TComponentState);
type
  PInteger = ^integer;
var
  PSet: PInteger;
  AInfo: PPropInfo;
begin
  // This is a "severe" hack in order to set a non-writable property value,
  // also using RTTI
  PSet := PInteger(@AState);
  AInfo := GetPropInfo(TComponentAccess, 'ComponentState');
  if assigned(AInfo.GetProc) then
    PInteger(Integer(Self) + Integer(AInfo.GetProc) and $00FFFFFF)^ := PSet^;
end;

end.

