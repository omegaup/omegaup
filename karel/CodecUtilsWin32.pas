// CodecUtilsWin32 1.0.0 -- Win32 version
// Delphi 4 to 2009 and Kylix 3 Implementation
// August 2009
//
//
// LICENSE
//
// The contents of this file are subject to the Mozilla Public License Version
// 1.1 (the "License"); you may not use this file except in compliance with
// the License. You may obtain a copy of the License at
// "http://www.mozilla.org/MPL/"
//
// Software distributed under the License is distributed on an "AS IS" basis,
// WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
// the specific language governing rights and limitations under the License.
//
// The Original Code is "CodecUtilsWin32.pas".
//
// The Initial Developers of the Original Code are David J Butler (Pretoria,
// South Africa, "http://fundementals.sourceforge.net/") and Dieter Köhler
// (Heidelberg, Germany, "http://www.philo.de/"). Portions created by the
// Initial Developers are Copyright (C) 2003-2009 David J Butler and Dieter
// Köhler. All Rights Reserved.
//
// Alternatively, the contents of this file may be used under the terms of the
// GNU General Public License Version 2 or later (the "GPL"), in which case the
// provisions of the GPL are applicable instead of those above. If you wish to
// allow use of your version of this file only under the terms of the GPL, and
// not to allow others to use your version of this file under the terms of the
// MPL, indicate your decision by deleting the provisions above and replace them
// with the notice and other provisions required by the GPL. If you do not delete
// the provisions above, a recipient may use your version of this file under the
// terms of any one of the MPL or the GPL.

// HISTORY
// 2009-08-12 1.0.1 Workarounds for problems with non-English locals implemented.
//                  Bugs for IBM870 and IBM918 codec fixed.
//                  Thanks to Yooichi Tagawa for providing the workarounds and
//                  fixes.
// 2009-02-23 1.0.0 First release of CodecUtilsWin32 maintained by Dieter
//                  Köhler that is based on the cUnicodeCodecs.pas unit from
//                  David J Butler and Dieter Köhler.

{$IFDEF VER140}
  {$DEFINE VER140+}
{$ENDIF}
{$IFDEF VER150}
  {$DEFINE VER140+}
{$ENDIF}
{$IFDEF VER160}
  {$DEFINE VER140+}
{$ENDIF}
{$IFDEF VER170}
  {$DEFINE VER140+}
{$ENDIF}
{$IFDEF VER180}
  {$DEFINE VER140+}
{$ENDIF}
{$IFDEF VER185}
  {$DEFINE VER140+}
{$ENDIF}
{$IFDEF VER190}
  {$DEFINE VER140+}
{$ENDIF}
{$IFDEF VER200}
  {$DEFINE VER140+}
{$ENDIF}

unit CodecUtilsWin32;

interface

uses
{$IFDEF UNICODE}
  AnsiStrings,
{$ENDIF}
  EncodingUtils, SysUtils, Classes;

{                                                                              }
{ UCS-4 and TBytes definitions                                                 }
{                                                                              }
{$IFNDEF VER140+}  // Remark: "Declared" is not defined in Delphi 5.
type
  UCS4Char = LongWord;
  TBytes = array of Byte;
{$ELSE}
  {$IF not Declared(TBytes)}
type
  TBytes = array of Byte;
  {$IFEND}
{$ENDIF}



{                                                                              }
{ TEncodingRepository class                                                    }
{   Provides information about all available codecs.                           }
{                                                                              }
type
  TUnicodeCodec = class;

  TEncodingRepository = class
  public
    // General encoding functions
    class function  CreateCodecByAlias(const CodecAlias: string): TUnicodeCodec; virtual;

    // Encoding detection functions
    class function  CreateCodecForStream(const Stream: TStream): TUnicodeCodec; virtual;

{$IFNDEF LINUX}
    // Windows system encoding functions
    class function  CreateCodecForSystemEncoding: TUnicodeCodec; virtual; {$IFDEF VER140+}platform;{$ENDIF}
    class function  SystemEncodingName: string; virtual; {$IFDEF VER140+}platform;{$ENDIF}
{$ENDIF}


    class function  Count: Integer; virtual;
    class function  EncodingName(const I: Integer): string; virtual;
  end;



{                                                                              }
{ TCustomUnicodeCodec                                                          }
{   Base class for Unicode Codec implementations.                              }
{                                                                              }
  TCodecErrorAction = (
      eaException,   // Raise an exception (default)
      eaStop,        // Stop encoding/decoding
      eaIgnore,      // Ignore error and continue
      eaSkip,        // Skip character and continue
      eaReplace);    // Replace invalid character and continue

  TCodecReadLFOption = (
      lrPass,        // No normalization takes place (default)
      lrNormalize);  // Line breaks are adjusted to Linux-style breaks with a
                     // single LINE FEED, i.e. a sequence of CARRIAGE RETURN
                     // ($0D) + LINE FEED ($0A) or a single CARRIAGE RETURN is
                     // normalized to a single LINE FEED ($0A)

  TCodecWriteLFOption = (
      lwLF,          // Transcode LINE FEED into LINE FEED (default)
      lwCR,          // Transcode LINE FEED into CARRIAGE RETURN
      lwCRLF);       // Transcode LINE FEED into CARRIAGE RETURN + LINE FEED

  TCodecReadEvent = procedure (Sender: TObject; var Buf; Count: Longint;
                               var Ok: Boolean) of object;
  TCodecWriteEvent = procedure (Sender: TObject; const Buf; Count: Longint)
                                of object;

  TUnicodeCodec = class
  private
    FErrorAction        : TCodecErrorAction;
    FDecodeReplaceChar  : WideChar;
    FReadLFOption       : TCodecReadLFOption;
    FWriteLFOption      : TCodecWriteLFOption;
    FOnRead             : TCodecReadEvent;
    FOnWrite            : TCodecWriteEvent;
    FReadAhead          : Boolean;   // Flag used for LF input normalization
    FReadAheadBuffer    : UCS4Char;  // Buffer storage LF input normalization
    FReadAheadByteCount : Integer;
  protected
    class function EncodingInfoClass: TEncodingInfoClass; virtual;

    procedure ResetReadAhead;

    procedure SetDecodeReplaceChar(const Value: WideChar);
    procedure SetErrorAction(const Value: TCodecErrorAction);
    procedure SetReadLFOption(const Value: TCodecReadLFOption); virtual;
    procedure SetWriteLFOption(const Value: TCodecWriteLFOption); virtual;
    procedure SetOnRead(const Value: TCodecReadEvent);

    function  ReadBuffer(var Buf; Count: Integer): Boolean;
    procedure WriteBuffer(const Buf; Count: Integer);

    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); virtual; abstract;
              // Implementation guidelines for derived classes:
              // ReadUCS4Char calls (if necessary repeatedly and sometimes
              // ahead) the InternalReadUCS4Char function, which must call
              // ReadBuffer to request buffer values. ReadBuffer calls the
              // OnRead event to request buffer values, similar to the Delphi
              // VCL TStream.Read function.
              // It must raise an EConvertError exception if the byte values
              // returned by the OnRead event contain code that cannot be
              // converted to a UCS-4 character or if the result value falls
              // into the reserved surrogate area [$D800..$DFFF].
              // If ReadBuffer returns False, the UCS-4 character $9C
              // (STRING TERMINATOR) must be returned.
              // LINE FEED characters ($A) are transformed according to the
              // value of the ReadLFOption property by the ReadUCS4Char
              // function.
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); virtual; abstract;
              // Implementation guideline for derived classes:
              // WriteUCS4Char calls (if necessary repeatedly) the
              // InternalWriteUCS4Char procedure, which must call WriteBuffer
              // to write buffer values. WriteBuffer calls the OnWrite event
              // to send the buffer values, similar to the Delphi VCL
              // TStream.Write function.
              // It must raise an EConvertError exception if the specified
              // UCS-4 character cannot be converted into the target encoding.
              // If no OnWrite event handler is assigned calling WriteUCS4Char
              // simply has no effect.
              // LINE FEED characters ($A) are transformed according to the
              // value of the WriteLFOption property by the WriteUCS4Char
              // procedure.

  public
    constructor Create; virtual;

    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); virtual; abstract;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; virtual; abstract;

    procedure DecodeStr(const Buf: Pointer; const BufSize: Integer;
              var Dest: WideString);
    function  EncodeStr(const S: WideString): AnsiString;

    procedure ReadUCS4Char(out C: UCS4Char; out ByteCount: Integer);
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); virtual;
              // Implementation guideline for derived classes:
              // WriteUCS4Char can be overridden to implement more efficient
              // handling of LINE FEED character transformations.

    class function Alias(I: Integer): string; virtual;
    class function AliasCount: Integer; virtual;
    class function AliasIndexOf(const S: string): Integer; virtual;
              // Looks for the alias case-insensitively.
    class function GetPreamble: TBytes; virtual;
              // Returns the Byte Order Mark for the encoding, if any.
    class function HasAlias(const S: string): Boolean; virtual;
    class function MIBenum: Integer; virtual;
    class function Name: string; virtual;
    class function PreferredMIMEName: string; virtual;

    property  ErrorAction: TCodecErrorAction read FErrorAction write SetErrorAction default eaException;
    property  DecodeReplaceChar: WideChar read FDecodeReplaceChar write SetDecodeReplaceChar default #$FFFD;
    property  ReadLFOption: TCodecReadLFOption read FReadLFOption write SetReadLFOption default lrPass;
    property  WriteLFOption: TCodecWriteLFOption read FWriteLFOption write SetWriteLFOption default lwLF;

    property  OnRead: TCodecReadEvent read FOnRead write SetOnRead;
    property  OnWrite: TCodecWriteEvent read FOnWrite write FOnWrite;
  end;

  EUnicodeCodecException = class(Exception)
    ProcessedBytes : Integer;
  end;



{                                                                              }
{ TUTF8Codec                                                                   }
{   Unicode Codec implementation for UTF-8.                                    }
{                                                                              }
type
  TUTF8Codec = class(TUnicodeCodec)
  private
    procedure UCS4CharToUTF8(const Ch: UCS4Char; const Dest: Pointer;
              const DestSize: Integer; out SeqSize: Integer);
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    function  GetCharCount(const S: AnsiString): Integer;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;

    class function GetPreamble: TBytes; override;
  end;



{                                                                              }
{ TUTF16BECodec                                                                }
{   Unicode Codec implementation for UTF-16BE.                                 }
{                                                                              }
type
  TUTF16BECodec = class(TUnicodeCodec)
  private
    procedure UCS4CharToUTF16BE(const Ch: UCS4Char; const Dest: Pointer;
              const DestSize: Integer; out SeqSize: Integer);
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;

    class function GetPreamble: TBytes; override;
  end;



{                                                                              }
{ TUTF16LECodec                                                                }
{   Unicode Codec implementation for UTF-16LE.                                 }
{                                                                              }
type
  TUTF16LECodec = class(TUnicodeCodec)
  private
    procedure UCS4CharToUTF16LE(const Ch: UCS4Char; const Dest: Pointer;
              const DestSize: Integer; out SeqSize: Integer);
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;

    class function GetPreamble: TBytes; override;
  end;



{                                                                              }
{ TUCS4BECodec                                                                 }
{   Unicode Codec implementation for ISO 10646 UCS-4BE.                        }
{                                                                              }
type
  TUCS4BECodec = class(TUnicodeCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;

    class function GetPreamble: TBytes; override;
  end;



{                                                                              }
{ TUCS4LECodec                                                                 }
{   Unicode Codec implementation for ISO 10646 UCS-4BE.                        }
{                                                                              }
type
  TUCS4LECodec = class(TUnicodeCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;

    class function GetPreamble: TBytes; override;
  end;



{                                                                              }
{ TUCS4_2143Codec                                                              }
{   Unicode Codec implementation for ISO 10646 UCS-4BE.                        }
{                                                                              }
type
  TUCS4_2143Codec = class(TUnicodeCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;

    class function GetPreamble: TBytes; override;
  end;



{                                                                              }
{ TUCS4_3412Codec                                                              }
{   Unicode Codec implementation for ISO 10646 UCS-4BE.                        }
{                                                                              }
type
  TUCS4_3412Codec = class(TUnicodeCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;

    class function GetPreamble: TBytes; override;
  end;



{                                                                              }
{ TUCS2BECodec                                                                 }
{   Unicode Codec implementation for ISO 10646 UCS-2 in Big Endian byte        }
{   order.                                                                     }
{                                                                              }
type
  TUCS2BECodec = class(TUnicodeCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;
  end;



{                                                                              }
{ TUCS2LECodec                                                                 }
{   Unicode Codec implementation for ISO 10646 UCS-2 in Little Endian byte     }
{   order.                                                                     }
{                                                                              }
type
  TUCS2LECodec = class(TUnicodeCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override;
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override;
    procedure WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer); override;
  end;



{                                                                              }
{ TCustomSingleByteCodec                                                       }
{   Base class for single-byte encodings.                                      }
{                                                                              }
type
  TCustomSingleByteCodec = class(TUnicodeCodec)
  protected
    FEncodeReplaceChar : AnsiChar;

    procedure InternalReadUCS4Char(out C: UCS4Char;
              out ByteCount: Integer); override;
    procedure InternalWriteUCS4Char(const C: UCS4Char;
              out ByteCount: Integer); override;

  public
    constructor Create; override;

    property  EncodeReplaceChar: AnsiChar read FEncodeReplaceChar write FEncodeReplaceChar default #$20;

    procedure Decode(const Buf: Pointer; const BufSize: Integer;
              const DestBuf: Pointer; const DestSize: Integer;
              out ProcessedBytes, DestLength: Integer); override; 
    function  Encode(const S: PWideChar; const Length: Integer;
              out ProcessedChars: Integer): AnsiString; override; 

    function  DecodeChar(const P: AnsiChar): WideChar; virtual; abstract;
    function  EncodeChar(const Ch: WideChar): AnsiChar; virtual; abstract;

    function  DecodeUCS4Char(const P: AnsiChar): UCS4Char; virtual;
    function  EncodeUCS4Char(const Ch: UCS4Char): AnsiChar; virtual;
  end;



{                                                                              }
{ ISO-8859                                                                     }
{                                                                              }
type
  TISO8859_1Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_2Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_3Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_4Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_5Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_6Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_7Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_8Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_9Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_10Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_13Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_14Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TISO8859_15Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;



{                                                                              }
{ Windows                                                                      }
{                                                                              }
type
  TWindows708Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows737Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows775Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows858Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows864Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows869Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows870Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows874Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows875Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1026Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1047Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1140Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1141Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1142Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1143Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1144Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1145Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1146Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1147Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1148Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1149Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TWindows1250Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1251Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1252Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1253Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1254Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1255Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1256Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1257Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TWindows1258Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;



{                                                                              }
{ IBM                                                                          }
{                                                                              }
type
  TIBM037Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM038Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM256Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM273Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM274Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM275Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM277Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM278Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM280Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM281Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM284Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM285Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM290Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM297Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM420Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM423Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM424Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM437Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM500Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM850Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM851Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM852Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM855Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM857Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM860Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM861Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM862Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM863Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM864Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM865Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM866Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM868Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM869Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM870Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM871Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM874Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TIBM875Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TIBM880Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM904Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM905Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM918Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM1004Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TIBM1026Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TIBM1047Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;




{                                                                              }
{ Macintosh                                                                    }
{                                                                              }
type
  TMacLatin2Codec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TMacRomanCodec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TMacCyrillicCodec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TMacGreekCodec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TMacIcelandicCodec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;

  TMacTurkishCodec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;




{                                                                              }
{ International                                                                }
{                                                                              }
type
  TUSASCIICodec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;

    // Checks whether the specified string is US-ASCII:
    class function CheckA(const S: AnsiString): Boolean;
    class function CheckW(const S: WideString): Boolean;
  end;

  TEBCDIC_USCodec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TKOI8_RCodec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TJIS_X0201Codec = class(TCustomSingleByteCodec)
  protected
    class function EncodingInfoClass: TEncodingInfoClass; override;
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
  end;

  TNextStepCodec = class(TCustomSingleByteCodec)
  public
    function  DecodeChar(const P: AnsiChar): WideChar; override;
    function  EncodeChar(const Ch: WideChar): AnsiChar; override;
    class function Name: string; override;
  end;



implementation

{$IFNDEF LINUX}
uses
  { Delphi }
  Windows;
{$ENDIF}



resourcestring
  SCannotConvert           = 'Unicode code point $%x has no equivalent in %s';
  SCannotConvertUCS4       = 'Cannot convert $%8.8X to %s';
  SHighSurrogateNotFound   = 'High surrogate not found';
  SIndexOutOfRange         = 'Index out of range';
  SInvalidCodePoint        = '$%x is not a valid %s code point';
  SInvalidEncoding         = 'Invalid %s encoding';
  SLowSurrogateNotFound    = 'Low surrogate not found';
  SSurrogateNotAllowed     = 'Surrogate value $%x found in %s. Values between $D800 and $DFFF are reserved for use with UTF-16';
  SEncodingOutOfRange      = '%s encoding out of range';
  SUTF8Error               = 'UTF-8 error %d';



{                                                                              }
{ Type definitions                                                             }
{                                                                              }
{$IFNDEF VER140+}
type
  PByte     = ^Byte;
  PWord     = ^Word;
  PLongWord = ^LongWord;
{$ENDIF}



{                                                                              }
{ Unicode character constants                                                  }
{                                                                              }
const
  UCS4_STRING_TERMINATOR = $9C;
  UCS4_LF                = $0A;
  UCS4_CR                = $0D;



{                                                                              }
{ Helper Functions                                                             }
{                                                                              }

function SwapUTF16Endian(const P: WideChar): WideChar;
begin
  Result := WideChar(((Ord(P) and $FF) shl 8) or (Ord(P) shr 8));
end;

type
  AnsiCharMap = array[Byte] of WideChar;

function CharFromMap(const Ch: WideChar; const Map: AnsiCharMap;
    const Encoding: string): AnsiChar;
var I : Byte;
    P : PWideChar;
begin
  if Ch = #$FFFF then
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), Encoding]);
  P := @Map;
  for I := $00 to $FF do
    if P^ <> Ch then
      Inc(P)
    else
      begin
        Result := AnsiChar(I);
        Exit;
      end;
  raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), Encoding]);
end;

type
  AnsiCharHighMap = array[$80..$FF] of WideChar;

function CharFromHighMap(const Ch: WideChar; const Map: AnsiCharHighMap;
    const Encoding: string): AnsiChar;
var I : Byte;
    P : PWideChar;
begin
  if Ord(Ch) < $80 then
    begin
      Result := AnsiChar(Ch);
      Exit;
    end;
  if Ch = #$FFFF then
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), Encoding]);
  P := @Map;
  for I := $80 to $FF do
    if P^ <> Ch then
      Inc(P)
    else
      begin
        Result := AnsiChar(I);
        Exit;
      end;
  raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), Encoding]);
end;

type
  AnsiCharISOMap = array[$A0..$FF] of WideChar;

function CharFromISOMap(const Ch: WideChar; const Map: AnsiCharISOMap;
    const Encoding: string): AnsiChar;
var I : Byte;
    P : PWideChar;
begin
  if Ord(Ch) < $A0 then
    begin
      Result := AnsiChar(Ch);
      Exit;
    end;
  if Ch = #$FFFF then
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), Encoding]);
  P := @Map;
  for I := $A0 to $FF do
    if P^ <> Ch then
      Inc(P)
    else
      begin
        Result := AnsiChar(I);
        Exit;
      end;
  raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), Encoding]);
end;


type
  TUnicodeCodecClass = class of TUnicodeCodec;

{                                                                              }
{ TEncodingRepository class                                                    }
{                                                                              }
const
  UnicodeCodecs: array[0..106] of TUnicodeCodecClass = (
    TUTF8Codec,
    TUTF16LECodec,
    TUTF16BECodec,
    TUCS4LECodec,
    TUCS4BECodec,
    TUCS4_2143Codec,
    TUCS4_3412Codec,
    TUCS2LECodec,
    TUCS2BECodec,
    TISO8859_1Codec,
    TISO8859_2Codec,
    TISO8859_3Codec,
    TISO8859_4Codec,
    TISO8859_5Codec,
    TISO8859_6Codec,
    TISO8859_7Codec,
    TISO8859_8Codec,
    TISO8859_9Codec,
    TISO8859_10Codec,
    TISO8859_13Codec,
    TISO8859_14Codec,
    TISO8859_15Codec,
    TWindows708Codec,
    TWindows737Codec,
    TWindows775Codec,
    TWindows858Codec,
    TWindows864Codec,
    TWindows869Codec,
    TWindows870Codec,
    TWindows874Codec,
    TWindows875Codec,
    TWindows1026Codec,
    TWindows1047Codec,
    TWindows1140Codec,
    TWindows1141Codec,
    TWindows1142Codec,
    TWindows1143Codec,
    TWindows1144Codec,
    TWindows1145Codec,
    TWindows1146Codec,
    TWindows1147Codec,
    TWindows1148Codec,
    TWindows1149Codec,
    TWindows1250Codec,
    TWindows1251Codec,
    TWindows1252Codec,
    TWindows1253Codec,
    TWindows1254Codec,
    TWindows1255Codec,
    TWindows1256Codec,
    TWindows1257Codec,
    TWindows1258Codec,
    TIBM037Codec,
    TIBM038Codec,
    TIBM256Codec,
    TIBM273Codec,
    TIBM274Codec,
    TIBM275Codec,
    TIBM277Codec,
    TIBM278Codec,
    TIBM280Codec,
    TIBM281Codec,
    TIBM284Codec,
    TIBM285Codec,
    TIBM290Codec,
    TIBM297Codec,
    TIBM420Codec,
    TIBM423Codec,
    TIBM424Codec,
    TIBM437Codec,
    TIBM500Codec,
    TIBM850Codec,
    TIBM851Codec,
    TIBM852Codec,
    TIBM855Codec,
    TIBM857Codec,
    TIBM860Codec,
    TIBM861Codec,
    TIBM862Codec,
    TIBM863Codec,
    TIBM864Codec,
    TIBM865Codec,
    TIBM866Codec,
    TIBM868Codec,
    TIBM869Codec,
    TIBM870Codec,
    TIBM871Codec,
    TIBM874Codec,
    TIBM875Codec,
    TIBM880Codec,
    TIBM904Codec,
    TIBM905Codec,
    TIBM918Codec,
    TIBM1004Codec,
    TIBM1026Codec,
    TIBM1047Codec,
    TMacLatin2Codec,
    TMacRomanCodec,
    TMacCyrillicCodec,
    TMacGreekCodec,
    TMacIcelandicCodec,
    TMacTurkishCodec,
    TUSASCIICodec,
    TEBCDIC_USCodec,
    TKOI8_RCodec,
    TJIS_X0201Codec,
    TNextStepCodec
  );

class function TEncodingRepository.Count: Integer;
begin
  Result := Length(UnicodeCodecs);
end;

class function TEncodingRepository.CreateCodecByAlias(const CodecAlias: string): TUnicodeCodec;
var
  I : Integer;
begin
  for I := 0 to Length(UnicodeCodecs) - 1 do
    begin
      if UnicodeCodecs[I].HasAlias(CodecAlias) then begin
        Result := UnicodeCodecs[I].Create;
        Exit;
      end;
    end;
  Result := nil;
end;

{ Evaluates the Byte Order Mark of the stream and returns an appropriate Codec }
{ object, or nil of no Byte Order Mark had been detected.                      }
class function TEncodingRepository.CreateCodecForStream(const Stream: TStream): TUnicodeCodec;
var
  // Remark: We always use Byte arrays for platform independence.
  A: array[0..1] of Byte;
  B: array[0..0] of Byte;
  W: array[0..1] of Byte;
  PositionBackup: Int64;
begin
  Result := nil;
  with Stream do
  begin
    PositionBackup := Position;
    try
      if Read(A, 2) = 2 then
        case A[0] of
          $00: if A[1] = $00 then
              if Read(A, 2) = 2 then
                if (A[0] = $FE) and (A[1] = $FF) then
                begin
                  Result := TUCS4BECodec.Create;
                end
                else if (A[0] = $FF) and (A[1] = $FE) then
                begin
                  Result := TUCS4_2143Codec.Create;
                end;
          $FE: if A[1] = $FF then
              if Read(W, 2) = 2 then
              begin
                if (W[0] = $00) and (W[1] = $00) then
                begin
                  Result := TUCS4_3412Codec.Create;
                end
                else
                begin
                  Result := TUTF16BECodec.Create;
                end;
              end
              else
              begin
                Result := TUTF16BECodec.Create;
              end;
          $FF: if (A[1] = $FE) then
              if Read(W, 2) = 2 then
              begin
                if (W[0] = $00) and (W[1] = $00) then
                begin
                  Result := TUCS4LECodec.Create;
                end
                else
                begin
                  Result := TUTF16LECodec.Create;
                end;
              end
              else
              begin
                Result := TUTF16LECodec.Create;
              end;
          $EF: if (A[1] = $BB) then
              if Read(B, 1) = 1 then
                if B[0] = $BF then
                begin
                  Result := TUTF8Codec.Create;
                end;
        end;
    finally
      Position := PositionBackup;
    end;
  end;
end;

{$IFNDEF LINUX}
class function TEncodingRepository.CreateCodecForSystemEncoding: TUnicodeCodec;
begin
  case GetACP of
    874  : Result := TWindows874Codec.Create;   // Thai
    932  : Result := nil;                       // Japan  --  Not supported
    936  : Result := nil;                       // Chinese (PRC, Singapore) --  Not supported
    949  : Result := nil;                       // Korean  --  Not supported
    950  : Result := nil;                       // Chinese (Taiwan, Hong Kong)  --  Not supported
    1200 : Result := nil;                       // Unicode (BMP of ISO 10646)  --  Not supported
    1250 : Result := TWindows1250Codec.Create;  // Windows 3.1 Eastern European
    1251 : Result := TWindows1251Codec.Create;  // Windows 3.1 Cyrillic
    1252 : Result := TWindows1252Codec.Create;  // Windows 3.1 Latin 1 (US, Western Europe)
    1253 : Result := TWindows1253Codec.Create;  // Windows 3.1 Greek
    1254 : Result := TWindows1254Codec.Create;  // Windows 3.1 Turkish
    1255 : Result := TWindows1255Codec.Create;  // Hebrew
    1256 : Result := TWindows1256Codec.Create;  // Arabic
    1257 : Result := TWindows1257Codec.Create;  // Baltic
  else
    Result := nil;
  end;
end;

class function TEncodingRepository.SystemEncodingName: string;
begin
  // GetACP returns the current ANSI code-page identifier for the system,
  // or a default identifier if no code page is current.
  case GetACP of
    874  : Result := 'cp874';            // Thai
    932  : Result := 'cp932';            // Japan
    936  : Result := 'cp936';            // Chinese (PRC, Singapore)
    949  : Result := 'cp949';            // Korean
    950  : Result := 'cp950';            // Chinese (Taiwan, Hong Kong)
    1200 : Result := 'ISO-10646-UCS-2';  // Unicode (BMP of ISO 10646)
    1250 : Result := 'windows-1250';     // Windows 3.1 Eastern European
    1251 : Result := 'windows-1251';     // Windows 3.1 Cyrillic
    1252 : Result := 'windows-1252';     // Windows 3.1 Latin 1 (US, Western Europe)
    1253 : Result := 'windows-1253';     // Windows 3.1 Greek
    1254 : Result := 'windows-1254';     // Windows 3.1 Turkish
    1255 : Result := 'windows-1255';     // Hebrew
    1256 : Result := 'windows-1256';     // Arabic
    1257 : Result := 'windows-1257';     // Baltic
  else
    Result := '';
  end;
end;
{$ENDIF}

class function TEncodingRepository.EncodingName(const I: Integer): string;
begin
  if (I < 0) or (I >= Count) then
    raise EAccessViolation.Create(SIndexOutOfRange);
  Result := UnicodeCodecs[I].Name;
end;



{                                                                              }
{ EUnicodeCodecException helper functions                                      }
{                                                                              }
procedure RaiseUnicodeCodecException(const Msg: string;
    const ProcessedBytes: Integer); overload;
var E : EUnicodeCodecException;
begin
  E := EUnicodeCodecException.Create(Msg);
  E.ProcessedBytes := ProcessedBytes;
  raise E;
end;

procedure RaiseUnicodeCodecException(const Msg: string; const Args: array of const;
    const ProcessedBytes: Integer); overload;
var E : EUnicodeCodecException;
begin
  E := EUnicodeCodecException.CreateFmt(Msg, Args);
  E.ProcessedBytes := ProcessedBytes;
end;



{                                                                              }
{ TCustomUnicodeCodec                                                          }
{                                                                              }
constructor TUnicodeCodec.Create;
begin
  inherited Create;
  FDecodeReplaceChar := WideChar(#$FFFD);
  FErrorAction := eaException;
  FReadLFOption := lrPass;
  FWriteLFOption := lwLF;
  ResetReadAhead;
end;

procedure TUnicodeCodec.ResetReadAhead;
begin
  FReadAhead := False;
  FReadAheadBuffer := 0;
end;

class function TUnicodeCodec.Alias(I: Integer): string;
begin
  if EncodingInfoClass = nil
  then
    begin
      if (I = 0) and (Name <> '') then
        Result := Name
      else
        raise EAccessViolation.Create(SIndexOutOfRange);
    end
  else
    Result := EncodingInfoClass.Alias(I);
end;

class function TUnicodeCodec.AliasCount: Integer;
begin
  if EncodingInfoClass = nil
  then
    begin
      if Name = '' then
        Result := 0
      else
        Result := 1;
    end
  else
    Result := EncodingInfoClass.AliasCount;
end;

class function TUnicodeCodec.AliasIndexOf(const S: string): Integer;
var
  I: Integer;
begin
  for I := 0 to AliasCount - 1 do
    begin
      if CompareText(S, Alias(I)) = 0 then
        begin
          Result := I;
          Exit;
        end;
    end;
  Result := -1;
end;

class function TUnicodeCodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := nil;
end;

class function TUnicodeCodec.GetPreamble: TBytes;
begin
  Result := nil;
end;

class function TUnicodeCodec.HasAlias(const S: string): Boolean;
begin
  Result := AliasIndexOf(S) <> -1;
end;

class function TUnicodeCodec.MIBenum: Integer;
begin
  if EncodingInfoClass = nil
    then Result := 1
    else Result := EncodingInfoClass.MIBenum;
end;

class function TUnicodeCodec.Name: string;
begin
  if EncodingInfoClass = nil
    then Result := ''
    else Result := EncodingInfoClass.Name;
end;

class function TUnicodeCodec.PreferredMIMEName: string;
begin
  if EncodingInfoClass = nil
    then Result := ''
    else Result := EncodingInfoClass.PreferredMIMEName;
end;

procedure TUnicodeCodec.SetDecodeReplaceChar(const Value: WideChar);
begin
  FDecodeReplaceChar := Value;
end;

procedure TUnicodeCodec.SetErrorAction(const Value: TCodecErrorAction);
begin
  FErrorAction := Value;
end;

procedure TUnicodeCodec.SetReadLFOption(const Value: TCodecReadLFOption);
begin
  FReadLFOption := Value;
end;

procedure TUnicodeCodec.SetWriteLFOption(const Value: TCodecWriteLFOption);
begin
  FWriteLFOption := Value;
end;

procedure TUnicodeCodec.SetOnRead(const Value: TCodecReadEvent);
begin
  if @Value <> @FOnRead then
    begin
      ResetReadAhead;
      FOnRead := Value;
    end;
end;

procedure TUnicodeCodec.DecodeStr(const Buf: Pointer; const BufSize: Integer;
    var Dest: WideString);
var P    : PAnsiChar;
    Q    : PWideChar;
    L, M : Integer;
    I, J : Integer;
begin
  P := Buf;
  L := BufSize;
  if not Assigned(P) or (L <= 0) then
    begin
      Dest := '';
      Exit;
    end;
  SetLength(Dest, BufSize);
  M := 0;
  repeat
    Q := Pointer(Dest);
    Inc(Q, M);
    Decode(P, L, Q, BufSize * Sizeof(WideChar), I, J);
    Dec(L, I);
    Inc(P, I);
    Inc(M, J);
    if (J < BufSize) or (L <= 0) then
      Break;
    SetLength(Dest, M + BufSize);
  until False;
  if Length(Dest) <> M then
    SetLength(Dest, M);
end;

function TUnicodeCodec.EncodeStr(const S: WideString): AnsiString;
var I : Integer;
begin
  Result := Encode(Pointer(S), Length(S), I);
end;

function TUnicodeCodec.ReadBuffer(var Buf; Count: Integer): Boolean;
begin
  Result := False;
  if Assigned(FOnRead) then
    FOnRead(self, Buf, Count, Result);
end;

procedure TUnicodeCodec.WriteBuffer(const Buf; Count: Integer);
begin
  if Assigned(FOnWrite) then
    FOnWrite(self, Buf, Count);
end;

procedure TUnicodeCodec.ReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
begin
  // Get UCS4 character from read-ahead buffer or from InternalReadUCS4Char
  if FReadAhead then
    begin
      C := FReadAheadBuffer;
      ByteCount := FReadAheadByteCount;
      FReadAhead := False;
    end
  else
    InternalReadUCS4Char(C, ByteCount);
  // Adjust line breaks to Linux-style breaks with a single LINE FEED character
  if (C = UCS4_CR) and (ReadLFOption = lrNormalize) then
    begin
      InternalReadUCS4Char(FReadAheadBuffer, FReadAheadByteCount);
      if FReadAheadBuffer = UCS4_LF then
        Inc(ByteCount, FReadAheadByteCount)
      else
        FReadAhead := True;
      C := UCS4_LF;
    end;
end;

procedure TUnicodeCodec.WriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var ByteCount2 : Integer;
begin
  if C = UCS4_LF then
    // Transform LINE FEED character
    case WriteLFOption of
      lwLF : InternalWriteUCS4Char(UCS4_LF, ByteCount);
      lwCR : InternalWriteUCS4Char(UCS4_CR, ByteCount);
      lwCRLF :
        begin
          InternalWriteUCS4Char(UCS4_CR, ByteCount);
          InternalWriteUCS4Char(UCS4_LF, ByteCount2);
          Inc(ByteCount, ByteCount2);
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ TCustomSingleByteCodec                                                       }
{                                                                              }
constructor TCustomSingleByteCodec.Create;
begin
  inherited Create;
  FEncodeReplaceChar := AnsiChar(#$20);
end;

procedure TCustomSingleByteCodec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);
var P       : PAnsiChar;
    Q       : PWideChar;
    I, L, C : Integer;
begin
  P := Buf;
  Q := DestBuf;
  C := DestSize div Sizeof(WideChar);
  if not Assigned(P) or (BufSize <= 0) or not Assigned(Q) or (C <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  L := 0;
  for I := 1 to BufSize do
    try
      if L >= C then
        Break;
      Q^ := DecodeChar(P^);
      Inc(P);
      Inc(Q);
      Inc(L);
    except
      on E : Exception do
        case FErrorAction of
          eaException :
            RaiseUnicodeCodecException(E.Message, P - Buf);
          eaStop :
            Break;
          eaSkip :
            Inc(P);
          eaIgnore :
            begin
              Q^ := WideChar(P^);
              Inc(P);
              Inc(Q);
              Inc(L);
            end;
          eaReplace :
            begin
              Q^ := FDecodeReplaceChar;
              Inc(P);
              Inc(Q);
              Inc(L);
            end;
        end;
    end;
  DestLength := L;
  ProcessedBytes := P - Buf;
end;

function TCustomSingleByteCodec.DecodeUCS4Char(const P: AnsiChar): UCS4Char;
begin
  Result := Ord(DecodeChar(P));
end;

function TCustomSingleByteCodec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var P       : PAnsiChar;
    Q       : PWideChar;
    I, L, M : Integer;
begin
  Q := S;
  if not Assigned(Q) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  SetLength(Result, Length);
  L := 0;
  M := 0;
  P := Pointer(Result);
  for I := 1 to Length do
    try
      P^ := EncodeChar(Q^);
      Inc(P);
      Inc(Q);
      Inc(L);
      Inc(M);
    except
      on E : Exception do
        case FErrorAction of
          eaException :
            RaiseUnicodeCodecException(E.Message, L);
          eaStop :
            Break;
          eaSkip :
            begin
              Inc(Q);
              Inc(L);
            end;
          eaIgnore :
            begin
              P^ := AnsiChar(Q^);
              Inc(P);
              Inc(Q);
              Inc(L);
              Inc(M);
            end;
          eaReplace :
            begin
              P^ := FEncodeReplaceChar;
              Inc(P);
              Inc(Q);
              Inc(L);
              Inc(M);
            end;
        end;
    end;
  if Length <> M then
    SetLength(Result, M);
  ProcessedChars := L;
end;

function TCustomSingleByteCodec.EncodeUCS4Char(const Ch: UCS4Char): AnsiChar;
begin
  if Ch < $10000 then
    Result := EncodeChar(WideChar(Ch))
  else
    raise EConvertError.CreateFmt(SInvalidCodePoint, [Ch, '']);
end;

procedure TCustomSingleByteCodec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
var B : AnsiChar;
begin
  if ReadBuffer(B, 1) then
    begin
      C := Ord(DecodeChar(B));
      ByteCount := 1;
    end
  else
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
    end;
end;

procedure TCustomSingleByteCodec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var E : AnsiChar;
begin
  E := EncodeUCS4Char(C);
  WriteBuffer(E, 1);
  ByteCount := 1;
end;



{                                                                              }
{ UTF-8                                                                        }
{                                                                              }
procedure TUTF8Codec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);

  type
    TUTF8Error = (
        UTF8ErrorNone,
        UTF8ErrorInvalidEncoding,
        UTF8ErrorIncompleteEncoding,
        UTF8ErrorInvalidBuffer,
        UTF8ErrorOutOfRange );

  { UTF8ToUCS4Char returns UTF8ErrorNone if a valid UTF-8 sequence was decoded }
  { (and Ch contains the decoded UCS4 character and SeqSize contains the size  }
  { of the UTF-8 sequence). If an incomplete UTF-8 sequence is encountered,    }
  { the function returns UTF8ErrorIncompleteEncoding and SeqSize > Size. If an }
  { invalid UTF-8 sequence is encountered, the function returns                }
  { UTF8ErrorInvalidEncoding and SeqSize (<= Size) is the size of the          }
  { invalid sequence, and Ch may be the intended character.                    }
  function UTF8ToUCS4Char(const S: AnsiString; const Index: Integer;
      out SeqSize: Integer; out Ch: UCS4Char): TUTF8Error;
  var C, D : Byte;
      V    : Cardinal;
      I    : Integer;
      Size : Integer;
  begin
    Size := Length(S) - Index + 1;
    if Size <= 0 then
      begin
        SeqSize := 0;
        Ch := 0;
        Result := UTF8ErrorInvalidBuffer;
        Exit;
      end;
    C := Ord(S[Index]);
    if C < $80 then
      begin
        SeqSize := 1;
        Ch := C;
        Result := UTF8ErrorNone;
        Exit;
      end;
    // multi-byte characters always start with 11xxxxxx ($C0)
    // following bytes always start with 10xxxxxx ($80)
    if C and $C0 = $80 then
      begin
        SeqSize := 1;
        Ch := C;
        Result := UTF8ErrorInvalidEncoding;
        Exit;
      end;
    if C and $20 = 0 then // 2-byte sequence
      begin
        SeqSize := 2;
        V := C and $1F;
      end else
    if C and $10 = 0 then // 3-byte sequence
      begin
        SeqSize := 3;
        V := C and $0F;
      end else
    if C and $08 = 0 then // 4-byte sequence (max needed for Unicode $0-$1FFFFF)
      begin
        SeqSize := 4;
        V := C and $07;
      end else
      begin
        SeqSize := 1;
        Ch := C;
        Result := UTF8ErrorInvalidEncoding;
        Exit;
      end;
    if Size < SeqSize then // incomplete
      begin
        Ch := C;
        Result := UTF8ErrorIncompleteEncoding;
        Exit;
      end;
    for I := 1 to SeqSize - 1 do
      begin
        D := Ord(S[Index + I]);
        if D and $C0 <> $80 then // following byte must start with 10xxxxxx
          begin
            SeqSize := 1;
            Ch := C;
            Result := UTF8ErrorInvalidEncoding;
            Exit;
          end;
        V := (V shl 6) or (D and $3F); // decode 6 bits
      end;
    Ch := V;
    Result := UTF8ErrorNone;
  end;

  function UTF8ToWideChar(const S: AnsiString; const Index: Integer;
      out SeqSize: Integer; out Ch: WideChar): TUTF8Error;
  var Ch4 : UCS4Char;
  begin
    Result := UTF8ToUCS4Char(S, Index, SeqSize, Ch4);
    if Ch4 > $FFFF then
      begin
        Result := UTF8ErrorOutOfRange;
        Ch := #$0000;
      end else
      Ch := WideChar(Ch4);
  end;

var P    : PAnsiChar;
    Q    : PWideChar;
    L, I : Integer;
    M, N : Integer;
    R    : TUTF8Error;
    C    : WideChar;
begin
  P := Buf;
  L := BufSize;
  Q := DestBuf;
  N := DestSize div Sizeof(WideChar);
  if not Assigned(P) or (L <= 0) or not Assigned(Q) or (N <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  M := 0;
  repeat
    if M >= N then
      Break;
    try
      R := UTF8ToWideChar(P, L, I, C);
      case R of
        UTF8ErrorNone :
          begin
            Q^ := C;
            Inc(Q);
            Inc(M);
            Inc(P, I);
            Dec(L, I);
          end;
        UTF8ErrorInvalidEncoding :
          raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-8']);
        UTF8ErrorIncompleteEncoding :
          begin
            ProcessedBytes := BufSize - L;
            DestLength := M;
            Exit;
          end;
        UTF8ErrorOutOfRange :
          raise EConvertError.CreateFmt(SEncodingOutOfRange, ['UTF-8']);
      else
        raise EConvertError.CreateFmt(SUTF8Error, [Ord(R)]);
      end;
    except
      on E : Exception do
        case FErrorAction of
          eaException :
            RaiseUnicodeCodecException(E.Message, BufSize - L);
          eaStop :
            Break;
          eaSkip :
            begin
              Inc(P, I);
              Dec(L, I);
            end;
          eaIgnore :
            begin
              Q^ := C;
              Inc(Q);
              Inc(M);
              Inc(P, I);
              Dec(L, I);
            end;
          eaReplace :
            begin
              Q^ := FDecodeReplaceChar;
              Inc(Q);
              Inc(M);
              Inc(P, I);
              Dec(L, I);
            end;
        end;
    end;
  until L <= 0;
  ProcessedBytes := BufSize - L;
  DestLength := M;
end;

function TUTF8Codec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var P     : PWideChar;
    Q     : PAnsiChar;
    I, L,
    M, J  : Integer;
begin
  P := S;
  if not Assigned(P) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  L := Length * 3;
  SetLength(Result, L);
  Q := Pointer(Result);
  M := 0;
  for I := 1 to Length do
    begin
      UCS4CharToUTF8(Ord(P^), Q, L, J);
      Inc(P);
      Inc(Q, J);
      Dec(L, J);
      Inc(M, J);
    end;
  SetLength(Result, M);
  ProcessedChars := Length;
end;

class function TUTF8Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUTF8;
end;

function TUTF8Codec.GetCharCount(const S: AnsiString): Integer;

  function UTF8CharSize(const S: AnsiString; const Index: Integer): Integer;
  var C : Byte;
      I : Integer;
  begin
    if Length(S) <= 0 then
      begin
        Result := 0;
        Exit;
      end;
    C := Ord(S[Index]);
    if C < $80 then // 1-byte (US-ASCII value)
      Result := 1 else
    if C and $C0 = $80 then // invalid encoding
      Result := 1
    else
      begin
        // multi-byte character
        if C and $20 = 0 then
          Result := 2 else
        if C and $10 = 0 then
          Result := 3 else
        if C and $08 = 0 then
          Result := 4 else
          begin
            Result := 1; // invalid encoding
            Exit;
          end;
        if Index + Result - 1 > Length(S) then begin // incomplete encoding
          Result := Length(S) - Index + 1;
          Exit;
        end;
        for I := Index + 1 to Index + Result - 1 do
          if Ord(S[I]) and $C0 <> $80 then
          begin
            Result := 1; // invalid encoding
            Exit;
          end;
      end;
  end;

var
  C, L, Q: Integer;
begin
  Q := 1;
  L := Length(S);
  Result := 0;
  while L > 0 do
    begin
      C := UTF8CharSize(S, Q);
      Dec(L, C);
      Inc(Q, C);
      Inc(Result);
    end;
end;

class function TUTF8Codec.GetPreamble: TBytes;
begin
  SetLength(Result, 3);
  Result[0] := $EF;
  Result[1] := $BB;
  Result[2] := $BF;
end;

procedure TUTF8Codec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
const
  MaxCode: array[1..6] of LongWord =
      ($7F, $7FF, $FFFF, $1FFFFF, $3FFFFFF, $7FFFFFFF);
var B, First, Mask: Byte;
begin
  if not ReadBuffer(B, 1) then
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
      Exit;
    end;
  C := B;
  ByteCount := 1;
  if C >= $80 then
    begin // UTF-8 sequence
      First := B;
      Mask := $40;
      if (B and $C0 <> $C0) then
        raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-8']);
      while (Mask and First <> 0) do
        begin
          if not ReadBuffer(B, 1) then
            raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-8']);
          if (B and $C0) <> $80 then
            raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-8']);
          C := (C shl 6) or (B and $3F);  // Add bits to C
          Inc(ByteCount);                 // Increase sequence length
          Mask := Mask shr 1;             // Adjust Mask
        end;
      if ByteCount > 6 then  // No 0 bit in sequence header 'First'
        raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-8']);
      C := C and MaxCode[ByteCount];	// dispose of header bits
      // Check for invalid sequence as suggested by RFC2279
      if ((ByteCount > 1) and (C <= MaxCode[ByteCount - 1])) then
        raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-8']);
    end;
end;

procedure TUTF8Codec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var Buffer : array[0..3] of Byte;
begin
  UCS4CharToUTF8(C, @Buffer, 4, ByteCount);
  WriteBuffer(Buffer, ByteCount);
end;

procedure TUTF8Codec.WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer);
const
  UTF8_LF   : Byte = $0A;
  UTF8_CR   : Byte = $0D;
  UTF8_CRLF : array[0..1] of Byte = ($0D, $0A);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UTF8_LF, 1);
          ByteCount := 1;
        end;
      lwCR:
        begin
          WriteBuffer(UTF8_CR, 1);
          ByteCount := 1;
        end;
      lwCRLF:
        begin
          WriteBuffer(UTF8_CRLF, 2);
          ByteCount := 2;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;

{ UCS4CharToUTF8 transforms the UCS4Char Ch to UTF-8 encoding. SeqSize         }
{ returns the number of bytes needed to transform Ch. Up to DestSize           }
{ bytes of the UTF-8 encoding will be placed in Dest.                          }
procedure TUTF8Codec.UCS4CharToUTF8(const Ch: UCS4Char; const Dest: Pointer;
    const DestSize: Integer; out SeqSize: Integer);
var P : PByte;
begin
  P := Dest;
  if Ch < $80 then // US-ASCII (1-byte sequence)
    begin
      SeqSize := 1;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      P^ := Byte(Ch);
    end else
  if Ch < $800 then // 2-byte sequence
    begin
      SeqSize := 2;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      P^ := $C0 or Byte(Ch shr 6);
      if DestSize = 1 then
        Exit;
      Inc(P);
      P^ := $80 or (Ch and $3F);
    end else
  if Ch < $10000 then // 3-byte sequence
    begin
      SeqSize := 3;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      P^ := $E0 or Byte(Ch shr 12);
      if DestSize = 1 then
        Exit;
      Inc(P);
      P^ := $80 or ((Ch shr 6) and $3F);
      if DestSize = 2 then
        Exit;
      Inc(P);
      P^ := $80 or (Ch and $3F);
    end else
  if Ch < $200000 then // 4-byte sequence
    begin
      SeqSize := 4;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      P^ := $F0 or Byte(Ch shr 18);
      if DestSize = 1 then
        Exit;
      Inc(P);
      P^ := $80 or ((Ch shr 12) and $3F);
      if DestSize = 2 then
        Exit;
      Inc(P);
      P^ := $80 or ((Ch shr 6) and $3F);
      if DestSize = 3 then
        Exit;
      Inc(P);
      P^ := $80 or (Ch and $3F);
    end
  else
    raise EConvertError.CreateFmt(SInvalidCodePoint, [Ord(Ch), 'Unicode']);
end;



{                                                                              }
{ UTF-16BE                                                                     }
{                                                                              }
procedure TUTF16BECodec.Decode(const Buf: Pointer; const BufSize: Integer;
  const DestBuf: Pointer; const DestSize: Integer; out ProcessedBytes, DestLength: Integer);
var I, L, M : Integer;
    P, Q    : PWideChar;
begin
  L := BufSize;
  if L > DestSize then
    L := DestSize;
  if L <= 1 then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  Dec(L, L mod Sizeof(WideChar));
  M := L div Sizeof(WideChar);
  P := Buf;
  Q := DestBuf;
  for I := 1 to M do
    begin
      Q^ := SwapUTF16Endian(P^);
      Inc(P);
      Inc(Q);
    end;
  DestLength := M;
  ProcessedBytes := L;
end;

function TUTF16BECodec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var I, L : Integer;
    P, Q : PWideChar;
begin
  if Length <= 0 then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  L := Length * 2;
  SetLength(Result, L);
  P := S;
  Q := Pointer(Result);
  for I := 1 to Length do
    begin
      Q^ := SwapUTF16Endian(P^);
      Inc(P);
      Inc(Q);
    end;
  ProcessedChars := Length;
end;

class function TUTF16BECodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUTF16BE;
end;

class function TUTF16BECodec.GetPreamble: TBytes;
begin
  SetLength(Result, 2);
  Result[0] := $FE;
  Result[1] := $FF;
end;

procedure TUTF16BECodec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
var LowSurrogate: array[0..1] of Byte;  // We do not use Word, because the byte order of a Word is CPU dependant
begin
  C := 0;
  // C must be initialized, because the ReadBuffer(C, 2) call below does
  // not fill the whole variable!
  if not ReadBuffer(C, 2) then
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
      Exit;
    end;
  C := Swap(C);  // UCS4Chars are stored in Little Endian mode; so we need to swap the bytes.
  case C of
    $D800..$DBFF: // High surrogate of Unicode character [$10000..$10FFFF]
      begin
        if not ReadBuffer(LowSurrogate, 2) then
          raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-16BE']);
        case LowSurrogate[0] of
          $DC..$DF:
          begin
            C := ((C - $D7C0) shl 10) + ((LowSurrogate[0] xor $DC) shl 8) + LowSurrogate[1];
            ByteCount := 4;
          end;
        else
          raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-16BE']);
        end;
      end;
    $DC00..$DFFF: // Low surrogate of Unicode character [$10000..$10FFFF]
      raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-16BE']);
    else
      ByteCount := 2;
  end;
end;

procedure TUTF16BECodec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var Buffer : array[0..3] of Byte;
begin
  UCS4CharToUTF16BE(C, @Buffer, 4, ByteCount);
  WriteBuffer(Buffer[0], ByteCount);
end;

{ UCS4CharToUTF16BE transforms the UCS4Char Ch to UTF-16BE encoding. SeqSize   }
{ returns the number of bytes needed to transform Ch. Up to DestSize           }
{ bytes of the UTF-16BE encoding will be placed in Dest.                       }
procedure TUTF16BECodec.UCS4CharToUTF16BE(const Ch: UCS4Char; const Dest: Pointer;
  const DestSize: Integer; out SeqSize: Integer);
var P : PByte;
    HighSurrogate, LowSurrogate : Word;
begin
  P := Dest;
  case Ch of
  $00000000..$0000D7FF, $0000E000..$0000FFFF :
    begin
      SeqSize := 2;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      P^ := Hi(Ch);
      if DestSize <= 1 then
        Exit;
      Inc(P);
      P^ := Lo(Ch);
    end;
  $0000D800..$0000DFFF :
    raise EConvertError.CreateFmt(SInvalidCodePoint, [Ch, 'UCS-4']);
  $00010000..$0010FFFF :
    begin
      SeqSize := 4;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      HighSurrogate := $D7C0 + (Ch shr 10);
      P^ := Hi(HighSurrogate);
      if DestSize <= 1 then
        Exit;
      Inc(P);
      P^ := Lo(HighSurrogate);
      if DestSize <= 2 then
        Exit;
      LowSurrogate := $DC00 xor (Ch and $3FF);
      Inc(P);
      P^ := Hi(LowSurrogate);
      if DestSize <= 3 then
        Exit;
      Inc(P);
      P^ := Lo(LowSurrogate);
    end;
  else // out of UTF-16 range
    raise EConvertError.CreateFmt(SCannotConvertUCS4, [Ch, 'UTF-16BE']);
  end;
end;

procedure TUTF16BECodec.WriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
const
  UTF16BE_LF   : array[0..1] of Byte = ($00, $0A);
  UTF16BE_CR   : array[0..1] of Byte = ($00, $0D);
  UTF16BE_CRLF : array[0..3] of Byte = ($00, $0D, $00, $0A);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UTF16BE_LF, 2);
          ByteCount := 2;
        end;
      lwCR:
        begin
          WriteBuffer(UTF16BE_CR, 2);
          ByteCount := 2;
        end;
      lwCRLF:
        begin
          WriteBuffer(UTF16BE_CRLF, 4);
          ByteCount := 4;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ UTF-16LE                                                                     }
{                                                                              }
procedure TUTF16LECodec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);
var
  L, M : Integer;
  P, Q : PWideChar;
begin
  L := BufSize;
  if L > DestSize then
    L := DestSize;
  if L <= 1 then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  Dec(L, L mod Sizeof(WideChar));
  M := L div Sizeof(WideChar);
  P := Buf;
  Q := DestBuf;
  Move(P^, Q^, L);
  DestLength := M;
  ProcessedBytes := L;
end;

function TUTF16LECodec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var L : Integer;
begin
  if Length <= 0 then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  L := Length * 2;
  SetLength(Result, L);
  Move(S^, Pointer(Result)^, L);
  ProcessedChars := Length;
end;

class function TUTF16LECodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUTF16LE;
end;

class function TUTF16LECodec.GetPreamble: TBytes;
begin
  SetLength(Result, 2);
  Result[0] := $FF;
  Result[1] := $FE;
end;

procedure TUTF16LECodec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
var LowSurrogate : array[0..1] of Byte;  // We do not use Word, because the byte order of a Word is CPU dependant
begin
  C := 0;
  // C must be initialized, because the ReadBuffer(C, 2) call below does
  // not fill the whole variable!
  if not ReadBuffer(C, 2) then
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
      Exit;
    end;
  case C of  // UCS4Chars are stored in Little Endian mode; so we just can go on with it.
    $D800..$DBFF: // High surrogate of Unicode character [$10000..$10FFFF]
      begin
        if not ReadBuffer(LowSurrogate, 2) then
          raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-16LE']);
        case LowSurrogate[1] of
          $DC..$DF:
          begin
            C := ((C - $D7C0) shl 10) + ((LowSurrogate[1] xor $DC) shl 8) + LowSurrogate[0];
            ByteCount := 4;
          end;
        else
          raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-16LE']);
        end;
      end;
    $DC00..$DFFF: // Low surrogate of Unicode character [$10000..$10FFFF]
      raise EConvertError.CreateFmt(SInvalidEncoding, ['UTF-16LE']);
    else
      ByteCount := 2;
  end;
end;

procedure TUTF16LECodec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var Buffer : array[0..3] of Byte;
begin
  UCS4CharToUTF16LE(C, @Buffer, 4, ByteCount);
  WriteBuffer(Buffer[0], ByteCount);
end;

{ UCS4CharToUTF16LE transforms the UCS4Char Ch to UTF-16LE encoding. SeqSize   }
{ returns the number of bytes needed to transform Ch. Up to DestSize           }
{ bytes of the UTF-16LE encoding will be placed in Dest.                       }
procedure TUTF16LECodec.UCS4CharToUTF16LE(const Ch: UCS4Char; const Dest: Pointer;
  const DestSize: Integer; out SeqSize: Integer);
var P : PByte;
    HighSurrogate, LowSurrogate : Word;
begin
  P := Dest;
  case Ch of
  $00000000..$0000D7FF, $0000E000..$0000FFFF :
    begin
      SeqSize := 2;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      P^ := Lo(Ch);
      if DestSize <= 1 then
        Exit;
      Inc(P);
      P^ := Hi(Ch);
    end;
  $0000D800..$0000DFFF :
    raise EConvertError.CreateFmt(SInvalidCodePoint, [Ch, 'UCS-4']);
  $00010000..$0010FFFF:
    begin
      SeqSize := 4;
      if not Assigned(P) or (DestSize <= 0) then
        Exit;
      HighSurrogate := $D7C0 + (Ch shr 10);
      P^ := Lo(HighSurrogate);
      if DestSize <= 1 then
        Exit;
      Inc(P);
      P^ := Hi(HighSurrogate);
      if DestSize <= 2 then
        Exit;
      LowSurrogate := $DC00 xor (Ch and $3FF);
      Inc(P);
      P^ := Lo(LowSurrogate);
      if DestSize <= 3 then
        Exit;
      Inc(P);
      P^ := Hi(LowSurrogate);
    end;
  else // out of UTF-16 range
    raise EConvertError.CreateFmt(SCannotConvertUCS4, [Ch, 'UTF-16LE']);
  end;
end;

procedure TUTF16LECodec.WriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
const
  UTF16LE_LF   : array[0..1] of Byte = ($0A, $00);
  UTF16LE_CR   : array[0..1] of Byte = ($0D, $00);
  UTF16LE_CRLF : array[0..3] of Byte = ($0D, $00, $0A, $00);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UTF16LE_LF, 2);
          ByteCount := 2;
        end;
      lwCR:
        begin
          WriteBuffer(UTF16LE_CR, 2);
          ByteCount := 2;
        end;
      lwCRLF:
        begin
          WriteBuffer(UTF16LE_CRLF, 4);
          ByteCount := 4;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ TUCS4BECodec                                                                 }
{                                                                              }
procedure TUCS4BECodec.Decode(const Buf: Pointer; const BufSize: Integer;
  const DestBuf: Pointer; const DestSize: Integer;
  out ProcessedBytes, DestLength: Integer);
var Ch4     : UCS4Char;
    N, P, Q : PAnsiChar;
    L, C    : Integer;
begin
  P := Buf;
  Q := DestBuf;
  C := DestSize div 2;
  if not Assigned(P) or (BufSize <= 0) or not Assigned(Q) or (C <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  L := 0;
  N := P + BufSize - 4;
  while P <= N do
    begin
      Ch4 := Ord(P^) * $1000000 +
             Ord((P + 1)^) * $10000 +
             Ord((P + 2)^) * $100 +
             Ord((P + 3)^);
    if ((Ch4 >= $D800) and (Ch4 < $E000)) or (Ch4 > $10FFFF) then
      case FErrorAction of
        eaException :
          if Ch4 > $10FFFF then
            RaiseUnicodeCodecException(SCannotConvertUCS4, [Ch4, 'UTF-16'], P - Buf)
          else
            RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ch4, 'UCS-4'], P - Buf);
        eaStop :
          Break;
        eaSkip :
          Inc(P, 4);
        eaIgnore :
          begin
            if L + 1 >= C then
              Break;
            Q^ := (P + 1)^;     // Nevertheless change Big Endian to Little Endian ...
            (Q + 1)^ := P^;
            (Q + 2)^ := (P + 3)^;
            (Q + 3)^ := (P + 2)^;
            Inc(Q, 4);
            Inc(P, 4);
            Inc(L, 2);
          end;
        eaReplace :
          begin
            if L >= C then
              Break;
            Q^ := AnsiChar(Lo(Ord(FDecodeReplaceChar)));
            (Q + 1)^ := AnsiChar(Hi(Ord(FDecodeReplaceChar)));
            Inc(Q, 2);
            Inc(P, 4);
            Inc(L);
          end;
      end
    else
      begin
        if Ch4 > $FFFF then
          begin
            if L + 1 >= C then
              Break;
            Q^ := AnsiChar((Ord((P + 1)^) shl 6) + (Ord((P + 2)^) shr 2));
            (Q + 1)^ := AnsiChar($D8 + (Ord((P + 1)^) shr 2));
            (Q + 2)^ := (P + 3)^;
            (Q + 3)^ := AnsiChar($DC + (3 and Ord((P + 2)^)));
            Inc(Q, 4);
            Inc(P, 4);
            Inc(L, 2);
          end
        else
          begin
            if L >= C then
              Break;
            Q^ := (P + 3)^;
            (Q + 1)^ := (P + 2)^;
            Inc(Q, 2);
            Inc(P, 4);
            Inc(L);
          end;
      end;
  end;
  DestLength := L;
  ProcessedBytes := P - Buf;
end;

function TUCS4BECodec.Encode(const S: PWideChar; const Length: Integer;
  out ProcessedChars: Integer): AnsiString;
var P, N          : PWideChar;
    Q             : PAnsiChar;
    M             : Integer;
    HighSurrogate : Word;
    LowSurrogate  : Word;
begin
  P := S;
  if not Assigned(P) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  SetLength(Result, Length * 4);
  Q := Pointer(Result);
  M := 0;
  N := P + Length;
  while P < N do
    case Ord(P^) of
      $D800..$DBFF: // High surrogate of Unicode character [$10000..$10FFFF]
        begin
          if P = N - 1 then // End of WideString?
            raise EConvertError.Create(SLowSurrogateNotFound);
          HighSurrogate := Ord(P^);
          Inc(P);
          Inc(M, 2);
          LowSurrogate := Ord(P^);
          case LowSurrogate of  // Low Surrogate following?
            $DC00..$DF00:
              begin
                Q^ := AnsiChar(0);
                (Q+1)^ := AnsiChar((HighSurrogate - $D7C0) shr 6);
                (Q+2)^ := AnsiChar(((HighSurrogate and $3F) shl 2) + ((LowSurrogate - $DC00) shr 8));
                (Q+3)^ := AnsiChar(Lo(LowSurrogate));
                Inc(P);
                Inc(Q, 4);
                Inc(M, 4);
              end;
          else
            raise EConvertError.Create(SLowSurrogateNotFound);
          end;
        end;
      $DC00..$DFFF: // Low surrogate of Unicode character [$10000..$10FFFF]
        raise EConvertError.Create(SHighSurrogateNotFound);
    else
      Q^ := AnsiChar(0);
      (Q+1)^ := AnsiChar(0);
      (Q+2)^ := AnsiChar(Hi(Ord(P^)));
      (Q+3)^ := AnsiChar(Lo(Ord(P^)));
      Inc(P);
      Inc(Q, 4);
      Inc(M, 4);
    end;
  SetLength(Result, M);
  ProcessedChars := Length;
end;

class function TUCS4BECodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUCS4;
end;

class function TUCS4BECodec.GetPreamble: TBytes;
begin
  SetLength(Result, 4);
  Result[0] := $00;
  Result[1] := $00;
  Result[2] := $FE;
  Result[3] := $FF;
end;

procedure TUCS4BECodec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
var B : array[0..3] of Byte;
begin
  if ReadBuffer(B, 4) then
    begin
      C := B[0] * $1000000 + B[1] * $10000 + B[2] * $100 + B[3];
      case C of
        $D800..$DFFF: // Do not accept surrogates
          raise EConvertError.CreateFmt(SSurrogateNotAllowed, [C, 'UCS-4BE']);
      end;
      ByteCount := 4;
    end
  else
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
    end;
end;

procedure TUCS4BECodec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var Buffer : array[0..3] of Byte;
begin
  Buffer[0] := C shr 24;
  Buffer[1] := (C and $FF0000) shr 16;
  Buffer[2] := (C and $FF00) shr 8;
  Buffer[3] := C and $FF;
  WriteBuffer(Buffer, 4);
  ByteCount := 4;
end;

procedure TUCS4BECodec.WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer);
const
  UCS4BE_LF   : array[0..3] of Byte = ($0A, $00, $00, $00);
  UCS4BE_CR   : array[0..3] of Byte = ($0D, $00, $00, $00);
  UCS4BE_CRLF : array[0..7] of Byte = ($0D, $00, $00, $00, $0A, $00, $00, $00);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UCS4BE_LF, 4);
          ByteCount := 4;
        end;
      lwCR:
        begin
          WriteBuffer(UCS4BE_CR, 4);
          ByteCount := 4;
        end;
      lwCRLF:
        begin
          WriteBuffer(UCS4BE_CRLF, 8);
          ByteCount := 8;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ TUCS4LECodec                                                                 }
{                                                                              }
procedure TUCS4LECodec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);
var Ch4     : UCS4Char;
    N, P, Q : PAnsiChar;
    L, C    : Integer;
begin
  P := Buf;
  Q := DestBuf;
  C := DestSize div 2;
  if not Assigned(P) or (BufSize <= 0) or not Assigned(Q) or (C <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  L := 0;
  N := P + BufSize - 4;
  while P <= N do
    begin
      Ch4 := Ord((P + 3)^) * $1000000 +
             Ord((P + 2)^) * $10000 +
             Ord((P + 1)^) * $100 +
             Ord(P^);
      if ((Ch4 >= $D800) and (Ch4 < $E000)) or (Ch4 > $10FFFF) then
        case FErrorAction of
          eaException :
            if Ch4 > $10FFFF then
              RaiseUnicodeCodecException(SCannotConvertUCS4, [Ch4, 'UTF-16'], P - Buf)
            else
              RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ch4, 'UCS-4'], P - Buf);
          eaStop :
            Break;
          eaSkip :
            Inc(P, 4);
          eaIgnore :
            begin
              if L + 1 >= C then
                Break;
              Q^ := P^;
              (Q + 1)^ := (P + 1)^;
              (Q + 2)^ := (P + 2)^;
              (Q + 3)^ := (P + 3)^;
              Inc(Q, 4);
              Inc(P, 4);
              Inc(L, 2);
            end;
          eaReplace :
            begin
              if L >= C then
                Break;
              Q^ := AnsiChar(Lo(Ord(FDecodeReplaceChar)));
              (Q + 1)^ := AnsiChar(Hi(Ord(FDecodeReplaceChar)));
              Inc(Q, 2);
              Inc(P, 4);
              Inc(L);
            end;
        end
      else
        if Ch4 > $FFFF then
          begin
            if L + 1 >= C then
              Break;
            Q^ := AnsiChar((Ord((P + 2)^) shl 6) + (Ord((P + 1)^) shr 2));
            (Q + 1)^ := AnsiChar($D8 + (Ord((P + 2)^) shr 2));
            (Q + 2)^ := P^;
            (Q + 3)^ := AnsiChar($DC + (3 and Ord((P + 1)^)));
            Inc(Q, 4);
            Inc(P, 4);
            Inc(L, 2);
          end
        else
          begin
            if L >= C then
              Break;
            Q^ := P^;
            (Q + 1)^ := (P + 1)^;
            Inc(Q, 2);
            Inc(P, 4);
            Inc(L);
          end;
    end;
  DestLength := L;
  ProcessedBytes := P - Buf;
end;

function TUCS4LECodec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var P, N          : PWideChar;
    Q             : PAnsiChar;
    M             : Integer;
    HighSurrogate : Word;
    LowSurrogate  : Word;
begin
  P := S;
  if not Assigned(P) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  SetLength(Result, Length * 4);
  Q := Pointer(Result);
  M := 0;
  N := P + Length;
  while P < N do
    case Ord(P^) of
      $D800..$DBFF: // High surrogate of Unicode character [$10000..$10FFFF]
        begin
          if P = N - 1 then // End of WideString?
            raise EConvertError.Create(SLowSurrogateNotFound);
          HighSurrogate := Ord(P^);
          Inc(P);
          Inc(M, 2);
          LowSurrogate := Ord(P^);
          case LowSurrogate of  // Low Surrogate following?
            $DC00..$DF00:
              begin
                Q^ := AnsiChar(Lo(LowSurrogate));
                (Q + 1)^ := AnsiChar(((HighSurrogate and $3F) shl 2) + ((LowSurrogate - $DC00) shr 8));
                (Q + 2)^ := AnsiChar((HighSurrogate - $D7C0) shr 6);
                (Q + 3)^ := AnsiChar(0);
                Inc(P);
                Inc(Q, 4);
                Inc(M, 4);
              end;
          else
            raise EConvertError.Create(SLowSurrogateNotFound);
          end;
        end;
      $DC00..$DFFF: // Low surrogate of Unicode character [$10000..$10FFFF]
        raise EConvertError.Create(SHighSurrogateNotFound);
    else
      Q^ := AnsiChar(0);
      (Q + 1)^ := AnsiChar(0);
      (Q + 2)^ := AnsiChar(Hi(Ord(P^)));
      (Q + 3)^ := AnsiChar(Lo(Ord(P^)));
      Inc(P);
      Inc(Q, 4);
      Inc(M, 4);
    end;
  SetLength(Result, M);
  ProcessedChars := Length;
end;

class function TUCS4LECodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUCS4;
end;

class function TUCS4LECodec.GetPreamble: TBytes;
begin
  SetLength(Result, 4);
  Result[0] := $FF;
  Result[1] := $FE;
  Result[2] := $00;
  Result[3] := $00;
end;

procedure TUCS4LECodec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
var B : array[0..3] of Byte;
begin
  if ReadBuffer(B, 4) then
    begin
      C := B[3] * $1000000 + B[2] * $10000 + B[1] * $100 + B[0];
      case C of
        $D800..$DFFF: // Do not accept surrogates
          raise EConvertError.CreateFmt(SSurrogateNotAllowed, [C, 'UCS-4LE']);
      end;
      ByteCount := 4;
    end
  else
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
    end;
end;

procedure TUCS4LECodec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var Buffer : array[0..3] of Byte;
begin
  Buffer[0] := C and $FF;
  Buffer[1] := (C and $FF00) shr 8;
  Buffer[2] := (C and $FF0000) shr 16;
  Buffer[3] := C shr 24;
  WriteBuffer(Buffer, 4);
  ByteCount := 4;
end;

procedure TUCS4LECodec.WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer);
const
  UCS4LE_LF   : array[0..3] of Byte = ($00, $00, $00, $0A);
  UCS4LE_CR   : array[0..3] of Byte = ($00, $00, $00, $0D);
  UCS4LE_CRLF : array[0..7] of Byte = ($00, $00, $00, $0D, $00, $00, $00, $0A);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UCS4LE_LF, 4);
          ByteCount := 4;
        end;
      lwCR:
        begin
          WriteBuffer(UCS4LE_CR, 4);
          ByteCount := 4;
        end;
      lwCRLF:
        begin
          WriteBuffer(UCS4LE_CRLF, 8);
          ByteCount := 8;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ TUCS4_2143Codec                                                              }
{                                                                              }
procedure TUCS4_2143Codec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);
var Ch4     : UCS4Char;
    N, P, Q : PAnsiChar;
    L, C    : Integer;
begin
  P := Buf;
  Q := DestBuf;
  C := DestSize div 2;
  if not Assigned(P) or (BufSize <= 0) or not Assigned(Q) or (C <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  L := 0;
  N := P + BufSize - 4;
  while P <= N do
    begin
      Ch4 := Ord((P + 1)^) * $1000000 +
             Ord(P^) * $10000 +
             Ord((P + 3)^) * $100 +
             Ord((P + 2)^);
      if ((Ch4 >= $D800) and (Ch4 < $E000)) or (Ch4 > $10FFFF) then
        case FErrorAction of
          eaException :
            if Ch4 > $10FFFF
              then RaiseUnicodeCodecException(SCannotConvertUCS4, [Ch4, 'UTF-16'], P - Buf)
              else RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ch4, 'UCS-4'], P - Buf);
          eaStop :
            Break;
          eaSkip :
            Inc(P, 4);
          eaIgnore :
            begin
              if L + 1 >= C then
                Break;
              Q^ := P^;
              (Q + 1)^ := (P + 1)^;
              (Q + 2)^ := (P + 2)^;
              (Q + 3)^ := (P + 3)^;
              Inc(Q, 4);
              Inc(P, 4);
              Inc(L, 2);
            end;
          eaReplace :
            begin
              if L >= C then
                Break;
              Q^ := AnsiChar(Lo(Ord(FDecodeReplaceChar)));
              (Q + 1)^ := AnsiChar(Hi(Ord(FDecodeReplaceChar)));
              Inc(Q, 2);
              Inc(P, 4);
              Inc(L);
            end;
        end
      else
        if Ch4 > $FFFF then
          begin
            if L + 1 >= C then
              Break;
            Q^ := AnsiChar((Ord(P^) shl 6) + (Ord((P + 3)^) shr 2));
            (Q + 1)^ := AnsiChar($D8 + (Ord(P^) shr 2));
            (Q + 2)^ := (P + 2)^;
            (Q + 3)^ := AnsiChar($DC +  (3 and Ord((P + 3)^)));
            Inc(Q, 4);
            Inc(P, 4);
            Inc(L, 2);
          end
        else
          begin
            if L >= C then
              Break;
            Q^ := (P + 2)^;
            (Q + 1)^ := (P + 3)^;
            Inc(Q, 2);
            Inc(P, 4);
            Inc(L);
          end;
    end;
  DestLength := L;
  ProcessedBytes := P - Buf;
end;

function TUCS4_2143Codec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var P, N          : PWideChar;
    Q             : PAnsiChar;
    M             : Integer;
    HighSurrogate : Word;
    LowSurrogate  : Word;
begin
  P := S;
  if not Assigned(P) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  SetLength(Result, Length * 4);
  Q := Pointer(Result);
  M := 0;
  N := P + Length;
  while P < N do
    begin
      case Ord(P^) of
        $D800..$DBFF: // High surrogate of Unicode character [$10000..$10FFFF]
          begin
            if P = N - 1 then // End of WideString?
              raise EConvertError.Create(SLowSurrogateNotFound);
            HighSurrogate := Ord(P^);
            Inc(P);
            Inc(M, 2);
            LowSurrogate := Ord(P^);
            case LowSurrogate of  // Low Surrogate following?
              $DC00..$DF00:
                begin
                  Q^ := AnsiChar((HighSurrogate - $D7C0) shr 6);
                  (Q + 1)^ := AnsiChar(0);
                  (Q + 2)^ := AnsiChar(Lo(LowSurrogate));
                  (Q + 3)^ := AnsiChar(((HighSurrogate and $3F) shl 2) + ((LowSurrogate - $DC00) shr 8));
                  Inc(P);
                  Inc(Q, 4);
                  Inc(M, 4);
                end;
            else
              raise EConvertError.Create(SLowSurrogateNotFound);
            end;
          end;
        $DC00..$DFFF: // Low surrogate of Unicode character [$10000..$10FFFF]
          raise EConvertError.Create(SHighSurrogateNotFound);
      else
        Q^ := AnsiChar(0);
        (Q + 1)^ := AnsiChar(0);
        (Q + 2)^ := AnsiChar(Lo(Ord(P^)));
        (Q + 3)^ := AnsiChar(Hi(Ord(P^)));
        Inc(P);
        Inc(Q, 4);
        Inc(M, 4);
      end;
    end;
  SetLength(Result, M);
  ProcessedChars := Length;
end;

class function TUCS4_2143Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUCS4;
end;

class function TUCS4_2143Codec.GetPreamble: TBytes;
begin
  SetLength(Result, 4);
  Result[0] := $00;
  Result[1] := $00;
  Result[2] := $FF;
  Result[3] := $FE;
end;

procedure TUCS4_2143Codec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
var B : array[0..3] of Byte;
begin
  if ReadBuffer(B, 4) then
    begin
      C := B[1] * $1000000 + B[0] * $10000 + B[3] * $100 + B[2];
      case C of
        $D800..$DFFF: // Do not accept surrogates
          raise EConvertError.CreateFmt(SSurrogateNotAllowed, [C, 'UCS-4']);
      end;
    end
  else
    C := UCS4_STRING_TERMINATOR;
  ByteCount := 4;
end;

procedure TUCS4_2143Codec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var Buffer : array[0..3] of Byte;
begin
  Buffer[0] := (C and $FF0000) shr 16;
  Buffer[1] := C shr 24;
  Buffer[2] := C and $FF;
  Buffer[3] := (C and $FF00) shr 8;
  WriteBuffer(Buffer, 4);
  ByteCount := 4;
end;

procedure TUCS4_2143Codec.WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer);
const
  UCS4_2143_LF   : array[0..3] of Byte = ($00, $0A, $00, $00);
  UCS4_2143_CR   : array[0..3] of Byte = ($00, $0D, $00, $00);
  UCS4_2143_CRLF : array[0..7] of Byte = ($00, $0D, $00, $00, $00, $0A, $00, $00);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UCS4_2143_LF, 4);
          ByteCount := 4;
        end;
      lwCR:
        begin
          WriteBuffer(UCS4_2143_CR, 4);
          ByteCount := 4;
        end;
      lwCRLF:
        begin
          WriteBuffer(UCS4_2143_CRLF, 8);
          ByteCount := 8;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ TUCS4_3412Codec                                                              }
{                                                                              }
procedure TUCS4_3412Codec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);
var Ch4     : UCS4Char;
    N, P, Q : PAnsiChar;
    L, C    : Integer;
begin
  P := Buf;
  Q := DestBuf;
  C := DestSize div 2;
  if not Assigned(P) or (BufSize <= 0) or not Assigned(Q) or (C <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  L := 0;
  N := P + BufSize - 4;
  while P <= N do
    begin
      Ch4 := Ord((P + 2)^) * $1000000 +
             Ord((P + 3)^) * $10000 +
             Ord(P^) * $100 +
             Ord((P + 1)^);
      if ((Ch4 >= $D800) and (Ch4 < $E000)) or (Ch4 > $10FFFF) then
        case FErrorAction of
          eaException :
            if Ch4 > $10FFFF then
              RaiseUnicodeCodecException(SCannotConvertUCS4, [Ch4, 'UTF-16'], P - Buf)
            else
              RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ch4, 'UCS-4'], P - Buf);
          eaStop :
            Break;
          eaSkip :
            Inc(P, 4);
          eaIgnore :
            begin
              if L + 1 >= C then
                Break;
              Q^ := (P + 1)^;     // Nevertheless change Big Endian to Little Endian ...
              (Q + 1)^ := P^;
              (Q + 2)^ := (P + 3)^;
              (Q + 3)^ := (P + 2)^;
              Inc(Q, 4);
              Inc(P, 4);
              Inc(L, 2);
            end;
          eaReplace :
            begin
              if L >= C then
                Break;
              Q^ := AnsiChar(Lo(Ord(FDecodeReplaceChar)));
              (Q + 1)^ := AnsiChar(Hi(Ord(FDecodeReplaceChar)));
              Inc(Q, 2);
              Inc(P, 4);
              Inc(L);
            end;
        end
      else
        if Ch4 > $FFFF then
          begin
            if L + 1 >= C then
              Break;
            Q^ := AnsiChar((Ord((P + 3)^) shl 6) + (Ord(P^) shr 2));
            (Q + 1)^ := AnsiChar($D8 + (Ord((P + 3)^) shr 2));
            (Q + 2)^ := (P + 1)^;
            (Q + 3)^ := AnsiChar($DC +  (3 and Ord(P^)));
            Inc(Q, 4);
            Inc(P, 4);
            Inc(L, 2);
          end
        else
          begin
            if L >= C then
              Break;
            Q^ := (P + 1)^;
            (Q + 1)^ := P^;
            Inc(Q, 2);
            Inc(P, 4);
            Inc(L);
          end;
    end;
  DestLength := L;
  ProcessedBytes := P - Buf;
end;

function TUCS4_3412Codec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var P, N          : PWideChar;
    Q             : PAnsiChar;
    M             : Integer;
    HighSurrogate : Word;
    LowSurrogate  : Word;
begin
  P := S;
  if not Assigned(P) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  SetLength(Result, Length * 4);
  Q := Pointer(Result);
  M := 0;
  N := P + Length;
  while P < N do
    case Ord(P^) of
      $D800..$DBFF: // High surrogate of Unicode character [$10000..$10FFFF]
        begin
          if P = N - 1 then // End of WideString?
            raise EConvertError.Create(SLowSurrogateNotFound);
          HighSurrogate := Ord(P^);
          Inc(P);
          Inc(M, 2);
          LowSurrogate := Ord(P^);
          case LowSurrogate of  // Low Surrogate following?
            $DC00..$DF00:
              begin
                Q^ := AnsiChar(((HighSurrogate and $3F) shl 2) + ((LowSurrogate - $DC00) shr 8));
                (Q + 1)^ := AnsiChar(Lo(LowSurrogate));
                (Q + 2)^ := AnsiChar(0);
                (Q + 3)^ := AnsiChar((HighSurrogate - $D7C0) shr 6);
                Inc(P);
                Inc(Q, 4);
                Inc(M, 4);
              end;
          else
            raise EConvertError.Create(SLowSurrogateNotFound);
          end;
        end;
      $DC00..$DFFF: // Low surrogate of Unicode character [$10000..$10FFFF]
        raise EConvertError.Create(SHighSurrogateNotFound);
    else
      Q^ := AnsiChar(Hi(Ord(P^)));
      (Q + 1)^ := AnsiChar(Lo(Ord(P^)));
      (Q + 2)^ := AnsiChar(0);
      (Q + 3)^ := AnsiChar(0);
      Inc(P);
      Inc(Q, 4);
      Inc(M, 4);
    end;
  SetLength(Result, M);
  ProcessedChars := Length;
end;

class function TUCS4_3412Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUCS4;
end;

class function TUCS4_3412Codec.GetPreamble: TBytes;
begin
  SetLength(Result, 4);
  Result[0] := $FE;
  Result[1] := $FF;
  Result[2] := $00;
  Result[3] := $00;
end;

procedure TUCS4_3412Codec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
var B : array[0..3] of Byte;
begin
  if ReadBuffer(B, 4) then
    begin
      C := B[2] * $1000000 + B[3] * $10000 + B[0] * $100 + B[1];
      case C of
        $D800..$DFFF: // Do not accept surrogates
          raise EConvertError.CreateFmt(SSurrogateNotAllowed, [C, 'UCS-4']);
      end;
      ByteCount := 4;
    end
  else
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
    end;
end;

procedure TUCS4_3412Codec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var Buffer : array[0..3] of Byte;
begin
  Buffer[0] := (C and $FF00) shr 8;
  Buffer[1] := C and $FF;
  Buffer[2] := C shr 24;
  Buffer[3] := (C and $FF0000) shr 16;
  WriteBuffer(Buffer, 4);
  ByteCount := 4;
end;

procedure TUCS4_3412Codec.WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer);
const
  UCS4_3412_LF   : array[0..3] of Byte = ($00, $00, $0A, $00);
  UCS4_3412_CR   : array[0..3] of Byte = ($00, $00, $0D, $00);
  UCS4_3412_CRLF : array[0..7] of Byte = ($00, $00, $0D, $00, $00, $00, $0A, $00);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UCS4_3412_LF, 4);
          ByteCount := 4;
        end;
      lwCR:
        begin
          WriteBuffer(UCS4_3412_CR, 4);
          ByteCount := 4;
        end;
      lwCRLF:
        begin
          WriteBuffer(UCS4_3412_CRLF, 8);
          ByteCount := 8;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ TUCS2BECodec                                                                 }
{                                                                              }
procedure TUCS2BECodec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);
var
  Chr: WideChar;
  P, Q: PWideChar;
  I, L, C: Integer;
begin
  P := Buf;
  Q := DestBuf;
  C := DestSize div Sizeof(WideChar);
  if not Assigned(P) or (BufSize <= 0) or not Assigned(Q) or (C <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  L := 0;
  for I := 1 to BufSize do begin
    Chr := SwapUTF16Endian(P^);
    if (Ord(Chr) >= $D800) and (Ord(Chr) < $E000) then // Do not accept surrogates
      case FErrorAction of
        eaException :
          RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ord(Chr), 'UCS-2'], P - Buf);
        eaStop :
          Break;
        eaSkip :
          Inc(P);
        eaIgnore :
          begin
            Q^ := Chr;
            Inc(P);
            Inc(Q);
            Inc(L);
          end;
        eaReplace :
          begin
            Q^ := FDecodeReplaceChar;
            Inc(P);
            Inc(Q);
            Inc(L);
          end;
      end
    else
      begin
        if L >= C then
          Break;
        Q^ := Chr;
        Inc(P);
        Inc(Q);
        Inc(L);
      end;
  end; {for ...}
  DestLength := L;
  ProcessedBytes := P - Buf;
end;

function TUCS2BECodec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var P       : PWideChar;
    Q       : PWideChar;
    I, L, M : Integer;
begin
  Q := S;
  if not Assigned(Q) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  SetLength(Result, Length*2);
  L := 0;
  M := 0;
  P := Pointer(Result);
  for I := 1 to Length do
    if (Ord(Q^) >= $D800) and (Ord(Q^) < $E000) then // Do not accept surrogates
      case FErrorAction of
        eaException :
          RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ord(Q^), 'UCS-2'], L * 2);
        eaStop :
          Break;
        eaSkip :
          begin
            Inc(Q);
            Inc(L);
          end;
        eaIgnore :
          begin
            P^ := SwapUTF16Endian(Q^);
            Inc(P);
            Inc(Q);
            Inc(L);
            Inc(M);
          end;
        eaReplace :
          begin
            P^ := SwapUTF16Endian(FDecodeReplaceChar);
            Inc(P);
            Inc(Q);
            Inc(L);
            Inc(M);
          end;
      end
    else
      begin
        P^ := SwapUTF16Endian(Q^);
        Inc(P);
        Inc(Q);
        Inc(L);
        Inc(M);
      end;
  if Length <> M then
    SetLength(Result, M * 2);
  ProcessedChars := L;
end;

class function TUCS2BECodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUCS2;
end;

procedure TUCS2BECodec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
begin
  C := 0;
  // C must be initialized, because the ReadBuffer(C, 2) call below does
  // not fill the whole variable!
  if not ReadBuffer(C, 2) then
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
      Exit;
    end;
  C := Swap(C);  // UCS4Chars are stored in Little Endian mode; so we need to swap the bytes.
  ByteCount := 2;
  case C of
    $D800..$DFFF: // Do not accept surrogates
      raise EConvertError.CreateFmt(SSurrogateNotAllowed, [C, 'UCS-2']);
  end;
end;

procedure TUCS2BECodec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var HighByte, LowByte: Byte;
begin
  if C > $FFFF then
    raise EConvertError.CreateFmt(SEncodingOutOfRange, ['UCS-2']);
  HighByte := Hi(C);
  LowByte := Lo(C);
  WriteBuffer(HighByte, 1);
  WriteBuffer(LowByte, 1);
  ByteCount := 2;
end;

procedure TUCS2BECodec.WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer);
const
  UCS2BE_LF   : array[0..1] of Byte = ($00, $0A);
  UCS2BE_CR   : array[0..1] of Byte = ($00, $0D);
  UCS2BE_CRLF : array[0..3] of Byte = ($00, $0D, $00, $0A);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UCS2BE_LF, 2);
          ByteCount := 2;
        end;
      lwCR:
        begin
          WriteBuffer(UCS2BE_CR, 2);
          ByteCount := 2;
        end;
      lwCRLF:
        begin
          WriteBuffer(UCS2BE_CRLF, 4);
          ByteCount := 4;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ TUCS2LECodec                                                                 }
{                                                                              }
procedure TUCS2LECodec.Decode(const Buf: Pointer; const BufSize: Integer;
    const DestBuf: Pointer; const DestSize: Integer;
    out ProcessedBytes, DestLength: Integer);
var P       : PWideChar;
    Q       : PWideChar;
    I, L, C : Integer;
begin
  P := Buf;
  Q := DestBuf;
  C := DestSize div Sizeof(WideChar);
  if not Assigned(P) or (BufSize <= 0) or not Assigned(Q) or (C <= 0) then
    begin
      ProcessedBytes := 0;
      DestLength := 0;
      Exit;
    end;
  L := 0;
  for I := 1 to BufSize do
    if (Ord(P^) >= $D800) and (Ord(P^) < $E000) then // Do not accept surrogates
      case FErrorAction of
        eaException :
          RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ord(P^), 'UCS-2'], P - Buf);
        eaStop :
          Break;
        eaSkip :
          Inc(P);
        eaIgnore :
          begin
            Q^ := WideChar(P^);
            Inc(P);
            Inc(Q);
            Inc(L);
          end;
        eaReplace :
          begin
            Q^ := FDecodeReplaceChar;
            Inc(P);
            Inc(Q);
            Inc(L);
          end;
      end
    else
      begin
        if L >= C then
          Break;
        Q^ := P^;
        Inc(P);
        Inc(Q);
        Inc(L);
      end;
  DestLength := L;
  ProcessedBytes := P - Buf;
end;

function TUCS2LECodec.Encode(const S: PWideChar; const Length: Integer;
    out ProcessedChars: Integer): AnsiString;
var P       : PWideChar;
    Q       : PWideChar;
    I, L, M : Integer;
begin
  Q := S;
  if not Assigned(Q) or (Length <= 0) then
    begin
      ProcessedChars := 0;
      Result := '';
      Exit;
    end;
  SetLength(Result, Length*2);
  L := 0;
  M := 0;
  P := Pointer(Result);
  for I := 1 to Length do
    if (Ord(Q^) >= $D800) and (Ord(Q^) < $E000) then // Do not accept surrogates
      case FErrorAction of
        eaException :
          RaiseUnicodeCodecException(SSurrogateNotAllowed, [Ord(Q^), 'UCS-2'], L * 2);
        eaStop :
          Break;
        eaSkip :
          begin
            Inc(Q);
            Inc(L);
          end;
        eaIgnore :
          begin
            P^ := Q^;
            Inc(P);
            Inc(Q);
            Inc(L);
            Inc(M);
          end;
        eaReplace :
          begin
            P^ := FDecodeReplaceChar;
            Inc(P);
            Inc(Q);
            Inc(L);
            Inc(M);
          end;
      end
    else
      begin
        P^ := Q^;
        Inc(P);
        Inc(Q);
        Inc(L);
        Inc(M);
      end;
  if Length <> M then
    SetLength(Result, M * 2);
  ProcessedChars := L;
end;

class function TUCS2LECodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoUCS2;
end;

procedure TUCS2LECodec.InternalReadUCS4Char(out C: UCS4Char;
    out ByteCount: Integer);
begin
  C := 0;
  // C must be initialized, because the ReadBuffer(C, 2) call below does
  // not fill the whole variable!
  if not ReadBuffer(C, 2) then
    begin
      C := UCS4_STRING_TERMINATOR;
      ByteCount := 0;
      Exit;
    end;
  ByteCount := 2;
  case C of
    $D800..$DFFF: // Do not accept surrogates
      raise EConvertError.CreateFmt(SSurrogateNotAllowed, [C, 'UCS-2']);
  end;
end;

procedure TUCS2LECodec.InternalWriteUCS4Char(const C: UCS4Char;
    out ByteCount: Integer);
var HighByte, LowByte: Byte;
begin
  if C > $FFFF then
    raise EConvertError.CreateFmt(SEncodingOutOfRange, ['UCS-2']);
  HighByte := Hi(C);
  LowByte := Lo(C);
  WriteBuffer(LowByte, 1);
  WriteBuffer(HighByte, 1);
  ByteCount := 2;
end;

procedure TUCS2LECodec.WriteUCS4Char(const C: UCS4Char; out ByteCount: Integer);
const
  UCS2LE_LF   : array[0..1] of Byte = ($0A, $00);
  UCS2LE_CR   : array[0..1] of Byte = ($0D, $00);
  UCS2LE_CRLF : array[0..3] of Byte = ($0D, $00, $0A, $00);
begin
  if C = UCS4_LF then
    case WriteLFOption of
      lwLF:
        begin
          WriteBuffer(UCS2LE_LF, 2);
          ByteCount := 2;
        end;
      lwCR:
        begin
          WriteBuffer(UCS2LE_CR, 2);
          ByteCount := 2;
        end;
      lwCRLF:
        begin
          WriteBuffer(UCS2LE_CRLF, 4);
          ByteCount := 4;
        end;
    end
  else
    InternalWriteUCS4Char(C, ByteCount);
end;



{                                                                              }
{ ISO-8859-1 - Latin 1                                                         }
{                                                                              }
class function TISO8859_1Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoISOLatin1;
end;

function TISO8859_1Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := WideChar(P);
end;

function TISO8859_1Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  if Ord(Ch) >= $100 then
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-1']);
  Result := AnsiChar(Ch);
end;



{                                                                              }
{ ISO-8859-2 Latin 2                                                           }
{                                                                              }
class function TISO8859_2Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoISOLatin2;
end;

const
  ISO8859_2Map : AnsiCharISOMap = (
      #$00A0, #$0104, #$02D8, #$0141, #$00A4, #$013D, #$015A, #$00A7,
      #$00A8, #$0160, #$015E, #$0164, #$0179, #$00AD, #$017D, #$017B,
      #$00B0, #$0105, #$02DB, #$0142, #$00B4, #$013E, #$015B, #$02C7,
      #$00B8, #$0161, #$015F, #$0165, #$017A, #$02DD, #$017E, #$017C,
      #$0154, #$00C1, #$00C2, #$0102, #$00C4, #$0139, #$0106, #$00C7,
      #$010C, #$00C9, #$0118, #$00CB, #$011A, #$00CD, #$00CE, #$010E,
      #$0110, #$0143, #$0147, #$00D3, #$00D4, #$0150, #$00D6, #$00D7,
      #$0158, #$016E, #$00DA, #$0170, #$00DC, #$00DD, #$0162, #$00DF,
      #$0155, #$00E1, #$00E2, #$0103, #$00E4, #$013A, #$0107, #$00E7,
      #$010D, #$00E9, #$0119, #$00EB, #$011B, #$00ED, #$00EE, #$010F,
      #$0111, #$0144, #$0148, #$00F3, #$00F4, #$0151, #$00F6, #$00F7,
      #$0159, #$016F, #$00FA, #$0171, #$00FC, #$00FD, #$0163, #$02D9);

function TISO8859_2Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $A0 then
    Result := WideChar(P)
  else
    Result := ISO8859_2Map[Ord(P)];
end;

function TISO8859_2Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromISOMap(Ch, ISO8859_2Map, 'ISO-8859-2');
end;



{                                                                              }
{ ISO-8859-3 - Latin 3                                                         }
{                                                                              }
class function TISO8859_3Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoISOLatin3;
end;

const
  ISO8859_3Map : AnsiCharISOMap = (
      #$00A0, #$0126, #$02D8, #$00A3, #$00A4, #$FFFF, #$0124, #$00A7,
      #$00A8, #$0130, #$015E, #$011E, #$0134, #$00AD, #$FFFF, #$017B,
      #$00B0, #$0127, #$00B2, #$00B3, #$00B4, #$00B5, #$0125, #$00B7,
      #$00B8, #$0131, #$015F, #$011F, #$0135, #$00BD, #$FFFF, #$017C,
      #$00C0, #$00C1, #$00C2, #$FFFF, #$00C4, #$010A, #$0108, #$00C7,
      #$00C8, #$00C9, #$00CA, #$00CB, #$00CC, #$00CD, #$00CE, #$00CF,
      #$FFFF, #$00D1, #$00D2, #$00D3, #$00D4, #$0120, #$00D6, #$00D7,
      #$011C, #$00D9, #$00DA, #$00DB, #$00DC, #$016C, #$015C, #$00DF,
      #$00E0, #$00E1, #$00E2, #$FFFF, #$00E4, #$010B, #$0109, #$00E7,
      #$00E8, #$00E9, #$00EA, #$00EB, #$00EC, #$00ED, #$00EE, #$00EF,
      #$FFFF, #$00F1, #$00F2, #$00F3, #$00F4, #$0121, #$00F6, #$00F7,
      #$011D, #$00F9, #$00FA, #$00FB, #$00FC, #$016D, #$015D, #$02D9);

function TISO8859_3Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $A0 then
    Result := WideChar(P)
  else
    Result := ISO8859_3Map[Ord(P)];
end;

function TISO8859_3Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromISOMap(Ch, ISO8859_3Map, 'ISO-8859-3');
end;



{                                                                              }
{ ISO-8859-4 - Latin 4                                                         }
{                                                                              }
class function TISO8859_4Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoISOLatin4;
end;

const
  ISO8859_4Map : AnsiCharISOMap = (
      #$00A0, #$0104, #$0138, #$0156, #$00A4, #$0128, #$013B, #$00A7,
      #$00A8, #$0160, #$0112, #$0122, #$0166, #$00AD, #$017D, #$00AF,
      #$00B0, #$0105, #$02DB, #$0157, #$00B4, #$0129, #$013C, #$02C7,
      #$00B8, #$0161, #$0113, #$0123, #$0167, #$014A, #$017E, #$014B,
      #$0100, #$00C1, #$00C2, #$00C3, #$00C4, #$00C5, #$00C6, #$012E,
      #$010C, #$00C9, #$0118, #$00CB, #$0116, #$00CD, #$00CE, #$012A,
      #$0110, #$0145, #$014C, #$0136, #$00D4, #$00D5, #$00D6, #$00D7,
      #$00D8, #$0172, #$00DA, #$00DB, #$00DC, #$0168, #$016A, #$00DF,
      #$0101, #$00E1, #$00E2, #$00E3, #$00E4, #$00E5, #$00E6, #$012F,
      #$010D, #$00E9, #$0119, #$00EB, #$0117, #$00ED, #$00EE, #$012B,
      #$0111, #$0146, #$014D, #$0137, #$00F4, #$00F5, #$00F6, #$00F7,
      #$00F8, #$0173, #$00FA, #$00FB, #$00FC, #$0169, #$016B, #$02D9);

function TISO8859_4Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $A0 then
    Result := WideChar(P)
  else
    Result := ISO8859_4Map[Ord(P)];
end;

function TISO8859_4Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromISOMap(Ch, ISO8859_4Map, 'ISO-8859-4');
end;



{                                                                              }
{ ISO-8859-5 - Cyrillic                                                        }
{                                                                              }
class function TISO8859_5Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatinCyrillic;
end;

function TISO8859_5Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$A0, $AD : Result := WideChar(P);
    $F0 : Result := #$2116;
    $FD : Result := #$00A7;
  else
    Result := WideChar(Ord(P) + $0360);
  end;
end;

function TISO8859_5Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  if Ord(Ch) <= $A0 then
    Result := AnsiChar(Ch)
  else
    case Ch of
      #$2116 : Result := #$F0;
      #$00A7 : Result := #$FD;
      #$00AD : Result := #$AD;
      #$0401..#$045F :
        case Ch of
          #$0450, #$045D, #$040D :
            raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-5']);
        else
          Result := AnsiChar(Ord(Ch) - $0360);
        end;
    else
      raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-5']);
    end;
end;



{                                                                              }
{ ISO-8859-6 - Arabic                                                          }
{                                                                              }
class function TISO8859_6Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatinArabic;
end;

function TISO8859_6Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$A0, $A4, $AD                : Result := WideChar(P);
    $AC, $BB, $BF, $C1..$DA, $E0..$F2 : Result := WideChar(Ord(P) + $0580);
  else
    Result := #$FFFF;
  end;
end;

function TISO8859_6Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  if Ord(Ch) <= $A0 then
    Result := AnsiChar(Ch)
  else
    case Ch of
      #$00A4 : Result := #$A4;
      #$00AD : Result := #$AD;
      #$062C, #$063B, #$063F, #$0641..#$065A, #$0660..#$0672 :
        Result := AnsiChar(Ord(Ch) - $0580);
    else
      raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-6']);
    end;
end;



{                                                                              }
{ ISO-8859-7 - Modern Greek                                                    }
{                                                                              }
class function TISO8859_7Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatinGreek;
end;

function TISO8859_7Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$A0, $A6..$A9, $AB..$AD, $B0..$B3, $B7, $BB, $BD :
      Result := WideChar(P);
    $A1      : Result := #$2018;
    $A2      : Result := #$2019;
    $AF      : Result := #$2015;
    $D2, $FF : Result := #$FFFF;
  else
    Result := WideChar(Ord(P) + $02D0);
  end;
end;

function TISO8859_7Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  if Ord(Ch) <= $A0 then
    Result := AnsiChar(Ch)
  else
    case Ch of
      #$00A6..#$00A9, #$00AB..#$00AD, #$00B0..#$00B3, #$00B7, #$00BB, #$00BD :
        Result := AnsiChar(Ch);
      #$0373..#$03CE : Result := AnsiChar(Ord(Ch) - $02D0);
      #$2018         : Result := #$A1;
      #$2019         : Result := #$A2;
      #$2015         : Result := #$AF;
    else
      raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-7']);
    end;
end;



{                                                                              }
{ ISO-8859-8 - Hebrew                                                          }
{                                                                              }
class function TISO8859_8Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatinHebrew;
end;

function TISO8859_8Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$A0, $A2..$A9, $AB..$AE, $B0..$B9, $BB..$BE :
      Result := WideChar(P);
    $AA      : Result := #$00D7;
    $AF      : Result := #$203E;
    $BA      : Result := #$00F7;
    $DF      : Result := #$2017;
    $E0..$FA : Result := WideChar(Ord(P) + $04E0);
  else
    Result := #$FFFF;
  end;
end;

function TISO8859_8Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  if Ord(Ch) <= $A0 then
    Result := AnsiChar(Ch)
  else
    case Ch of
      #$00A2..#$00A9, #$00AB..#$00AE, #$00B0..#$00B9, #$00BB..#$00BE :
        Result := AnsiChar(Ord(Ch));
      #$00D7 : Result := #$AA;
      #$203E : Result := #$AF;
      #$00F7 : Result := #$BA;
      #$2017 : Result := #$DF;
      #$05C0..#$05DA : Result := AnsiChar(Ord(Ch) - $04E0);
    else
      raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-8']);
    end;
end;



{                                                                              }
{ ISO-8859-9 - Latin 5                                                         }
{                                                                              }
class function TISO8859_9Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatin5;
end;

function TISO8859_9Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $D0 : Result := #$011E;
    $DD : Result := #$0130;
    $DE : Result := #$015E;
    $F0 : Result := #$011F;
    $FD : Result := #$0131;
    $FE : Result := #$015F;
  else
    Result := WideChar(P);
  end;
end;

function TISO8859_9Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ch of
    #$011E : Result := #$D0;
    #$0130 : Result := #$DD;
    #$015E : Result := #$DE;
    #$011F : Result := #$F0;
    #$0131 : Result := #$FD;
    #$015F : Result := #$FE;
  else
    if Ord(Ch) <= $00FF then
      Result := AnsiChar(Ch)
    else
      raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-9']);
  end;
end;



{                                                                              }
{ ISO-8859-10 - Latin 6                                                        }
{                                                                              }
class function TISO8859_10Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatin6;
end;

const
  ISO8859_10Map : AnsiCharISOMap = (
      #$00A0, #$0104, #$0112, #$0122, #$012A, #$0128, #$0136, #$00A7,
      #$013B, #$0110, #$0160, #$0166, #$017D, #$00AD, #$016A, #$014A,
      #$00B0, #$0105, #$0113, #$0123, #$012B, #$0129, #$0137, #$00B7,
      #$013C, #$0111, #$0161, #$0167, #$017E, #$2014, #$016B, #$014B,
      #$0100, #$00C1, #$00C2, #$00C3, #$00C4, #$00C5, #$00C6, #$012E,
      #$010C, #$00C9, #$0118, #$00CB, #$0116, #$00CD, #$00CE, #$00CF,
      #$00D0, #$0145, #$014C, #$00D3, #$00D4, #$00D5, #$00D6, #$0168,
      #$00D8, #$0172, #$00DA, #$00DB, #$00DC, #$00DD, #$00DE, #$00DF,
      #$0101, #$00E1, #$00E2, #$00E3, #$00E4, #$00E5, #$00E6, #$012F,
      #$010D, #$00E9, #$0119, #$00EB, #$0117, #$00ED, #$00EE, #$00EF,
      #$00F0, #$0146, #$014D, #$00F3, #$00F4, #$00F5, #$00F6, #$0169,
      #$00F8, #$0173, #$00FA, #$00FB, #$00FC, #$00FD, #$00FE, #$0138);

function TISO8859_10Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $A0 then
    Result := WideChar(P)
  else
    Result := ISO8859_10Map[Ord(P)];
end;

function TISO8859_10Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromISOMap(Ch, ISO8859_10Map, 'ISO-8859-10');
end;



{                                                                              }
{ ISO-8859-13 - Latin 7                                                        }
{                                                                              }
class function TISO8859_13Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoISO885913;
end;

const
  ISO8859_13Map : AnsiCharISOMap = (
    #$00A0, #$201D, #$00A2, #$00A3, #$00A4, #$201E, #$00A6, #$00A7,
    #$00D8, #$00A9, #$0156, #$00AB, #$00AC, #$00AD, #$00AE, #$00C6,
    #$00B0, #$00B1, #$00B2, #$00B3, #$201C, #$00B5, #$00B6, #$00B7,
    #$00F8, #$00B9, #$0157, #$00BB, #$00BC, #$00BD, #$00BE, #$00E6,
    #$0104, #$012E, #$0100, #$0106, #$00C4, #$00C5, #$0118, #$0112,
    #$010C, #$00C9, #$0179, #$0116, #$0122, #$0136, #$012A, #$013B,
    #$0160, #$0143, #$0145, #$00D3, #$014C, #$00D5, #$00D6, #$00D7,
    #$0172, #$0141, #$015A, #$016A, #$00DC, #$017B, #$017D, #$00DF,
    #$0105, #$012F, #$0101, #$0107, #$00E4, #$00E5, #$0119, #$0113,
    #$010D, #$00E9, #$017A, #$0117, #$0123, #$0137, #$012B, #$013C,
    #$0161, #$0144, #$0146, #$00F3, #$014D, #$00F5, #$00F6, #$00F7,
    #$0173, #$0142, #$015B, #$016B, #$00FC, #$017B, #$017E, #$2019);

function TISO8859_13Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $A0 then
    Result := WideChar(P)
  else
    Result := ISO8859_13Map[Ord(P)];
end;

function TISO8859_13Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromISOMap(Ch, ISO8859_13Map, 'ISO-8859-13');
end;



{                                                                              }
{ ISO-8859-14 - Latin 8                                                        }
{                                                                              }
class function TISO8859_14Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatin8;
end;

const
  ISO8859_14Map : AnsiCharISOMap = (
    #$00A0, #$1E02, #$1E03, #$00A3, #$010A, #$010B, #$1E0A, #$00A7,
    #$1E80, #$00A9, #$1E82, #$1E0B, #$1EF2, #$00AD, #$00AE, #$0178,
    #$1E1E, #$1E1F, #$0120, #$0121, #$1E40, #$1E41, #$00B6, #$1E56,
    #$1E81, #$1E57, #$1E83, #$1E60, #$1EF3, #$1E84, #$1E85, #$1E61,
    #$00C0, #$00C1, #$00C2, #$00C3, #$00C4, #$00C5, #$00C6, #$00C7,
    #$00C8, #$00C9, #$00CA, #$00CB, #$00CC, #$00CD, #$00CE, #$00CF,
    #$0174, #$00D1, #$00D2, #$00D3, #$00D4, #$00D5, #$00D6, #$1E6A,
    #$00D8, #$00D9, #$00DA, #$00DB, #$00DC, #$00DD, #$0176, #$00DF,
    #$00E0, #$00E1, #$00E2, #$00E3, #$00E4, #$00E5, #$00E6, #$00E7,
    #$00E8, #$00E9, #$00EA, #$00EB, #$00EC, #$00ED, #$00EE, #$00EF,
    #$0175, #$00F1, #$00F2, #$00F3, #$00F4, #$00F5, #$00F6, #$1E6B,
    #$00F8, #$00F9, #$00FA, #$00FB, #$00FC, #$00FD, #$0177, #$00FF);

function TISO8859_14Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $A0 then
    Result := WideChar(P)
  else
    Result := ISO8859_14Map[Ord(P)];
end;

function TISO8859_14Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromISOMap(Ch, ISO8859_14Map, 'ISO-8859-14');
end;



{                                                                              }
{ ISO-8859-15 - Latin 9                                                        }
{                                                                              }
class function TISO8859_15Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIsoLatin9;
end;

function TISO8859_15Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $A4 : Result := #$20AC;
    $A6 : Result := #$00A6;
    $A8 : Result := #$0161;
    $B4 : Result := #$017D;
    $B8 : Result := #$017E;
    $BC : Result := #$0152;
    $BD : Result := #$0153;
    $BE : Result := #$0178;
  else
    Result := WideChar(P);
  end;
end;

function TISO8859_15Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ch of
    #$20AC : Result := #$A4;
    #$00A6 : Result := #$A6;
    #$0161 : Result := #$A8;
    #$017D : Result := #$B4;
    #$017E : Result := #$B8;
    #$0152 : Result := #$BC;
    #$0153 : Result := #$BD;
    #$0178 : Result := #$BE;
  else
    if Ord(Ch) <= $00FF then
      Result := AnsiChar(Ch)
    else
      raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'ISO-8859-15']);
  end;
end;



{                                                                              }
{ IBM037                                                                       }
{   Identical with Windows-37.                                                 }
{                                                                              }
class function TIBM037Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM037;
end;

const
  CP37Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$00A2, #$002E, #$003C, #$0028, #$002B, #$007C,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$0021, #$0024, #$002A, #$0029, #$003B, #$00AC,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4,
      #$00B5, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$005E, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$005B, #$005D, #$00AF, #$00A8, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM037Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := CP37Map[Ord(P)];
end;

function TIBM037Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, CP37Map, 'IBM037');
end;



{                                                                              }
{ IBM038                                                                       }
{                                                                              }
class function TIBM038Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM038;
end;

const
  IBM038Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$FFFF, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$005C, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$009F);

function TIBM038Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM038Map[Ord(P)];
end;

function TIBM038Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM038Map, 'IBM038');
end;



{                                                                              }
{ IBM256                                                                       }
{                                                                              }
const
  IBM256Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4,
      #$00B5, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$00A3, #$00A5, #$20A7, #$0192, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$203E, #$00A8, #$00B4, #$2017,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$005C, #$2003, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM256Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM256Map[Ord(P)];
end;

function TIBM256Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM256Map, 'IBM256');
end;



{                                                                              }
{ IBM273                                                                       }
{                                                                              }
class function TIBM273Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM273;
end;

const
  IBM273Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$007B, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$00C4, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$007E, #$00DC, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$005B, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00F6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$0023, #$00A7, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4,
      #$00B5, #$00DF, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$0040, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$203E, #$00A8, #$00B4, #$00D7,
      #$00E4, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00A6, #$00F2, #$00F3, #$00F5,
      #$00FC, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$007D, #$00F9, #$00FA, #$00FF,
      #$00D6, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$005C, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$005D, #$00D9, #$00DA, #$009F);

function TIBM273Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM273Map[Ord(P)];
end;

function TIBM273Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM273Map, 'IBM273');
end;



{                                                                              }
{ IBM274                                                                       }
{                                                                              }
class function TIBM274Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM274;
end;

const
  IBM274Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$00F9, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$0060, #$003A, #$0023, #$00E0, #$0027, #$003D, #$0022,
      #$FFFF, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$00A8, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$00E9, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$00E8, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$00E7, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$009F);

function TIBM274Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM274Map[Ord(P)];
end;

function TIBM274Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM274Map, 'IBM274');
end;



{                                                                              }
{ IBM275                                                                       }
{                                                                              }
class function TIBM275Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM275;
end;

const
  IBM275Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$00C9, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$0024, #$00C7, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$00E7, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$00E3, #$003A, #$00D5, #$00C3, #$0027, #$003D, #$0022,
      #$FFFF, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$00F5, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$00E9, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$005C, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$009F);

function TIBM275Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM275Map[Ord(P)];
end;

function TIBM275Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM275Map, 'IBM275');
end;



{                                                                              }
{ IBM277                                                                       }
{                                                                              }
class function TIBM277Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM277;
end;

const
  IBM277Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$007D,
      #$00E7, #$00F1, #$0023, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF, 
      #$00EC, #$00DF, #$00A4, #$00C5, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$0024, 
      #$00C7, #$00D1, #$00F8, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00A6, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$00C6, #$00D8, #$0027, #$003D, #$0022, 
      #$0040, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$007B, #$00B8, #$005B, #$005D,
      #$00B5, #$00FC, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$203E, #$00A8, #$00B4, #$00D7, 
      #$00E6, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5, 
      #$00E5, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$007E, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058, 
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM277Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM277Map[Ord(P)];
end;

function TIBM277Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM277Map, 'IBM277');
end;



{                                                                              }
{ IBM278                                                                       }
{                                                                              }
class function TIBM278Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM278;
end;

const
  IBM278Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F, 
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$007B, #$00E0, #$00E1, #$00E3, #$007D,
      #$00E7, #$00F1, #$00A7, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$0060, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$00A4, #$00C5, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$00C2, #$0023, #$00C0, #$00C1, #$00C3, #$0024, 
      #$00C7, #$00D1, #$00F6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$00E9, #$003A, #$00C4, #$00D6, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$005D, 
      #$00B5, #$00FC, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$005B, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$203E, #$00A8, #$00B4, #$00D7, 
      #$00E4, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047, 
      #$0048, #$0049, #$00AD, #$00F4, #$00A6, #$00F2, #$00F3, #$00F5, 
      #$00E5, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$007E, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058, 
      #$0059, #$005A, #$00B2, #$00D4, #$0040, #$00D2, #$00D3, #$00D5, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM278Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM278Map[Ord(P)];
end;

function TIBM278Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM278Map, 'IBM278');
end;



{                                                                              }
{ IBM280                                                                       }
{                                                                              }
class function TIBM280Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM280;
end;

const
  IBM280Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F, 
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004, 
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$00E4, #$007B, #$00E1, #$00E3, #$00E5, 
      #$005C, #$00F1, #$00B0, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$005D, #$00EA, #$00EB, #$007D, #$00ED, #$00EE, #$00EF, 
      #$007E, #$00DF, #$00E9, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$00D1, #$00F2, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$00F9, #$003A, #$00A3, #$00A7, #$0027, #$003D, #$0022, 
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$005B, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4,
      #$00B5, #$00EC, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$0023, #$00A5, #$00B7, #$00A9, #$0040, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$203E, #$00A8, #$00B4, #$00D7,
      #$00E0, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00A6, #$00F3, #$00F5,
      #$00E8, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$0060, #$00FA, #$00FF,
      #$00E7, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM280Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM280Map[Ord(P)];
end;

function TIBM280Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM280Map, 'IBM280');
end;



{                                                                              }
{ IBM281                                                                       }
{   Similar to IBM038.                                                         }
{                                                                              }
class function TIBM281Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM281;
end;

function TIBM281Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $4A : Result := #$00A3;
    $4F : Result := #$007C;
    $5A : Result := #$0021;
    $5B : Result := #$00A5;
    $5F : Result := #$00AC;
    $A1 : Result := #$203E;
    $E0 : Result := #$0024;
  else
    Result := IBM038Map[Ord(P)];
  end;
end;

function TIBM281Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $00A3 : Result := #$4A;
    $007C : Result := #$4F;
    $0021 : Result := #$5A;
    $00A5 : Result := #$5B;
    $00AC : Result := #$5F;
    $203E : Result := #$A1;
    $0024 : Result := #$E0;
  else
    Result := CharFromMap(Ch, IBM038Map, 'IBM281');
  end;
end;



{                                                                              }
{ IBM284                                                                       }
{                                                                              }
class function TIBM284Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM284;
end;

const
  IBM284Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00A6, #$005B, #$002E, #$003C, #$0028, #$002B, #$007C,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$005D, #$0024, #$002A, #$0029, #$003B, #$00AC,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$0023, #$00F1, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$00D1, #$0040, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4,
      #$00B5, #$00A8, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE, 
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$005E, #$0021, #$203E, #$007E, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047, 
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5, 
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM284Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM284Map[Ord(P)];
end;

function TIBM284Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM284Map, 'IBM284');
end;



{                                                                              }
{ IBM285                                                                       }
{                                                                              }
class function TIBM285Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM285;
end;

const
  IBM285Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$0024, #$002E, #$003C, #$0028, #$002B, #$007C,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$0021, #$00A3, #$002A, #$0029, #$003B, #$00AC,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022, 
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4, 
      #$00B5, #$203E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$005B, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$005E, #$005D, #$007E, #$00A8, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047, 
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058, 
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM285Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM285Map[Ord(P)];
end;

function TIBM285Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM285Map, 'IBM285');
end;



{                                                                              }
{ IBM290                                                                       }
{                                                                              }
class function TIBM290Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM290;
end;

const
  IBM290Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F, 
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$3002, #$300C, #$300D, #$3001, #$30FB, #$30F2, #$30A1, 
      #$30A3, #$30A5, #$00A3, #$002E, #$003C, #$0028, #$002B, #$007C,
      #$0026, #$30A7, #$30A9, #$30E3, #$30E5, #$30E7, #$30C3, #$FFFF, 
      #$30FC, #$FFFF, #$0021, #$00A5, #$002A, #$0029, #$003B, #$00AC,
      #$002D, #$002F, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, 
      #$FFFF, #$FFFF, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, 
      #$FFFF, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022, 
      #$FFFF, #$30A2, #$30A4, #$30A6, #$30A8, #$30AA, #$30AB, #$30AD,
      #$30AF, #$30B1, #$30B3, #$FFFF, #$30B5, #$30B7, #$30B9, #$30BB,
      #$30BD, #$30BF, #$30C1, #$30C4, #$30C6, #$30C8, #$30CA, #$30CB,
      #$30CC, #$30CD, #$30CE, #$FFFF, #$FFFF, #$30CF, #$30D2, #$30D5, 
      #$FFFF, #$203E, #$30D8, #$30DB, #$30DE, #$30DF, #$30E0, #$30E1,
      #$30E2, #$30E4, #$30E6, #$FFFF, #$30E8, #$30E9, #$30EA, #$30EB, 
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$30EC, #$30ED, #$30EF, #$30F3, #$309B, #$309C, 
      #$FFFF, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047, 
      #$0048, #$0049, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, 
      #$0024, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058, 
      #$0059, #$005A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$009F);

function TIBM290Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM290Map[Ord(P)];
end;

function TIBM290Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM290Map, 'IBM290');
end;



{                                                                              }
{ IBM297                                                                       }
{                                                                              }
class function TIBM297Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM297;
end;

const
  IBM297Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$0040, #$00E1, #$00E3, #$00E5,
      #$005C, #$00F1, #$00B0, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$007B, #$00EA, #$00EB, #$007D, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$00A7, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00F9, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$00B5, #$003A, #$00A3, #$00E0, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$005B, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4, 
      #$0060, #$00A8, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078, 
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$0023, #$00A5, #$00B7, #$00A9, #$005D, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$203E, #$007E, #$00B4, #$00D7, 
      #$00E9, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$00E8, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00A6, #$00FA, #$00FF, 
      #$00E7, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM297Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM297Map[Ord(P)];
end;

function TIBM297Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM297Map, 'IBM297');
end;



{                                                                              }
{ IBM420                                                                       }
{                                                                              }
class function TIBM420Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM420;
end;

const
  IBM420Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087, 
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F, 
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004, 
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$0651, #$FE7D, #$0640, #$FFFF, #$0621, #$0622, 
      #$FE82, #$0623, #$00A2, #$002E, #$003C, #$0028, #$002B, #$007C, 
      #$0026, #$FE84, #$0624, #$FFFF, #$FFFF, #$0626, #$0627, #$FE8E,
      #$0628, #$FE91, #$0021, #$0024, #$002A, #$0029, #$003B, #$00AC, 
      #$002D, #$002F, #$0629, #$062A, #$FE97, #$062B, #$FE9B, #$062C, 
      #$FE9F, #$062D, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$FEA3, #$062E, #$FEA7, #$062F, #$0630, #$0631, #$0632, #$0633, 
      #$FEB3, #$060C, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$0634, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$FEB7, #$0635, #$FEBB, #$0636, #$FEBF, #$0637, 
      #$0638, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$0639, #$FECA, #$FECB, #$FECC, #$063A, #$FECE, 
      #$FECF, #$00F7, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$FED0, #$0641, #$FED3, #$0642, #$FED7, #$0643, 
      #$FEDB, #$0644, #$FEF5, #$FEF6, #$FEF7, #$FEF8, #$FFFF, #$FFFF,
      #$FEFB, #$FEFC, #$FEDF, #$0645, #$FEE3, #$0646, #$FEE7, #$0647, 
      #$061B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$FEEB, #$FFFF, #$FEEC, #$FFFF, #$0648,
      #$061F, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$0649, #$FEF0, #$064A, #$FEF2, #$FEF3, #$0660,
      #$00D7, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$0661, #$0662, #$FFFF, #$0663, #$0664, #$0665,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$FFFF, #$0666, #$0667, #$0668, #$0669, #$009F);

function TIBM420Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM420Map[Ord(P)];
end;

function TIBM420Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM420Map, 'IBM420');
end;



{                                                                              }
{ IBM423                                                                       }
{                                                                              }
class function TIBM423Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM423;
end;

const
  IBM423Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F, 
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F, 
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087, 
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B, 
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004, 
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$0391, #$0392, #$0393, #$0394, #$0395, #$0396, #$0397, 
      #$0398, #$0399, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$039A, #$039B, #$039C, #$039D, #$039E, #$039F, #$03A0, 
      #$03A1, #$03A3, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$03A4, #$03A5, #$03A6, #$03A7, #$03A8, #$03A9,
      #$FFFF, #$FFFF, #$FFFF, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$FFFF, #$0386, #$0388, #$0389, #$FFFF, #$038A, #$038C, #$038E, 
      #$038F, #$0060, #$003A, #$00A3, #$00A7, #$0027, #$003D, #$0022,
      #$00C4, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$03B1, #$03B2, #$03B3, #$03B4, #$03B5, #$03B6, 
      #$00D6, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$03B7, #$03B8, #$03B9, #$03BA, #$03BB, #$03BC,
      #$00DC, #$00A8, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$03BD, #$03BE, #$03BF, #$03C0, #$03C1, #$03C2,
      #$FFFF, #$03AC, #$03AD, #$03AE, #$03CA, #$03AF, #$03CC, #$03CD,
      #$03CB, #$03CE, #$03C3, #$03C4, #$03C5, #$03C6, #$03C7, #$03C8, 
      #$00B8, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$FFFF, #$03C9, #$00C2, #$00E0, #$00E4, #$00EA, 
      #$00B4, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B1, #$00E9, #$00E8, #$00EB, #$00EE, #$00EF,
      #$00B0, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00BD, #$00F6, #$00F4, #$00FB, #$00F9, #$00FC, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00FF, #$00E7, #$00C7, #$FFFF, #$FFFF, #$009F);

function TIBM423Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM423Map[Ord(P)];
end;

function TIBM423Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM423Map, 'IBM423');
end;



{                                                                              }
{ IBM424                                                                       }
{                                                                              }
class function TIBM424Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM424;
end;

const
  IBM424Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$05D0, #$05D1, #$05D2, #$05D3, #$05D4, #$05D5, #$05D6,
      #$05D7, #$05D8, #$00A2, #$002E, #$003C, #$0028, #$002B, #$007C,
      #$0026, #$05D9, #$05DA, #$05DB, #$05DC, #$05DD, #$05DE, #$05DF,
      #$05E0, #$05E1, #$0021, #$0024, #$002A, #$0029, #$003B, #$00AC,
      #$002D, #$002F, #$05E2, #$05E3, #$05E4, #$05E5, #$05E6, #$05E7,
      #$05E8, #$05E9, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$FFFF, #$05EA, #$FFFF, #$FFFF, #$00A0, #$FFFF, #$FFFF, #$FFFF,
      #$21D4, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$FFFF, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$FFFF, #$FFFF, #$FFFF, #$00B8, #$FFFF, #$00A4,
      #$00B5, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$00AE,
      #$005E, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$005B, #$005D, #$203E, #$00A8, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047, 
      #$0048, #$0049, #$00AD, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$009F);

function TIBM424Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM424Map[Ord(P)];
end;

function TIBM424Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM424Map, 'IBM424');
end;



{                                                                              }
{ IBM437                                                                       }
{   Identical with Windows-437.                                                }
{                                                                              }
class function TIBM437Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoPC8CodePage437;
end;

const
  CP437Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E4, #$00E0, #$00E5, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00EF, #$00EE, #$00EC, #$00C4, #$00C5,
      #$00C9, #$00E6, #$00C6, #$00F4, #$00F6, #$00F2, #$00FB, #$00F9,
      #$00FF, #$00D6, #$00DC, #$00A2, #$00A3, #$00A5, #$20A7, #$0192,
      #$00E1, #$00ED, #$00F3, #$00FA, #$00F1, #$00D1, #$00AA, #$00BA,
      #$00BF, #$2310, #$00AC, #$00BD, #$00BC, #$00A1, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567,
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B,
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$03B1, #$00DF, #$0393, #$03C0, #$03A3, #$03C3, #$00B5, #$03C4,
      #$03A6, #$0398, #$03A9, #$03B4, #$221E, #$03C6, #$03B5, #$2229,
      #$2261, #$00B1, #$2265, #$2264, #$2320, #$2321, #$00F7, #$2248,
      #$00B0, #$2219, #$00B7, #$221A, #$207F, #$00B2, #$25A0, #$00A0);

function TIBM437Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP437Map[Ord(P)];
end;

function TIBM437Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP437Map, 'IBM437');
end;



{                                                                              }
{ IBM500                                                                       }
{   Identical with Windows-500.                                                }
{                                                                              }
class function TIBM500Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM500;
end;

const
  CP500Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4,
      #$00B5, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM500Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := CP500Map[Ord(P)];
end;

function TIBM500Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, CP500Map, 'IBM500');
end;



{                                                                              }
{ Windows-708                                                                  }
{                                                                              }
const
  Windows708Map : AnsiCharHighMap = (
      #$2502, #$2524, #$00E9, #$00E2, #$2561, #$00E0, #$2562, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00EF, #$00EE, #$2556, #$2555, #$2563,
      #$2551, #$2557, #$255D, #$00F4, #$255C, #$255B, #$00FB, #$00F9,
      #$2510, #$2514, #$009A, #$009B, #$009C, #$009D, #$009E, #$009F,
      #$F8C1, #$2534, #$252C, #$251C, #$00A4, #$2500, #$253C, #$255E,
      #$255F, #$255A, #$2554, #$2569, #$060C, #$2566, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2560, #$2550, #$256C, #$2567, #$2568,
      #$2564, #$2565, #$2559, #$061B, #$2558, #$2552, #$2553, #$061F,
      #$256B, #$0621, #$0622, #$0623, #$0624, #$0625, #$0626, #$0627,
      #$0628, #$0629, #$062A, #$062B, #$062C, #$062D, #$062E, #$062F,
      #$0630, #$0631, #$0632, #$0633, #$0634, #$0635, #$0636, #$0637,
      #$0638, #$0639, #$063A, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$0640, #$0641, #$0642, #$0643, #$0644, #$0645, #$0646, #$0647,
      #$0648, #$0649, #$064A, #$064B, #$064C, #$064D, #$064E, #$064F,
      #$0650, #$0651, #$0652, #$F8C2, #$F8C3, #$F8C4, #$F8C5, #$F8C6,
      #$F8C7, #$256A, #$2518, #$250C, #$00B5, #$00A3, #$25A0, #$00A0);

function TWindows708Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows708Map[Ord(P)];
end;

function TWindows708Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows708Map, 'Windows-708');
end;



class function TWindows708Codec.Name: string;
begin
  Result := 'windows-708';
end;

{                                                                              }
{ Windows-737                                                                  }
{                                                                              }
const
  Windows737Map : AnsiCharHighMap = (
      #$0391, #$0392, #$0393, #$0394, #$0395, #$0396, #$0397, #$0398,
      #$0399, #$039A, #$039B, #$039C, #$039D, #$039E, #$039F, #$03A0,
      #$03A1, #$03A3, #$03A4, #$03A5, #$03A6, #$03A7, #$03A8, #$03A9,
      #$03B1, #$03B2, #$03B3, #$03B4, #$03B5, #$03B6, #$03B7, #$03B8,
      #$03B9, #$03BA, #$03BB, #$03BC, #$03BD, #$03BE, #$03BF, #$03C0,
      #$03C1, #$03C3, #$03C2, #$03C4, #$03C5, #$03C6, #$03C7, #$03C8,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567,
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B,
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$03C9, #$03AC, #$03AD, #$03AE, #$03CA, #$03AF, #$03CC, #$03CD,
      #$03CB, #$03CE, #$0386, #$0388, #$0389, #$038A, #$038C, #$038E,
      #$038F, #$00B1, #$2265, #$2264, #$03AA, #$03AB, #$00F7, #$2248,
      #$00B0, #$2219, #$00B7, #$221A, #$207F, #$00B2, #$25A0, #$00A0);

function TWindows737Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows737Map[Ord(P)];
end;

function TWindows737Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows737Map, 'Windows-737');
end;

class function TWindows737Codec.Name: string;
begin
  Result := 'windows-737';
end;



{                                                                              }
{ Windows-775                                                                  }
{                                                                              }
const
  Windows775Map : AnsiCharHighMap = (
      #$0106, #$00FC, #$00E9, #$0101, #$00E4, #$0123, #$00E5, #$0107,
      #$0142, #$0113, #$0156, #$0157, #$012B, #$0179, #$00C4, #$00C5,
      #$00C9, #$00E6, #$00C6, #$014D, #$00F6, #$0122, #$00A2, #$015A,
      #$015B, #$00D6, #$00DC, #$00F8, #$00A3, #$00D8, #$00D7, #$00A4,
      #$0100, #$012A, #$00F3, #$017B, #$017C, #$017A, #$201D, #$00A6,
      #$00A9, #$00AE, #$00AC, #$00BD, #$00BC, #$0141, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$0104, #$010C, #$0118,
      #$0116, #$2563, #$2551, #$2557, #$255D, #$012E, #$0160, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$0172, #$016A,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$017D,
      #$0105, #$010D, #$0119, #$0117, #$012F, #$0161, #$0173, #$016B,
      #$017E, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$00D3, #$00DF, #$014C, #$0143, #$00F5, #$00D5, #$00B5, #$0144,
      #$0136, #$0137, #$013B, #$013C, #$0146, #$0112, #$0145, #$2019,
      #$00AD, #$00B1, #$201C, #$00BE, #$00B6, #$00A7, #$00F7, #$201E,
      #$00B0, #$2219, #$00B7, #$00B9, #$00B3, #$00B2, #$25A0, #$00A0);

function TWindows775Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows775Map[Ord(P)];
end;

function TWindows775Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows775Map, 'Windows-775');
end;

class function TWindows775Codec.Name: string;
begin
  Result := 'windows-775';
end;



{                                                                              }
{ IBM850                                                                       }
{   Identical with Windows-850.                                                }
{                                                                              }
class function TIBM850Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoPC850Multilingual;
end;

const
  CP850Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E4, #$00E0, #$00E5, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00EF, #$00EE, #$00EC, #$00C4, #$00C5,
      #$00C9, #$00E6, #$00C6, #$00F4, #$00F6, #$00F2, #$00FB, #$00F9,
      #$00FF, #$00D6, #$00DC, #$00F8, #$00A3, #$00D8, #$00D7, #$0192,
      #$00E1, #$00ED, #$00F3, #$00FA, #$00F1, #$00D1, #$00AA, #$00BA,
      #$00BF, #$00AE, #$00AC, #$00BD, #$00BC, #$00A1, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$00C1, #$00C2, #$00C0,
      #$00A9, #$2563, #$2551, #$2557, #$255D, #$00A2, #$00A5, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$00E3, #$00C3,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$00A4,
      #$00F0, #$00D0, #$00CA, #$00CB, #$00C8, #$0131, #$00CD, #$00CE,
      #$00CF, #$2518, #$250C, #$2588, #$2584, #$00A6, #$00CC, #$2580,
      #$00D3, #$00DF, #$00D4, #$00D2, #$00F5, #$00D5, #$00B5, #$00FE,
      #$00DE, #$00DA, #$00DB, #$00D9, #$00FD, #$00DD, #$00AF, #$00B4,
      #$00AD, #$00B1, #$2017, #$00BE, #$00B6, #$00A7, #$00F7, #$00B8,
      #$00B0, #$00A8, #$00B7, #$00B9, #$00B3, #$00B2, #$25A0, #$00A0);

function TIBM850Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP850Map[Ord(P)];
end;

function TIBM850Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP850Map, 'IBM850');
end;



{                                                                              }
{ IBM851                                                                       }
{                                                                              }
class function TIBM851Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM851;
end;

const
  IBM851Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E4, #$00E0, #$0386, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00EF, #$00EE, #$0388, #$00C4, #$0389,
      #$038A, #$FFFF, #$038C, #$00F4, #$00F6, #$038E, #$00FB, #$00F9,
      #$038F, #$00D6, #$00DC, #$03AC, #$00A3, #$03AD, #$03AE, #$03AF,
      #$03CA, #$0390, #$03CC, #$03CD, #$0391, #$0392, #$0393, #$0394,
      #$0395, #$0396, #$0397, #$00BD, #$0398, #$0399, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$039A, #$039B, #$039D,
      #$039C, #$2563, #$2551, #$2557, #$255D, #$039E, #$039F, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$03A0, #$03A1,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$03A3,
      #$03A4, #$03A5, #$03A6, #$03A7, #$03A8, #$03A9, #$03B1, #$03B2,
      #$03B3, #$2518, #$250C, #$2588, #$2584, #$03B4, #$03B5, #$2580,
      #$03B6, #$03B7, #$03B8, #$03B9, #$03BA, #$03BB, #$03BC, #$03BD,
      #$03BE, #$03BF, #$03C0, #$03C1, #$03C3, #$03C2, #$03C4, #$00B4,
      #$00AD, #$00B1, #$03C5, #$03C6, #$03C7, #$00A7, #$03C8, #$02DB,
      #$00B0, #$00A8, #$03C9, #$03CB, #$03B0, #$03CE, #$25A0, #$00A0);

function TIBM851Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := IBM851Map[Ord(P)];
end;

function TIBM851Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, IBM851Map, 'IBM851');
end;



{                                                                              }
{ IBM852                                                                       }
{   Identical with Windows-852.                                                }
{                                                                              }
class function TIBM852Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoPCp852;
end;

const
  CP852Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E4, #$016F, #$0107, #$00E7,
      #$0142, #$00EB, #$0150, #$0151, #$00EE, #$0179, #$00C4, #$0106,
      #$00C9, #$0139, #$013A, #$00F4, #$00F6, #$013D, #$013E, #$015A,
      #$015B, #$00D6, #$00DC, #$0164, #$0165, #$0141, #$00D7, #$010D,
      #$00E1, #$00ED, #$00F3, #$00FA, #$0104, #$0105, #$017D, #$017E,
      #$0118, #$0119, #$00AC, #$017A, #$010C, #$015F, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$00C1, #$00C2, #$011A,
      #$015E, #$2563, #$2551, #$2557, #$255D, #$017B, #$017C, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$0102, #$0103,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$00A4,
      #$0111, #$0110, #$010E, #$00CB, #$010F, #$0147, #$00CD, #$00CE,
      #$011B, #$2518, #$250C, #$2588, #$2584, #$0162, #$016E, #$2580,
      #$00D3, #$00DF, #$00D4, #$0143, #$0144, #$0148, #$0160, #$0161,
      #$0154, #$00DA, #$0155, #$0170, #$00FD, #$00DD, #$0163, #$00B4,
      #$00AD, #$02DD, #$02DB, #$02C7, #$02D8, #$00A7, #$00F7, #$00B8,
      #$00B0, #$00A8, #$02D9, #$0171, #$0158, #$0159, #$25A0, #$00A0);

function TIBM852Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP852Map[Ord(P)];
end;

function TIBM852Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP852Map, 'IBM852');
end;



{                                                                              }
{ IBM855                                                                       }
{   Identical with Windows-855.                                                }
{                                                                              }
class function TIBM855Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM855;
end;

const
  CP855Map : AnsiCharHighMap = (
      #$0452, #$0402, #$0453, #$0403, #$0451, #$0401, #$0454, #$0404,
      #$0455, #$0405, #$0456, #$0406, #$0457, #$0407, #$0458, #$0408,
      #$0459, #$0409, #$045A, #$040A, #$045B, #$040B, #$045C, #$040C,
      #$045E, #$040E, #$045F, #$040F, #$044E, #$042E, #$044A, #$042A,
      #$0430, #$0410, #$0431, #$0411, #$0446, #$0426, #$0434, #$0414,
      #$0435, #$0415, #$0444, #$0424, #$0433, #$0413, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$0445, #$0425, #$0438,
      #$0418, #$2563, #$2551, #$2557, #$255D, #$0439, #$0419, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$043A, #$041A,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$00A4,
      #$043B, #$041B, #$043C, #$041C, #$043D, #$041D, #$043E, #$041E,
      #$043F, #$2518, #$250C, #$2588, #$2584, #$041F, #$044F, #$2580,
      #$042F, #$0440, #$0420, #$0441, #$0421, #$0442, #$0422, #$0443,
      #$0423, #$0436, #$0416, #$0432, #$0412, #$044C, #$042C, #$2116,
      #$00AD, #$044B, #$042B, #$0437, #$0417, #$0448, #$0428, #$044D,
      #$042D, #$0449, #$0429, #$0447, #$0427, #$00A7, #$25A0, #$00A0);

function TIBM855Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP855Map[Ord(P)];
end;

function TIBM855Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP855Map, 'IBM855');
end;



{                                                                              }
{ IBM857                                                                       }
{   Identical with Windows-857.                                                }
{                                                                              }
class function TIBM857Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM857;
end;

const
  CP857Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E4, #$00E0, #$00E5, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00EF, #$00EE, #$0131, #$00C4, #$00C5,
      #$00C9, #$00E6, #$00C6, #$00F4, #$00F6, #$00F2, #$00FB, #$00F9,
      #$0130, #$00D6, #$00DC, #$00F8, #$00A3, #$00D8, #$015E, #$015F,
      #$00E1, #$00ED, #$00F3, #$00FA, #$00F1, #$00D1, #$011E, #$011F,
      #$00BF, #$00AE, #$00AC, #$00BD, #$00BC, #$00A1, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$00C1, #$00C2, #$00C0,
      #$00A9, #$2563, #$2551, #$2557, #$255D, #$00A2, #$00A5, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$00E3, #$00C3,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$00A4,
      #$00BA, #$00AA, #$00CA, #$00CB, #$00C8, #$F8BB, #$00CD, #$00CE,
      #$00CF, #$2518, #$250C, #$2588, #$2584, #$00A6, #$00CC, #$2580,
      #$00D3, #$00DF, #$00D4, #$00D2, #$00F5, #$00D5, #$00B5, #$F8BC,
      #$00D7, #$00DA, #$00DB, #$00D9, #$00EC, #$00FF, #$00AF, #$00B4,
      #$00AD, #$00B1, #$F8BD, #$00BE, #$00B6, #$00A7, #$00F7, #$00B8,
      #$00B0, #$00A8, #$00B7, #$00B9, #$00B3, #$00B2, #$25A0, #$00A0);

function TIBM857Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$7F      : Result := WideChar(P);
    $D5, $E7, $F2 : Result := #$FFFF;
  else
    Result := CP857Map[Ord(P)];
  end;
end;

function TIBM857Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP857Map, 'IBM857');
  if Ord(Result) in [$D5, $E7, $F2] then
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'IBM857']);
end;



{                                                                              }
{ Windows-858                                                                  }
{   Similar to CP850.                                                          }
{                                                                              }
function TWindows858Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$7F : Result := WideChar(P);
    $D5      : Result := #$20AC;
  else
    Result := CP850Map[Ord(P)];
  end;
end;

function TWindows858Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $20AC : Result := #$D5;
  else
    Result := CharFromHighMap(Ch, CP850Map, 'Windows-858');
  end;
end;

class function TWindows858Codec.Name: string;
begin
  Result := 'windows-858';
end;



{                                                                              }
{ IBM860                                                                       }
{                                                                              }
class function TIBM860Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM860;
end;

const
  IBM860Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E3, #$00E0, #$00C1, #$00E7,
      #$00EA, #$00CA, #$00E8, #$00CD, #$00D4, #$00EC, #$00C3, #$00C2,
      #$00C9, #$00C0, #$00C8, #$00F4, #$00F5, #$00F2, #$00DA, #$00F9,
      #$00CC, #$00D5, #$00DC, #$00A2, #$00A3, #$00D9, #$20A7, #$00D3,
      #$00E1, #$00ED, #$00F3, #$00FA, #$00F1, #$00D1, #$00AA, #$00BA,
      #$00BF, #$00D2, #$00AC, #$00BD, #$00BC, #$00A1, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F, 
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567, 
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B, 
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$03B1, #$00DF, #$0393, #$03C0, #$03A3, #$03C3, #$00B5, #$03C4,
      #$03A6, #$0398, #$03A9, #$03B4, #$221E, #$03C6, #$03B5, #$2229, 
      #$2261, #$00B1, #$2265, #$2264, #$2320, #$2321, #$00F7, #$2248,
      #$00B0, #$2219, #$00B7, #$221A, #$207F, #$00B2, #$25A0, #$00A0);

function TIBM860Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := IBM860Map[Ord(P)];
end;

function TIBM860Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, IBM860Map, 'IBM860');
end;



{                                                                              }
{ IBM861                                                                       }
{   Identical with Windows-861.                                                }
{                                                                              }
class function TIBM861Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM861;
end;

const
  CP861Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E4, #$00E0, #$00E5, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00D0, #$00F0, #$00DE, #$00C4, #$00C5,
      #$00C9, #$00E6, #$00C6, #$00F4, #$00F6, #$00FE, #$00FB, #$00DD,
      #$00FD, #$00D6, #$00DC, #$00F8, #$00A3, #$00D8, #$20A7, #$0192,
      #$00E1, #$00ED, #$00F3, #$00FA, #$00C1, #$00CD, #$00D3, #$00DA,
      #$00BF, #$2310, #$00AC, #$00BD, #$00BC, #$00A1, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567,
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B,
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$03B1, #$00DF, #$0393, #$03C0, #$03A3, #$03C3, #$00B5, #$03C4,
      #$03A6, #$0398, #$03A9, #$03B4, #$221E, #$03C6, #$03B5, #$2229,
      #$2261, #$00B1, #$2265, #$2264, #$2320, #$2321, #$00F7, #$2248,
      #$00B0, #$2219, #$00B7, #$221A, #$207F, #$00B2, #$25A0, #$00A0);

function TIBM861Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP861Map[Ord(P)]
end;

function TIBM861Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP861Map, 'IBM861');
end;



{                                                                              }
{ IBM862                                                                       }
{   Identical with Windows-862.                                                }
{                                                                              }
class function TIBM862Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoPC862LatinHebrew;
end;

const
  CP862Map : AnsiCharHighMap = (
      #$05D0, #$05D1, #$05D2, #$05D3, #$05D4, #$05D5, #$05D6, #$05D7,
      #$05D8, #$05D9, #$05DA, #$05DB, #$05DC, #$05DD, #$05DE, #$05DF,
      #$05E0, #$05E1, #$05E2, #$05E3, #$05E4, #$05E5, #$05E6, #$05E7,
      #$05E8, #$05E9, #$05EA, #$00A2, #$00A3, #$00A5, #$20A7, #$0192,
      #$00E1, #$00ED, #$00F3, #$00FA, #$00F1, #$00D1, #$00AA, #$00BA,
      #$00BF, #$2310, #$00AC, #$00BD, #$00BC, #$00A1, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567,
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B,
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$03B1, #$00DF, #$0393, #$03C0, #$03A3, #$03C3, #$00B5, #$03C4,
      #$03A6, #$0398, #$03A9, #$03B4, #$221E, #$03C6, #$03B5, #$2229,
      #$2261, #$00B1, #$2265, #$2264, #$2320, #$2321, #$00F7, #$2248,
      #$00B0, #$2219, #$00B7, #$221A, #$207F, #$00B2, #$25A0, #$00A0);

function TIBM862Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP862Map[Ord(P)];
end;

function TIBM862Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP862Map, 'IBM862');
end;



{                                                                              }
{ IBM863                                                                       }
{   Identical with Windows-863.                                                }
{                                                                              }
class function TIBM863Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM863;
end;

const
  CP863Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00C2, #$00E0, #$00B6, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00EF, #$00EE, #$2017, #$00C0, #$00A7,
      #$00C9, #$00C8, #$00CA, #$00F4, #$00CB, #$00CF, #$00FB, #$00F9,
      #$00A4, #$00D4, #$00DC, #$00A2, #$00A3, #$00D9, #$00DB, #$0192,
      #$00A6, #$00B4, #$00F3, #$00FA, #$00A8, #$00B8, #$00B3, #$00AF,
      #$00CE, #$2310, #$00AC, #$00BD, #$00BC, #$00BE, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567,
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B,
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$03B1, #$00DF, #$0393, #$03C0, #$03A3, #$03C3, #$00B5, #$03C4,
      #$03A6, #$0398, #$03A9, #$03B4, #$221E, #$03C6, #$03B5, #$2229,
      #$2261, #$00B1, #$2265, #$2264, #$2320, #$2321, #$00F7, #$2248,
      #$00B0, #$2219, #$00B7, #$221A, #$207F, #$00B2, #$25A0, #$00A0);

function TIBM863Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP863Map[Ord(P)];
end;

function TIBM863Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP863Map, 'IBM863');
end;



{                                                                              }
{ Windows-864                                                                  }
{                                                                              }
const
  Windows864Map : AnsiCharHighMap = (
      #$00B0, #$00B7, #$2219, #$221A, #$2592, #$2500, #$2502, #$253C,
      #$2524, #$252C, #$251C, #$2534, #$2510, #$250C, #$2514, #$2518,
      #$03B2, #$221E, #$03C6, #$00B1, #$00BD, #$00BC, #$2248, #$00AB,
      #$00BB, #$FEF7, #$FEF8, #$009B, #$009C, #$FEFB, #$FEFC, #$009F,
      #$00A0, #$00AD, #$FE82, #$00A3, #$00A4, #$FE84, #$F8BE, #$F8BF,
      #$FE8E, #$FE8F, #$FE95, #$FE99, #$060C, #$FE9D, #$FEA1, #$FEA5,
      #$0660, #$0661, #$0662, #$0663, #$0664, #$0665, #$0666, #$0667,
      #$0668, #$0669, #$FED1, #$061B, #$FEB1, #$FEB5, #$FEB9, #$061F,
      #$00A2, #$FE80, #$FE81, #$FE83, #$FE85, #$FECA, #$FE8B, #$FE8D,
      #$FE91, #$FE93, #$FE97, #$FE9B, #$FE9F, #$FEA3, #$FEA7, #$FEA9,
      #$FEAB, #$FEAD, #$FEAF, #$FEB3, #$FEB7, #$FEBB, #$FEBF, #$FEC1,
      #$FEC5, #$FECB, #$FECF, #$00A6, #$00AC, #$00F7, #$00D7, #$FEC9,
      #$0640, #$FED3, #$FED7, #$FEDB, #$FEDF, #$FEE3, #$FEE7, #$FEEB,
      #$FEED, #$FEEF, #$FEF3, #$FEBD, #$FECC, #$FECE, #$FECD, #$FEE1,
      #$FE7D, #$0651, #$FEE5, #$FEE9, #$FEEC, #$FEF0, #$FEF2, #$FED0,
      #$FED5, #$FEF5, #$FEF6, #$FEDD, #$FED9, #$FEF1, #$25A0, #$F8C0);

function TWindows864Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows864Map[Ord(P)];
end;

function TWindows864Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows864Map, 'Windows-864');
end;

class function TWindows864Codec.Name: string;
begin
  Result := 'windows-864';
end;



{                                                                              }
{ IBM864                                                                       }
{                                                                              }
class function TIBM864Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM864;
end;

const
  IBM864Map : array[$25..$FF] of WideChar = (
      #$FFFF, #$0026, #$0027, #$0028, #$0029, #$002A, #$002B, #$002C,
      #$002D, #$002E, #$002F, #$0030, #$0031, #$0032, #$0033, #$0034,
      #$0035, #$0036, #$0037, #$0038, #$0039, #$003A, #$003B, #$003C,
      #$003D, #$003E, #$003F, #$0040, #$0041, #$0042, #$0043, #$0044,
      #$0045, #$0046, #$0047, #$0048, #$0049, #$004A, #$004B, #$004C,
      #$004D, #$004E, #$004F, #$0050, #$0051, #$0052, #$0053, #$0054,
      #$0055, #$0056, #$0057, #$0058, #$0059, #$005A, #$005B, #$005C,
      #$005D, #$005E, #$005F, #$0060, #$0061, #$0062, #$0063, #$0064,
      #$0065, #$0066, #$0067, #$0068, #$0069, #$006A, #$006B, #$006C,
      #$006D, #$006E, #$006F, #$0070, #$0071, #$0072, #$0073, #$0074,
      #$0075, #$0076, #$0077, #$0078, #$0079, #$007A, #$007B, #$007C,
      #$007D, #$007E, #$007F, #$00B0, #$00B7, #$2219, #$221A, #$2592,
      #$2500, #$2502, #$253C, #$2524, #$252C, #$251C, #$2534, #$2510,
      #$250C, #$2514, #$2518, #$03B2, #$221E, #$03C6, #$00B1, #$00BD,
      #$00BC, #$2248, #$00AB, #$00BB, #$FEF7, #$FEF8, #$FFFF, #$FFFF,
      #$FEFB, #$FEFC, #$FFFF, #$00A0, #$00AD, #$FE82, #$00A3, #$00A4,
      #$FE84, #$FFFF, #$FFFF, #$FE8E, #$FE8F, #$FE95, #$FE99, #$060C,
      #$FE9D, #$FEA1, #$FEA5, #$0660, #$0661, #$0662, #$0663, #$0664, 
      #$0665, #$0666, #$0667, #$0668, #$0669, #$FED1, #$061B, #$FEB1,
      #$FEB5, #$FEB9, #$061F, #$00A2, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FECA, #$FFFF, #$FE8D, #$FE91, #$FE93, #$FE97, #$FE9B, #$FE9F,
      #$FEA3, #$FEA7, #$FEA9, #$FEAB, #$FEAD, #$FEAF, #$FEB3, #$FEB7,
      #$FEBB, #$FEBF, #$FEC1, #$FEC5, #$FECB, #$FECF, #$00A6, #$00AC,
      #$00F7, #$00D7, #$FEC9, #$0640, #$FED3, #$FED7, #$FEDB, #$FEDF,
      #$FEE3, #$FEE7, #$FEEB, #$FEED, #$FEEF, #$FEF3, #$FEBD, #$FECC,
      #$FECE, #$FECD, #$FEE1, #$FE7D, #$0651, #$FEE5, #$FEE9, #$FEEC,
      #$FEF0, #$FEF2, #$FED0, #$FED5, #$FEF5, #$FEF6, #$FEDD, #$FED9,
      #$FEF1, #$25A0, #$FFFF);

function TIBM864Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) >= $25 then
    Result := IBM864Map[Ord(P)]
  else
    Result := WideChar(P);
end;

function TIBM864Codec.EncodeChar(const Ch: WideChar): AnsiChar;
var I : Byte;
begin
  if Ch = #$FFFF then
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'IBM864']);
  if Ord(Ch) < $25 then
    begin
      Result := AnsiChar(Ord(Ch));
      Exit;
    end;
  for I := $25 to $FF do
    if IBM864Map[I] = Ch then
      begin
        Result := AnsiChar(I);
        Exit;
      end;
  raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'IBM864']);
end;



{                                                                              }
{ IBM865                                                                       }
{   Identical with Windows-865.                                                }
{                                                                              }
class function TIBM865Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM865;
end;

const
  CP865Map : AnsiCharHighMap = (
      #$00C7, #$00FC, #$00E9, #$00E2, #$00E4, #$00E0, #$00E5, #$00E7,
      #$00EA, #$00EB, #$00E8, #$00EF, #$00EE, #$00EC, #$00C4, #$00C5,
      #$00C9, #$00E6, #$00C6, #$00F4, #$00F6, #$00F2, #$00FB, #$00F9,
      #$00FF, #$00D6, #$00DC, #$00F8, #$00A3, #$00D8, #$20A7, #$0192,
      #$00E1, #$00ED, #$00F3, #$00FA, #$00F1, #$00D1, #$00AA, #$00BA,
      #$00BF, #$2310, #$00AC, #$00BD, #$00BC, #$00A1, #$00AB, #$00A4,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567,
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B,
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$03B1, #$00DF, #$0393, #$03C0, #$03A3, #$03C3, #$00B5, #$03C4,
      #$03A6, #$0398, #$03A9, #$03B4, #$221E, #$03C6, #$03B5, #$2229,
      #$2261, #$00B1, #$2265, #$2264, #$2320, #$2321, #$00F7, #$2248,
      #$00B0, #$2219, #$00B7, #$221A, #$207F, #$00B2, #$25A0, #$00A0);

function TIBM865Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP865Map[Ord(P)];
end;

function TIBM865Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP865Map, 'IBM865');
end;



{                                                                              }
{ IBM866                                                                       }
{   Identical with Windows-866.                                                }
{                                                                              }
class function TIBM866Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM866;
end;

const
  CP866Map : AnsiCharHighMap = (
      #$0410, #$0411, #$0412, #$0413, #$0414, #$0415, #$0416, #$0417,
      #$0418, #$0419, #$041A, #$041B, #$041C, #$041D, #$041E, #$041F,
      #$0420, #$0421, #$0422, #$0423, #$0424, #$0425, #$0426, #$0427,
      #$0428, #$0429, #$042A, #$042B, #$042C, #$042D, #$042E, #$042F,
      #$0430, #$0431, #$0432, #$0433, #$0434, #$0435, #$0436, #$0437,
      #$0438, #$0439, #$043A, #$043B, #$043C, #$043D, #$043E, #$043F,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$2561, #$2562, #$2556,
      #$2555, #$2563, #$2551, #$2557, #$255D, #$255C, #$255B, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$255E, #$255F,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$2567,
      #$2568, #$2564, #$2565, #$2559, #$2558, #$2552, #$2553, #$256B,
      #$256A, #$2518, #$250C, #$2588, #$2584, #$258C, #$2590, #$2580,
      #$0440, #$0441, #$0442, #$0443, #$0444, #$0445, #$0446, #$0447,
      #$0448, #$0449, #$044A, #$044B, #$044C, #$044D, #$044E, #$044F,
      #$0401, #$0451, #$0404, #$0454, #$0407, #$0457, #$040E, #$045E,
      #$00B0, #$2219, #$00B7, #$221A, #$2116, #$00A4, #$25A0, #$00A0);

function TIBM866Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP866Map[Ord(P)];
end;

function TIBM866Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP866Map, 'IBM866');
end;



{                                                                              }
{ IBM868                                                                       }
{                                                                              }
class function TIBM868Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM868;
end;

const
  IBM868Map : AnsiCharHighMap = (
      #$0660, #$0661, #$0662, #$0663, #$0664, #$0665, #$0666, #$0667,
      #$0668, #$0669, #$060C, #$061B, #$061F, #$0622, #$0627, #$FE8E,
      #$E016, #$0628, #$FE91, #$067E, #$FFFF, #$0629, #$062A, #$FE97,
      #$FFFF, #$FFFF, #$062B, #$FE9B, #$062C, #$FE9F, #$FFFF, #$FFFF,
      #$062D, #$FEA3, #$062E, #$FEA7, #$062F, #$FFFF, #$0630, #$0631,
      #$FFFF, #$0632, #$FFFF, #$0633, #$FEB3, #$0634, #$00AB, #$00BB,
      #$FEB7, #$0635, #$2591, #$2592, #$2593, #$2502, #$2524, #$FEBB,
      #$0636, #$FEBF, #$0637, #$2563, #$2551, #$2557, #$255D, #$0638,
      #$0639, #$2510, #$2514, #$2534, #$252C, #$251C, #$2500, #$253C,
      #$FECA, #$FECB, #$255A, #$2554, #$2569, #$2566, #$2560, #$2550,
      #$256C, #$FECC, #$063A, #$FECE, #$FECF, #$FED0, #$0641, #$FED3,
      #$0642, #$FED7, #$FEDA, #$2518, #$250C, #$2588, #$2580, #$FEDB,
      #$FFFF, #$2584, #$FFFF, #$0644, #$FEDE, #$FEE0, #$0645, #$FEE3,
      #$FFFF, #$0646, #$FEE7, #$FFFF, #$0648, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$0621, #$00AD, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$0651, #$FE7D, #$FFFF, #$25A0, #$00A0);

function TIBM868Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := IBM868Map[Ord(P)];
end;

function TIBM868Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP866Map, 'IBM868');
end;



{                                                                              }
{ CP869                                                                        }
{   Map shared by IBM869 and Windows-869.                                      }
{                                                                              }
const
  CP869Map : AnsiCharHighMap = (
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$0386, #$FFFF,
      #$00B7, #$00AC, #$00A6, #$2018, #$2019, #$0388, #$2015, #$0389,
      #$038A, #$03AA, #$038C, #$FFFF, #$FFFF, #$038E, #$03AB, #$00A9,
      #$038F, #$00B2, #$00B3, #$03AC, #$00A3, #$03AD, #$03AE, #$03AF,
      #$03CA, #$0390, #$03CC, #$03CD, #$0391, #$0392, #$0393, #$0394,
      #$0395, #$0396, #$0397, #$00BD, #$0398, #$0399, #$00AB, #$00BB,
      #$2591, #$2592, #$2593, #$2502, #$2524, #$039A, #$039B, #$039C,
      #$039D, #$2563, #$2551, #$2557, #$255D, #$039E, #$039F, #$2510,
      #$2514, #$2534, #$252C, #$251C, #$2500, #$253C, #$03A0, #$03A1,
      #$255A, #$2554, #$2569, #$2566, #$2560, #$2550, #$256C, #$03A3,
      #$03A4, #$03A5, #$03A6, #$03A7, #$03A8, #$03A9, #$03B1, #$03B2,
      #$03B3, #$2518, #$250C, #$2588, #$2584, #$03B4, #$03B5, #$2580,
      #$03B6, #$03B7, #$03B8, #$03B9, #$03BA, #$03BB, #$03BC, #$03BD,
      #$03BE, #$03BF, #$03C0, #$03C1, #$03C3, #$03C2, #$03C4, #$0384,
      #$00AD, #$00B1, #$03C5, #$03C6, #$03C7, #$00A7, #$03C8, #$0385,
      #$00B0, #$00A8, #$03C9, #$03CB, #$03B0, #$03CE, #$25A0, #$00A0);



{                                                                              }
{ Windows-869                                                                  }
{                                                                              }
function TWindows869Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$7F : Result := WideChar(P);
    $87      : Result := #$0087;
    $93      : Result := #$0093;
    $94      : Result := #$0094;
  else
    Result := CP869Map[Ord(P)];
  end;
end;

function TWindows869Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $0087 : Result := #$87;
    $0093 : Result := #$93;
    $0094 : Result := #$94;
  else
    Result := CharFromHighMap(Ch, CP869Map, 'Windows-869');
  end;
end;

class function TWindows869Codec.Name: string;
begin
  Result := 'windows-869';
end;



{                                                                              }
{ IBM869                                                                       }
{                                                                              }
class function TIBM869Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM869;
end;

function TIBM869Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := CP869Map[Ord(P)];
end;

function TIBM869Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, CP869Map, 'IBM869');
end;



{                                                                              }
{ Windows-870                                                                  }
{                                                                              }
const
  Windows870Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$0163, #$00E1, #$0103, #$010D,
      #$00E7, #$0107, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$0119, #$00EB, #$016F, #$00ED, #$00EE, #$013E,
      #$013A, #$00DF, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$02DD, #$00C1, #$0102, #$010C,
      #$00C7, #$0106, #$007C, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$02C7, #$00C9, #$0118, #$00CB, #$016E, #$00CD, #$00CE, #$013D,
      #$0139, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$02D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$015B, #$0148, #$0111, #$00FD, #$0159, #$015F,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$0142, #$0144, #$0161, #$00B8, #$02DB, #$00A4,
      #$0105, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$015A, #$0147, #$0110, #$00DD, #$0158, #$015E,
      #$02D9, #$0104, #$017C, #$0162, #$017B, #$00A7, #$017E, #$017A,
      #$017D, #$0179, #$0141, #$0143, #$0160, #$00A8, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$0155, #$00F3, #$0151,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$011A, #$0171, #$00FC, #$0165, #$00FA, #$011B,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$010F, #$00D4, #$00D6, #$0154, #$00D3, #$0150,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$010E, #$0170, #$00DC, #$0164, #$00DA, #$009F);

function TWindows870Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows870Map[Ord(P)];
end;

function TWindows870Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows870Map, 'Windows-870');
end;

class function TWindows870Codec.Name: string;
begin
  Result := 'windows-870';
end;



{                                                                              }
{ IBM870                                                                       }
{                                                                              }
class function TIBM870Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM870;
end;

const
  IBM870Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$FFFF, #$00E4, #$FFFF, #$00E1, #$0103, #$010D, 
      #$00E7, #$0107, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$FFFF, #$00EB, #$016F, #$00ED, #$FFFF, #$013E, 
      #$013A, #$00DF, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$FFFF, #$00C4, #$02DD, #$00C1, #$FFFF, #$010C, 
      #$00C7, #$0106, #$007C, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$02C7, #$00C9, #$FFFF, #$00CB, #$016E, #$00CD, #$FFFF, #$013D, 
      #$0139, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$02D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$015B, #$0148, #$0111, #$00FD, #$0159, #$FFFF, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$0142, #$0144, #$0161, #$00B8, #$02DB, #$00A4,
      #$0105, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$015A, #$0147, #$0110, #$00DD, #$0158, #$FFFF,
      #$00B7, #$0104, #$017C, #$FFFF, #$017B, #$00A7, #$00B6, #$017E,
      #$017A, #$017D, #$0179, #$0143, #$0160, #$00A8, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$0155, #$00F3, #$0151,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$011A, #$0171, #$00FC, #$0165, #$00FA, #$011B,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$010F, #$00D4, #$00D6, #$0154, #$00D3, #$0150,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$010E, #$0170, #$00DC, #$0164, #$00DA, #$009F);

function TIBM870Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM870Map[Ord(P)];
end;

function TIBM870Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM870Map, 'IBM870');
end;



{                                                                              }
{ IBM871                                                                       }
{                                                                              }
class function TIBM871Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM871;
end;

const
  IBM871Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$00FE, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$00C6, #$0024, #$002A, #$0029, #$003B, #$00D6,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$00F0, #$003A, #$0023, #$00D0, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$0060, #$00FD, #$007B, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$007D, #$00B8, #$005D, #$00A4,
      #$00B5, #$00F6, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$0040, #$00DD, #$005B, #$00AE,
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$005C, #$00D7,
      #$00DE, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$007E, #$00F2, #$00F3, #$00F5,
      #$00E6, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$00B4, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$005E, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TIBM871Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM871Map[Ord(P)];
end;

function TIBM871Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM871Map, 'IBM871');
end;



{                                                                              }
{ Windows-874                                                                  }
{                                                                              }
const
  Windows874Map : AnsiCharHighMap = (
      #$20AC, #$0081, #$0082, #$0083, #$0084, #$2026, #$0086, #$0087,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$008D, #$008E, #$008F,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$0098, #$0099, #$009A, #$009B, #$009C, #$009D, #$009E, #$009F,
      #$00A0, #$0E01, #$0E02, #$0E03, #$0E04, #$0E05, #$0E06, #$0E07,
      #$0E08, #$0E09, #$0E0A, #$0E0B, #$0E0C, #$0E0D, #$0E0E, #$0E0F,
      #$0E10, #$0E11, #$0E12, #$0E13, #$0E14, #$0E15, #$0E16, #$0E17,
      #$0E18, #$0E19, #$0E1A, #$0E1B, #$0E1C, #$0E1D, #$0E1E, #$0E1F,
      #$0E20, #$0E21, #$0E22, #$0E23, #$0E24, #$0E25, #$0E26, #$0E27,
      #$0E28, #$0E29, #$0E2A, #$0E2B, #$0E2C, #$0E2D, #$0E2E, #$0E2F,
      #$0E30, #$0E31, #$0E32, #$0E33, #$0E34, #$0E35, #$0E36, #$0E37,
      #$0E38, #$0E39, #$0E3A, #$F8C1, #$F8C2, #$F8C3, #$F8C4, #$0E3F,
      #$0E40, #$0E41, #$0E42, #$0E43, #$0E44, #$0E45, #$0E46, #$0E47,
      #$0E48, #$0E49, #$0E4A, #$0E4B, #$0E4C, #$0E4D, #$0E4E, #$0E4F,
      #$0E50, #$0E51, #$0E52, #$0E53, #$0E54, #$0E55, #$0E56, #$0E57,
      #$0E58, #$0E59, #$0E5A, #$0E5B, #$F8C5, #$F8C6, #$F8C7, #$F8C8);

function TWindows874Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows874Map[Ord(P)];
end;

function TWindows874Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows874Map, 'Windows-874');
end;

class function TWindows874Codec.Name: string;
begin
  Result := 'windows-874';
end;



{                                                                              }
{ IBM874                                                                       }
{                                                                              }
const
  IBM874Map : AnsiCharHighMap = (
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$2026, #$FFFF, #$FFFF,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$00A0, #$0E01, #$0E02, #$0E03, #$0E04, #$0E05, #$0E06, #$0E07,
      #$0E08, #$0E09, #$0E0A, #$0E0B, #$0E0C, #$0E0D, #$0E0E, #$0E0F,
      #$0E10, #$0E11, #$0E12, #$0E13, #$0E14, #$0E15, #$0E16, #$0E17,
      #$0E18, #$0E19, #$0E1A, #$0E1B, #$0E1C, #$0E1D, #$0E1E, #$0E1F, 
      #$0E20, #$0E21, #$0E22, #$0E23, #$0E24, #$0E25, #$0E26, #$0E27,
      #$0E28, #$0E29, #$0E2A, #$0E2B, #$0E2C, #$0E2D, #$0E2E, #$0E2F,
      #$0E30, #$0E31, #$0E32, #$0E33, #$0E34, #$0E35, #$0E36, #$0E37,
      #$0E38, #$0E39, #$0E3A, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$0E3F,
      #$0E40, #$0E41, #$0E42, #$0E43, #$0E44, #$0E45, #$0E46, #$0E47,
      #$0E48, #$0E49, #$0E4A, #$0E4B, #$0E4C, #$0E4D, #$0E4E, #$0E4F,
      #$0E50, #$0E51, #$0E52, #$0E53, #$0E54, #$0E55, #$0E56, #$0E57,
      #$0E58, #$0E59, #$0E5A, #$0E5B, #$FFFF, #$FFFF, #$FFFF, #$FFFF);

function TIBM874Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := IBM874Map[Ord(P)];
end;

function TIBM874Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, IBM874Map, 'IBM874');
end;

class function TIBM874Codec.Name: string;
begin
  Result := 'IBM874';
end;



{                                                                              }
{ Windows-875                                                                  }
{                                                                              }
const
  Windows875Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$0391, #$0392, #$0393, #$0394, #$0395, #$0396, #$0397,
      #$0398, #$0399, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$039A, #$039B, #$039C, #$039D, #$039E, #$039F, #$03A0,
      #$03A1, #$03A3, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$03A4, #$03A5, #$03A6, #$03A7, #$03A8, #$03A9, 
      #$03AA, #$03AB, #$007C, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00A8, #$0386, #$0388, #$0389, #$00A0, #$038A, #$038C, #$038E,
      #$038F, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022, 
      #$0385, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$03B1, #$03B2, #$03B3, #$03B4, #$03B5, #$03B6, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$03B7, #$03B8, #$03B9, #$03BA, #$03BB, #$03BC,
      #$00B4, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078, 
      #$0079, #$007A, #$03BD, #$03BE, #$03BF, #$03C0, #$03C1, #$03C3,
      #$00A3, #$03AC, #$03AD, #$03AE, #$03CA, #$03AF, #$03CC, #$03CD,
      #$03CB, #$03CE, #$03C2, #$03C4, #$03C5, #$03C6, #$03C7, #$03C8, 
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047, 
      #$0048, #$0049, #$00AD, #$03C9, #$0390, #$03B0, #$2018, #$2015,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B1, #$00BD, #$FFFF, #$0387, #$2019, #$00A6,
      #$005C, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00A7, #$FFFF, #$FFFF, #$00AB, #$00AC,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00A9, #$FFFF, #$FFFF, #$00BB, #$009F);

function TWindows875Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows875Map[Ord(P)];
end;

function TWindows875Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows875Map, 'Windows-875');
end;

class function TWindows875Codec.Name: string;
begin
  Result := 'windows-875';
end;



{                                                                              }
{ IBM875                                                                       }
{                                                                              }
const
  IBM875Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$0391, #$0392, #$0393, #$0394, #$0395, #$0396, #$0397,
      #$0398, #$0399, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$039A, #$039B, #$039C, #$039D, #$039E, #$039F, #$03A0, 
      #$03A1, #$03A3, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$03A4, #$03A5, #$03A6, #$03A7, #$03A8, #$03A9,
      #$03AA, #$03AB, #$FFFF, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00A8, #$0386, #$0388, #$0389, #$2207, #$038A, #$038C, #$038E, 
      #$038F, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022, 
      #$0385, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$03B1, #$03B2, #$03B3, #$03B4, #$03B5, #$03B6,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$03B7, #$03B8, #$03B9, #$03BA, #$03BB, #$03BC,
      #$00B4, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$03BD, #$03BE, #$03BF, #$03C0, #$03C1, #$03C3,
      #$00A3, #$03AC, #$03AD, #$03AE, #$0390, #$03AF, #$03CC, #$03CD, 
      #$03B0, #$03CE, #$03C2, #$03C4, #$03C5, #$03C6, #$03C7, #$03C8,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$03C9, #$03CA, #$03CB, #$2018, #$2015,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B1, #$00BD, #$FFFF, #$00B7, #$2019, #$00A6,
      #$005C, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00A7, #$FFFF, #$FFFF, #$00AB, #$00AC,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00A9, #$FFFF, #$FFFF, #$00BB, #$009F);

function TIBM875Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM875Map[Ord(P)];
end;

function TIBM875Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM875Map, 'IBM875');
end;

class function TIBM875Codec.Name: string;
begin
  Result := 'IBM875';
end;



{                                                                              }
{ IBM880                                                                       }
{                                                                              }
class function TIBM880Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM880;
end;

const
  IBM880Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$FFFF, #$0452, #$0453, #$0451, #$FFFF, #$0455, #$0456, 
      #$0457, #$0458, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$0459, #$045A, #$045B, #$045C, #$FFFF, #$045F, #$042A, 
      #$2116, #$0402, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$0403, #$0401, #$FFFF, #$0405, #$0406, #$0407, 
      #$0408, #$0409, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$040A, #$040B, #$040C, #$FFFF, #$FFFF, #$040F, #$044E, #$0430, 
      #$0431, #$FFFF, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022, 
      #$0446, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$0434, #$0435, #$0444, #$0433, #$0445, #$0438, 
      #$0439, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$043A, #$043B, #$043C, #$043D, #$043E, #$043F, 
      #$044F, #$FFFF, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$0440, #$0441, #$0442, #$0443, #$0436, #$0432,
      #$044C, #$044B, #$0437, #$0448, #$044D, #$0449, #$0447, #$044A,
      #$042E, #$0410, #$0411, #$0426, #$0414, #$0415, #$0424, #$0413, 
      #$FFFF, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$0425, #$0418, #$0419, #$041A, #$041B, #$041C, 
      #$FFFF, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$041D, #$041E, #$041F, #$042F, #$0420, #$0421,
      #$005C, #$00A4, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$0422, #$0423, #$0416, #$0412, #$042C, #$042B, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$0417, #$0428, #$042D, #$0429, #$0427, #$009F);

function TIBM880Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM880Map[Ord(P)];
end;

function TIBM880Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM880Map, 'IBM880');
end;



{                                                                              }
{ IBM904                                                                       }
{                                                                              }
class function TIBM904Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM904;
end;

function TIBM904Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $00..$7F : Result := WideChar(P);
    $80      : Result := #$00A2;
    $FD      : Result := #$00AC;
    $FE      : Result := #$00A6;
  else
    Result := #$FFFF;
  end;
end;

function TIBM904Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $00..$7F : Result := AnsiChar(Ch);
    $00A2    : Result := #$80;
    $00AC    : Result := #$FD;
    $00A6    : Result := #$FE;
  else
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'IBM904']);
  end;
end;



{                                                                              }
{ IBM905                                                                       }
{                                                                              }
class function TIBM905Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM905;
end;

const
  IBM905Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F, 
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B, 
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004, 
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$FFFF, #$00E2, #$00E4, #$00E0, #$00E1, #$FFFF, #$010B,
      #$007B, #$00F1, #$00C7, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$011E, #$0130, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$FFFF, #$010A, 
      #$005B, #$00D1, #$015F, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$FFFF, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$0131, #$003A, #$00D6, #$015E, #$0027, #$003D, #$00DC,
      #$02D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$0127, #$0109, #$015D, #$016D, #$FFFF, #$007C, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$0125, #$011D, #$0135, #$02DB, #$FFFF, #$00A4, 
      #$00B5, #$00F6, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$0126, #$0108, #$015C, #$016C, #$FFFF, #$0040, 
      #$00B7, #$00A3, #$017C, #$007D, #$017B, #$00A7, #$005D, #$FFFF,
      #$00BD, #$0024, #$0124, #$011C, #$0134, #$00A8, #$00B4, #$00D7, 
      #$00E7, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047, 
      #$0048, #$0049, #$00AD, #$00F4, #$007E, #$00F2, #$00F3, #$0121,
      #$011F, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$0060, #$00FB, #$005C, #$00F9, #$00FA, #$FFFF, 
      #$00FC, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$0023, #$00D2, #$00D3, #$0120,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$0022, #$00D9, #$00DA, #$009F);

function TIBM905Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM905Map[Ord(P)];
end;

function TIBM905Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM905Map, 'IBM905');
end;



{                                                                              }
{ IBM918                                                                       }
{                                                                              }
class function TIBM918Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM918;
end;

const
  IBM918Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$060C, #$061B, #$061F, #$0623, #$0627, #$FE8E,
      #$FFFF, #$0628, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$FE91, #$067E, #$FFFF, #$0629, #$062A, #$FE97, #$FFFF,
      #$FFFF, #$062B, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$FE9B, #$062C, #$FE9F, #$FFFF, #$FFFF, #$062D,
      #$FEA3, #$062E, #$0060, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$0660, #$0661, #$0662, #$0663, #$0664, #$0665, #$0666, #$0667,
      #$0668, #$0669, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$FEA7, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$062F, #$FFFF, #$0630, #$0631, #$FFFF, #$0632, 
      #$FFFF, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$0633, #$FEB3, #$0634, #$FEB7, #$0635, #$FEBB, 
      #$0636, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$FEBF, #$0637, #$0638, #$0639, #$FECA, #$FECB,
      #$FECC, #$063A, #$FECE, #$FECF, #$FED0, #$0641, #$FED3, #$0642,
      #$FED7, #$0643, #$FEDB, #$007C, #$FFFF, #$FFFF, #$0644, #$FEDE,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$FFFF, #$0645, #$FEE3, #$FFFF, #$0646, 
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$FEE7, #$FFFF, #$0648, #$FFFF, #$FFFF, #$FFFF,
      #$005C, #$FFFF, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$0621, #$FFFF, #$FFFF, #$FFFF, #$FFFF, #$FFFF,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$FFFF, #$FFFF, #$FFFF, #$0651, #$FE7D, #$009F);

function TIBM918Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := IBM918Map[Ord(P)];
end;

function TIBM918Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, IBM918Map, 'IBM918');
end;



{                                                                              }
{ IBM1004                                                                      }
{                                                                              }
const
  IBM1004Map : array[$80..$FF] of WideChar = (
      #$FFFF, #$FFFF, #$201A, #$FFFF, #$201E, #$2026, #$2020, #$2021,
      #$02C6, #$2030, #$0160, #$2039, #$0152, #$FFFF, #$FFFF, #$FFFF,
      #$FFFF, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$02DC, #$2122, #$0161, #$203A, #$0153, #$FFFF, #$FFFF, #$0178,
      #$00A0, #$00A1, #$00A2, #$00A3, #$00A4, #$00A5, #$00A6, #$00A7,
      #$00A8, #$00A9, #$00AA, #$00AB, #$00AC, #$00AD, #$00AE, #$00AF,
      #$00B0, #$00B1, #$00B2, #$00B3, #$00B4, #$00B5, #$00B6, #$00B7,
      #$00B8, #$00B9, #$00BA, #$00BB, #$00BC, #$00BD, #$00BE, #$00BF,
      #$00C0, #$00C1, #$00C2, #$00C3, #$00C4, #$00C5, #$00C6, #$00C7,
      #$00C8, #$00C9, #$00CA, #$00CB, #$00CC, #$00CD, #$00CE, #$00CF,
      #$00D0, #$00D1, #$00D2, #$00D3, #$00D4, #$00D5, #$00D6, #$00D7,
      #$00D8, #$00D9, #$00DA, #$00DB, #$00DC, #$00DD, #$00DE, #$00DF,
      #$00E0, #$00E1, #$00E2, #$00E3, #$00E4, #$00E5, #$00E6, #$00E7,
      #$00E8, #$00E9, #$00EA, #$00EB, #$00EC, #$00ED, #$00EE, #$00EF,
      #$00F0, #$00F1, #$00F2, #$00F3, #$00F4, #$00F5, #$00F6, #$00F7,
      #$00F8, #$00F9, #$00FA, #$00FB, #$00FC, #$00FD, #$00FE, #$00FF);

function TIBM1004Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) >= $80 then
      Result := IBM1004Map[Ord(P)]
    else
      Result := WideChar(P);
end;

function TIBM1004Codec.EncodeChar(const Ch: WideChar): AnsiChar;
var
  I: Byte;
begin
  if Ch = #$FFFF then
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'IBM1004']);
  if Ord(Ch) < $80 then
    begin
      Result := AnsiChar(Ord(Ch));
      Exit;
    end;
  for I := $80 to $FF do
    if IBM1004Map[I] = Ch then
      begin
        Result := AnsiChar(I);
        Exit;
      end;
  raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'IBM1004']);
end;

class function TIBM1004Codec.Name: string;
begin
  Result := 'IBM1004';
end;



{                                                                              }
{ CP1026                                                                       }
{   Map shared by IBM1026 and Windows-1026.                                    }
{                                                                              }
const
  CP1026Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$007B, #$00F1, #$00C7, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$011E, #$0130, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$005B, #$00D1, #$015F, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0131, #$003A, #$00D6, #$015E, #$0027, #$003D, #$00DC,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$007D, #$0060, #$00A6, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$FFFF, #$00C6, #$00A4,
      #$00B5, #$00F6, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$005D, #$0024, #$0040, #$00AE,
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$FFFF, #$00A8, #$00B4, #$00D7,
      #$00E7, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$007E, #$00F2, #$00F3, #$00F5,
      #$011F, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$005C, #$00F9, #$00FA, #$00FF,
      #$00FC, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$0023, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$0022, #$00D9, #$00DA, #$009F);



{                                                                              }
{ Windows-1026                                                                 }
{                                                                              }
function TWindows1026Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $9D : Result := #$00B8;
    $BC : Result := #$00AF;
  else
    Result := CP1026Map[Ord(P)];
  end;
end;

function TWindows1026Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $00B8 : Result := #$9D;
    $00AF : Result := #$BC;
  else
    Result := CharFromMap(Ch, CP1026Map, 'Windows-1026');
  end;
end;

class function TWindows1026Codec.Name: string;
begin
  Result := 'windows-1026';
end;



{                                                                              }
{ IBM1026                                                                      }
{                                                                              }
class function TIBM1026Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM1026;
end;

function TIBM1026Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $9D : Result := #$02DB;
    $BC : Result := #$2014;
  else
    Result := CP1026Map[Ord(P)];
  end;
end;

function TIBM1026Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $02DB : Result := #$9D;
    $2014 : Result := #$BC;
  else
    Result := CharFromMap(Ch, CP1026Map, 'IBM1026');
  end;
end;



{                                                                              }
{ CP1047                                                                       }
{   Map shared by IBM1047 and Windows-1047.                                    }
{   Similar to CP37.                                                           }
{                                                                              }
const
  CP1047Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$FFFF, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$FFFF, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$00A2, #$002E, #$003C, #$0028, #$002B, #$007C,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$0021, #$0024, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$00A4,
      #$00B5, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$005B, #$00DE, #$00AE,
      #$00AC, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00DD, #$00A8, #$00AF, #$005D, #$00B4, #$00D7,
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);



{                                                                              }
{ Windows-1047                                                                 }
{                                                                              }
function TWindows1047Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $15 : Result := #$000A;
    $25 : Result := #$0085;
  else
    Result := CP1047Map[Ord(P)];
  end;
end;

function TWindows1047Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $000A : Result := #$15;
    $0085 : Result := #$25;
  else
    Result := CharFromMap(Ch, CP1047Map, 'Windows-1047');
  end;
end;

class function TWindows1047Codec.Name: string;
begin
  Result := 'windows-1047';
end;



{                                                                              }
{ IBM1047                                                                      }
{                                                                              }
class function TIBM1047Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoIBM1047;
end;

function TIBM1047Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $15 : Result := #$0085;
    $25 : Result := #$000A;
  else
    Result := CP1047Map[Ord(P)];
  end;
end;

function TIBM1047Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $000A : Result := #$25;
    $0085 : Result := #$15;
  else
    Result := CharFromMap(Ch, CP1047Map, 'IBM1047');
  end;
end;



{                                                                              }
{ Windows-1140                                                                 }
{   Similar to CP37.                                                           }
{                                                                              }
function TWindows1140Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $9F : Result := #$20AC;
  else
    Result := CP37Map[Ord(P)];
  end;
end;

function TWindows1140Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $20AC : Result := #$9F;
  else
    begin
      Result := CharFromMap(Ch, CP37Map, 'Windows-1140');
      if Result = #$9F then
        raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'Windows-1140']);
    end;
  end;
end;

class function TWindows1140Codec.Name: string;
begin
  Result := 'windows-1140';
end;



{                                                                              }
{ Windows-1141                                                                 }
{   Similar to IBM273.                                                         }
{                                                                              }
const
  Windows1141Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$007B, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$00C4, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF, 
      #$00EC, #$007E, #$00DC, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$00C2, #$005B, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00F6, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$0060, #$003A, #$0023, #$00A7, #$0027, #$003D, #$0022, 
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1,
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$20AC,
      #$00B5, #$00DF, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078, 
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE, 
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$0040, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$00B4, #$00D7,
      #$00E4, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00A6, #$00F2, #$00F3, #$00F5,
      #$00FC, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$007D, #$00F9, #$00FA, #$00FF,
      #$00D6, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$005C, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$005D, #$00D9, #$00DA, #$009F);

function TWindows1141Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1141Map[Ord(P)];
end;

function TWindows1141Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1141Map, 'Windows-1141');
end;

class function TWindows1141Codec.Name: string;
begin
  Result := 'windows-1141';
end;



{                                                                              }
{ Windows-1142                                                                 }
{   Similar to IBM277.                                                         }
{                                                                              }
const
  Windows1142Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F, 
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F, 
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B, 
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$007D, 
      #$00E7, #$00F1, #$0023, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$20AC, #$00C5, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$0024, 
      #$00C7, #$00D1, #$00F8, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00A6, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$0060, #$003A, #$00C6, #$00D8, #$0027, #$003D, #$0022, 
      #$0040, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$007B, #$00B8, #$005B, #$005D,
      #$00B5, #$00FC, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078, 
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE, 
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC, 
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$00B4, #$00D7, 
      #$00E6, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$00E5, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$007E, #$00F9, #$00FA, #$00FF, 
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058, 
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1142Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1142Map[Ord(P)];
end;

function TWindows1142Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1142Map, 'Windows-1142');
end;

class function TWindows1142Codec.Name: string;
begin
  Result := 'windows-1142';
end;



{                                                                              }
{ Windows-1143                                                                 }
{   Similar to IBM278.                                                         }
{                                                                              }
const
  Windows1143Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$007B, #$00E0, #$00E1, #$00E3, #$007D, 
      #$00E7, #$00F1, #$00A7, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$0060, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$20AC, #$00C5, #$002A, #$0029, #$003B, #$005E,
      #$002D, #$002F, #$00C2, #$0023, #$00C0, #$00C1, #$00C3, #$0024, 
      #$00C7, #$00D1, #$00F6, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$005C, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$00E9, #$003A, #$00C4, #$00D6, #$0027, #$003D, #$0022, 
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$005D,
      #$00B5, #$00FC, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$005B, #$00B6, #$00BC, 
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$00B4, #$00D7, 
      #$00E4, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00A6, #$00F2, #$00F3, #$00F5,
      #$00E5, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$007E, #$00F9, #$00FA, #$00FF, 
      #$00C9, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058, 
      #$0059, #$005A, #$00B2, #$00D4, #$0040, #$00D2, #$00D3, #$00D5, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1143Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1143Map[Ord(P)];
end;

function TWindows1143Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1143Map, 'Windows-1143');
end;

class function TWindows1143Codec.Name: string;
begin
  Result := 'windows-1143';
end;



{                                                                              }
{ Windows-1144                                                                 }
{   Similar to IBM280.                                                         }
{                                                                              }
const
  Windows1144Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$00E4, #$007B, #$00E1, #$00E3, #$00E5, 
      #$005C, #$00F1, #$00B0, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$005D, #$00EA, #$00EB, #$007D, #$00ED, #$00EE, #$00EF, 
      #$007E, #$00DF, #$00E9, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$00D1, #$00F2, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$00F9, #$003A, #$00A3, #$00A7, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$005B, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$20AC,
      #$00B5, #$00EC, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$0023, #$00A5, #$00B7, #$00A9, #$0040, #$00B6, #$00BC, 
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$00B4, #$00D7,
      #$00E0, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00A6, #$00F3, #$00F5,
      #$00E8, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$0060, #$00FA, #$00FF, 
      #$00E7, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058, 
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1144Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1144Map[Ord(P)];
end;

function TWindows1144Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1144Map, 'Windows-1144');
end;

class function TWindows1144Codec.Name: string;
begin
  Result := 'windows-1144';
end;



{                                                                              }
{ Windows-1145                                                                 }
{   Similar to IBM284.                                                         }
{                                                                              }
const
  Windows1145Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00A6, #$005B, #$002E, #$003C, #$0028, #$002B, #$007C, 
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$005D, #$0024, #$002A, #$0029, #$003B, #$00AC,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$0023, #$00F1, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$0060, #$003A, #$00D1, #$0040, #$0027, #$003D, #$0022, 
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$20AC,
      #$00B5, #$00A8, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078, 
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE, 
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC, 
      #$00BD, #$00BE, #$005E, #$0021, #$00AF, #$007E, #$00B4, #$00D7, 
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF,
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1145Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1145Map[Ord(P)];
end;

function TWindows1145Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1145Map, 'Windows-1145');
end;

class function TWindows1145Codec.Name: string;
begin
  Result := 'windows-1145';
end;

{                                                                              }
{ Windows-1146                                                                 }
{   Similar to IBM285.                                                         }
{                                                                              }
const
  Windows1146Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004,
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5,
      #$00E7, #$00F1, #$0024, #$002E, #$003C, #$0028, #$002B, #$007C, 
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF, 
      #$00EC, #$00DF, #$0021, #$00A3, #$002A, #$0029, #$003B, #$00AC,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5,
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022, 
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067,
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$20AC,
      #$00B5, #$00AF, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078, 
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE, 
      #$00A2, #$005B, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC, 
      #$00BD, #$00BE, #$005E, #$005D, #$007E, #$00A8, #$00B4, #$00D7, 
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF, 
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5, 
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037, 
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1146Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1146Map[Ord(P)];
end;

function TWindows1146Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1146Map, 'Windows-1146');
end;

class function TWindows1146Codec.Name: string;
begin
  Result := 'windows-1146';
end;



{                                                                              }
{ Windows-1147                                                                 }
{   Similar to IBM297.                                                         }
{                                                                              }
const
  Windows1147Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004, 
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$00E4, #$0040, #$00E1, #$00E3, #$00E5, 
      #$005C, #$00F1, #$00B0, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$007B, #$00EA, #$00EB, #$007D, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$00A7, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$00D1, #$00F9, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$00B5, #$003A, #$00A3, #$00E0, #$0027, #$003D, #$0022, 
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$005B, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070, 
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$20AC,
      #$0060, #$00A8, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE,
      #$00A2, #$0023, #$00A5, #$00B7, #$00A9, #$005D, #$00B6, #$00BC,
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$007E, #$00B4, #$00D7,
      #$00E9, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$00E8, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050,
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00A6, #$00FA, #$00FF,
      #$00E7, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1147Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1147Map[Ord(P)];
end;

function TWindows1147Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1147Map, 'Windows-1147');
end;

class function TWindows1147Codec.Name: string;
begin
  Result := 'windows-1147';
end;



{                                                                              }
{ Windows-1148                                                                 }
{   Similar to CP500.                                                          }
{                                                                              }
const
  Windows1148Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007,
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004, 
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A,
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5, 
      #$00E7, #$00F1, #$005B, #$002E, #$003C, #$0028, #$002B, #$0021, 
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF, 
      #$00EC, #$00DF, #$005D, #$0024, #$002A, #$0029, #$003B, #$005E, 
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F, 
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, 
      #$00CC, #$0060, #$003A, #$0023, #$0040, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$00F0, #$00FD, #$00FE, #$00B1, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$00E6, #$00B8, #$00C6, #$20AC,
      #$00B5, #$007E, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078, 
      #$0079, #$007A, #$00A1, #$00BF, #$00D0, #$00DD, #$00DE, #$00AE, 
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC, 
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$00B4, #$00D7, 
      #$007B, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$00F6, #$00F2, #$00F3, #$00F5,
      #$007D, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF, 
      #$005C, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$00D6, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1148Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1148Map[Ord(P)];
end;

function TWindows1148Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1148Map, 'Windows-1148');
end;

class function TWindows1148Codec.Name: string;
begin
  Result := 'windows-1148';
end;



{                                                                              }
{ Windows-1149                                                                 }
{   Similar to IBM871.                                                         }
{                                                                              }
const
  Windows1149Map : AnsiCharMap = (
      #$0000, #$0001, #$0002, #$0003, #$009C, #$0009, #$0086, #$007F,
      #$0097, #$008D, #$008E, #$000B, #$000C, #$000D, #$000E, #$000F,
      #$0010, #$0011, #$0012, #$0013, #$009D, #$0085, #$0008, #$0087,
      #$0018, #$0019, #$0092, #$008F, #$001C, #$001D, #$001E, #$001F,
      #$0080, #$0081, #$0082, #$0083, #$0084, #$000A, #$0017, #$001B,
      #$0088, #$0089, #$008A, #$008B, #$008C, #$0005, #$0006, #$0007, 
      #$0090, #$0091, #$0016, #$0093, #$0094, #$0095, #$0096, #$0004, 
      #$0098, #$0099, #$009A, #$009B, #$0014, #$0015, #$009E, #$001A, 
      #$0020, #$00A0, #$00E2, #$00E4, #$00E0, #$00E1, #$00E3, #$00E5, 
      #$00E7, #$00F1, #$00DE, #$002E, #$003C, #$0028, #$002B, #$0021,
      #$0026, #$00E9, #$00EA, #$00EB, #$00E8, #$00ED, #$00EE, #$00EF,
      #$00EC, #$00DF, #$00C6, #$0024, #$002A, #$0029, #$003B, #$00D6,
      #$002D, #$002F, #$00C2, #$00C4, #$00C0, #$00C1, #$00C3, #$00C5, 
      #$00C7, #$00D1, #$00A6, #$002C, #$0025, #$005F, #$003E, #$003F,
      #$00F8, #$00C9, #$00CA, #$00CB, #$00C8, #$00CD, #$00CE, #$00CF,
      #$00CC, #$00F0, #$003A, #$0023, #$00D0, #$0027, #$003D, #$0022,
      #$00D8, #$0061, #$0062, #$0063, #$0064, #$0065, #$0066, #$0067, 
      #$0068, #$0069, #$00AB, #$00BB, #$0060, #$00FD, #$007B, #$00B1, 
      #$00B0, #$006A, #$006B, #$006C, #$006D, #$006E, #$006F, #$0070,
      #$0071, #$0072, #$00AA, #$00BA, #$007D, #$00B8, #$005D, #$20AC,
      #$00B5, #$00F6, #$0073, #$0074, #$0075, #$0076, #$0077, #$0078,
      #$0079, #$007A, #$00A1, #$00BF, #$0040, #$00DD, #$005B, #$00AE, 
      #$00A2, #$00A3, #$00A5, #$00B7, #$00A9, #$00A7, #$00B6, #$00BC, 
      #$00BD, #$00BE, #$00AC, #$007C, #$00AF, #$00A8, #$005C, #$00D7, 
      #$00FE, #$0041, #$0042, #$0043, #$0044, #$0045, #$0046, #$0047,
      #$0048, #$0049, #$00AD, #$00F4, #$007E, #$00F2, #$00F3, #$00F5,
      #$00E6, #$004A, #$004B, #$004C, #$004D, #$004E, #$004F, #$0050, 
      #$0051, #$0052, #$00B9, #$00FB, #$00FC, #$00F9, #$00FA, #$00FF, 
      #$00B4, #$00F7, #$0053, #$0054, #$0055, #$0056, #$0057, #$0058,
      #$0059, #$005A, #$00B2, #$00D4, #$005E, #$00D2, #$00D3, #$00D5,
      #$0030, #$0031, #$0032, #$0033, #$0034, #$0035, #$0036, #$0037,
      #$0038, #$0039, #$00B3, #$00DB, #$00DC, #$00D9, #$00DA, #$009F);

function TWindows1149Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  Result := Windows1149Map[Ord(P)];
end;

function TWindows1149Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromMap(Ch, Windows1149Map, 'Windows-1149');
end;

class function TWindows1149Codec.Name: string;
begin
  Result := 'windows-1149';
end;



{                                                                              }
{ Windows-1250                                                                 }
{                                                                              }
class function TWindows1250Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1250;
end;

const
  Windows1250Map : AnsiCharHighMap = (
      #$20AC, #$0081, #$201A, #$0083, #$201E, #$2026, #$2020, #$2021, 
      #$0088, #$2030, #$0160, #$2039, #$015A, #$0164, #$017D, #$0179,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014, 
      #$0098, #$2122, #$0161, #$203A, #$015B, #$0165, #$017E, #$017A, 
      #$00A0, #$02C7, #$02D8, #$0141, #$00A4, #$0104, #$00A6, #$00A7,
      #$00A8, #$00A9, #$015E, #$00AB, #$00AC, #$00AD, #$00AE, #$017B, 
      #$00B0, #$00B1, #$02DB, #$0142, #$00B4, #$00B5, #$00B6, #$00B7, 
      #$00B8, #$0105, #$015F, #$00BB, #$013D, #$02DD, #$013E, #$017C, 
      #$0154, #$00C1, #$00C2, #$0102, #$00C4, #$0139, #$0106, #$00C7, 
      #$010C, #$00C9, #$0118, #$00CB, #$011A, #$00CD, #$00CE, #$010E,
      #$0110, #$0143, #$0147, #$00D3, #$00D4, #$0150, #$00D6, #$00D7, 
      #$0158, #$016E, #$00DA, #$0170, #$00DC, #$00DD, #$0162, #$00DF, 
      #$0155, #$00E1, #$00E2, #$0103, #$00E4, #$013A, #$0107, #$00E7,
      #$010D, #$00E9, #$0119, #$00EB, #$011B, #$00ED, #$00EE, #$010F,
      #$0111, #$0144, #$0148, #$00F3, #$00F4, #$0151, #$00F6, #$00F7,
      #$0159, #$016F, #$00FA, #$0171, #$00FC, #$00FD, #$0163, #$02D9);

function TWindows1250Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1250Map[Ord(P)];
end;

function TWindows1250Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1250Map, 'Windows-1250');
end;



{                                                                              }
{ Windows-1251                                                                 }
{                                                                              }
class function TWindows1251Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1251;
end;

const
  Windows1251Map : AnsiCharHighMap = (
      #$0402, #$0403, #$201A, #$0453, #$201E, #$2026, #$2020, #$2021,
      #$20AC, #$2030, #$0409, #$2039, #$040A, #$040C, #$040B, #$040F,
      #$0452, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$0098, #$2122, #$0459, #$203A, #$045A, #$045C, #$045B, #$045F,
      #$00A0, #$040E, #$045E, #$0408, #$00A4, #$0490, #$00A6, #$00A7,
      #$0401, #$00A9, #$0404, #$00AB, #$00AC, #$00AD, #$00AE, #$0407,
      #$00B0, #$00B1, #$0406, #$0456, #$0491, #$00B5, #$00B6, #$00B7,
      #$0451, #$2116, #$0454, #$00BB, #$0458, #$0405, #$0455, #$0457,
      #$0410, #$0411, #$0412, #$0413, #$0414, #$0415, #$0416, #$0417, 
      #$0418, #$0419, #$041A, #$041B, #$041C, #$041D, #$041E, #$041F,
      #$0420, #$0421, #$0422, #$0423, #$0424, #$0425, #$0426, #$0427,
      #$0428, #$0429, #$042A, #$042B, #$042C, #$042D, #$042E, #$042F,
      #$0430, #$0431, #$0432, #$0433, #$0434, #$0435, #$0436, #$0437,
      #$0438, #$0439, #$043A, #$043B, #$043C, #$043D, #$043E, #$043F,
      #$0440, #$0441, #$0442, #$0443, #$0444, #$0445, #$0446, #$0447,
      #$0448, #$0449, #$044A, #$044B, #$044C, #$044D, #$044E, #$044F);

function TWindows1251Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1251Map[Ord(P)];
end;

function TWindows1251Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1251Map, 'Windows-1251');
end;



{                                                                              }
{ Windows-1252                                                                 }
{                                                                              }
class function TWindows1252Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1252;
end;

const
  Windows1252Map : array[#$80..#$9F] of WideChar = (
      #$20AC, #$0081, #$201A, #$0192, #$201E, #$2026, #$2020, #$2021,
      #$02C6, #$2030, #$0160, #$2039, #$0152, #$008D, #$017D, #$008F,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$02DC, #$2122, #$0161, #$203A, #$0153, #$009D, #$017E, #$0178);

function TWindows1252Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) in [$80..$9F] then
    Result := Windows1252Map[P]
  else
    Result := WideChar(P);
end;

function TWindows1252Codec.EncodeChar(const Ch: WideChar): AnsiChar;
var I : AnsiChar;
begin
  if (Ord(Ch) < $80) or
     ((Ord(Ch) < $100) and (Ord(Ch) > $9F)) then
    begin
      Result := AnsiChar(Ch);
      Exit;
    end;
  for I := #$80 to #$9F do
    if Windows1252Map[I] = Ch then
      begin
        Result := I;
        Exit;
      end;
  raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'Windows-1252']);
end;



{                                                                              }
{ Windows-1253                                                                 }
{                                                                              }
class function TWindows1253Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1253;
end;

const
  Windows1253Map : AnsiCharHighMap = (
      #$20AC, #$0081, #$201A, #$0192, #$201E, #$2026, #$2020, #$2021,
      #$0088, #$2030, #$008A, #$2039, #$008C, #$008D, #$008E, #$008F,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$0098, #$2122, #$009A, #$203A, #$009C, #$009D, #$009E, #$009F,
      #$00A0, #$0385, #$0386, #$00A3, #$00A4, #$00A5, #$00A6, #$00A7,
      #$00A8, #$00A9, #$F8F9, #$00AB, #$00AC, #$00AD, #$00AE, #$2015,
      #$00B0, #$00B1, #$00B2, #$00B3, #$0384, #$00B5, #$00B6, #$00B7,
      #$0388, #$0389, #$038A, #$00BB, #$038C, #$00BD, #$038E, #$038F,
      #$0390, #$0391, #$0392, #$0393, #$0394, #$0395, #$0396, #$0397, 
      #$0398, #$0399, #$039A, #$039B, #$039C, #$039D, #$039E, #$039F, 
      #$03A0, #$03A1, #$F8FA, #$03A3, #$03A4, #$03A5, #$03A6, #$03A7, 
      #$03A8, #$03A9, #$03AA, #$03AB, #$03AC, #$03AD, #$03AE, #$03AF,
      #$03B0, #$03B1, #$03B2, #$03B3, #$03B4, #$03B5, #$03B6, #$03B7,
      #$03B8, #$03B9, #$03BA, #$03BB, #$03BC, #$03BD, #$03BE, #$03BF,
      #$03C0, #$03C1, #$03C2, #$03C3, #$03C4, #$03C5, #$03C6, #$03C7,
      #$03C8, #$03C9, #$03CA, #$03CB, #$03CC, #$03CD, #$03CE, #$F8FB);

function TWindows1253Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1253Map[Ord(P)];
end;

function TWindows1253Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1253Map, 'Windows-1253');
end;



{                                                                              }
{ Windows-1254                                                                 }
{                                                                              }
class function TWindows1254Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1254;
end;

const
  Windows1254Map : AnsiCharHighMap = (
      #$20AC, #$0081, #$201A, #$0192, #$201E, #$2026, #$2020, #$2021,
      #$02C6, #$2030, #$0160, #$2039, #$0152, #$008D, #$008E, #$008F,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$02DC, #$2122, #$0161, #$203A, #$0153, #$009D, #$009E, #$0178,
      #$00A0, #$00A1, #$00A2, #$00A3, #$00A4, #$00A5, #$00A6, #$00A7,
      #$00A8, #$00A9, #$00AA, #$00AB, #$00AC, #$00AD, #$00AE, #$00AF,
      #$00B0, #$00B1, #$00B2, #$00B3, #$00B4, #$00B5, #$00B6, #$00B7,
      #$00B8, #$00B9, #$00BA, #$00BB, #$00BC, #$00BD, #$00BE, #$00BF,
      #$00C0, #$00C1, #$00C2, #$00C3, #$00C4, #$00C5, #$00C6, #$00C7, 
      #$00C8, #$00C9, #$00CA, #$00CB, #$00CC, #$00CD, #$00CE, #$00CF, 
      #$011E, #$00D1, #$00D2, #$00D3, #$00D4, #$00D5, #$00D6, #$00D7,
      #$00D8, #$00D9, #$00DA, #$00DB, #$00DC, #$0130, #$015E, #$00DF, 
      #$00E0, #$00E1, #$00E2, #$00E3, #$00E4, #$00E5, #$00E6, #$00E7, 
      #$00E8, #$00E9, #$00EA, #$00EB, #$00EC, #$00ED, #$00EE, #$00EF,
      #$011F, #$00F1, #$00F2, #$00F3, #$00F4, #$00F5, #$00F6, #$00F7,
      #$00F8, #$00F9, #$00FA, #$00FB, #$00FC, #$0131, #$015F, #$00FF);

function TWindows1254Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1254Map[Ord(P)];
end;

function TWindows1254Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1254Map, 'Windows-1254');
end;



{                                                                              }
{ Windows-1255                                                                 }
{                                                                              }
class function TWindows1255Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1255;
end;

const
  Windows1255Map : AnsiCharHighMap = (
      #$20AC, #$0081, #$201A, #$0192, #$201E, #$2026, #$2020, #$2021,
      #$02C6, #$2030, #$008A, #$2039, #$008C, #$008D, #$008E, #$008F,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$02DC, #$2122, #$009A, #$203A, #$009C, #$009D, #$009E, #$009F,
      #$00A0, #$00A1, #$00A2, #$00A3, #$20AA, #$00A5, #$00A6, #$00A7,
      #$00A8, #$00A9, #$00D7, #$00AB, #$00AC, #$00AD, #$00AE, #$00AF,
      #$00B0, #$00B1, #$00B2, #$00B3, #$00B4, #$00B5, #$00B6, #$00B7,
      #$00B8, #$00B9, #$00F7, #$00BB, #$00BC, #$00BD, #$00BE, #$00BF,
      #$05B0, #$05B1, #$05B2, #$05B3, #$05B4, #$05B5, #$05B6, #$05B7,
      #$05B8, #$05B9, #$05BA, #$05BB, #$05BC, #$05BD, #$05BE, #$05BF, 
      #$05C0, #$05C1, #$05C2, #$05C3, #$05F0, #$05F1, #$05F2, #$05F3, 
      #$05F4, #$F88D, #$F88E, #$F88F, #$F890, #$F891, #$F892, #$F893,
      #$05D0, #$05D1, #$05D2, #$05D3, #$05D4, #$05D5, #$05D6, #$05D7, 
      #$05D8, #$05D9, #$05DA, #$05DB, #$05DC, #$05DD, #$05DE, #$05DF,
      #$05E0, #$05E1, #$05E2, #$05E3, #$05E4, #$05E5, #$05E6, #$05E7, 
      #$05E8, #$05E9, #$05EA, #$F894, #$F895, #$200E, #$200F, #$F896);

function TWindows1255Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1255Map[Ord(P)];
end;

function TWindows1255Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1255Map, 'Windows-1255');
end;



{                                                                              }
{ Windows-1256                                                                 }
{                                                                              }
class function TWindows1256Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1256;
end;

const
  Windows1256Map : AnsiCharHighMap = (
      #$20AC, #$067E, #$201A, #$0192, #$201E, #$2026, #$2020, #$2021,
      #$02C6, #$2030, #$0679, #$2039, #$0152, #$0686, #$0698, #$0688,
      #$06AF, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$06A9, #$2122, #$0691, #$203A, #$0153, #$200C, #$200D, #$06BA,
      #$00A0, #$060C, #$00A2, #$00A3, #$00A4, #$00A5, #$00A6, #$00A7,
      #$00A8, #$00A9, #$06BE, #$00AB, #$00AC, #$00AD, #$00AE, #$00AF,
      #$00B0, #$00B1, #$00B2, #$00B3, #$00B4, #$00B5, #$00B6, #$00B7,
      #$00B8, #$00B9, #$061B, #$00BB, #$00BC, #$00BD, #$00BE, #$061F,
      #$06C1, #$0621, #$0622, #$0623, #$0624, #$0625, #$0626, #$0627, 
      #$0628, #$0629, #$062A, #$062B, #$062C, #$062D, #$062E, #$062F,
      #$0630, #$0631, #$0632, #$0633, #$0634, #$0635, #$0636, #$00D7, 
      #$0637, #$0638, #$0639, #$063A, #$0640, #$0641, #$0642, #$0643, 
      #$00E0, #$0644, #$00E2, #$0645, #$0646, #$0647, #$0648, #$00E7, 
      #$00E8, #$00E9, #$00EA, #$00EB, #$0649, #$064A, #$00EE, #$00EF,
      #$064B, #$064C, #$064D, #$064E, #$00F4, #$064F, #$0650, #$00F7,
      #$0651, #$00F9, #$0652, #$00FB, #$00FC, #$200E, #$200F, #$06D2);

function TWindows1256Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1256Map[Ord(P)];
end;

function TWindows1256Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1256Map, 'Windows-1256');
end;



{                                                                              }
{ Windows-1257                                                                 }
{                                                                              }
class function TWindows1257Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1257;
end;

const
  Windows1257Map : AnsiCharHighMap = (
      #$20AC, #$0081, #$201A, #$0083, #$201E, #$2026, #$2020, #$2021,
      #$0088, #$2030, #$008A, #$2039, #$008C, #$00A8, #$02C7, #$00B8,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$0098, #$2122, #$009A, #$203A, #$009C, #$00AF, #$02DB, #$009F,
      #$00A0, #$F8FC, #$00A2, #$00A3, #$00A4, #$F8FD, #$00A6, #$00A7,
      #$00D8, #$00A9, #$0156, #$00AB, #$00AC, #$00AD, #$00AE, #$00C6, 
      #$00B0, #$00B1, #$00B2, #$00B3, #$00B4, #$00B5, #$00B6, #$00B7, 
      #$00F8, #$00B9, #$0157, #$00BB, #$00BC, #$00BD, #$00BE, #$00E6, 
      #$0104, #$012E, #$0100, #$0106, #$00C4, #$00C5, #$0118, #$0112, 
      #$010C, #$00C9, #$0179, #$0116, #$0122, #$0136, #$012A, #$013B,
      #$0160, #$0143, #$0145, #$00D3, #$014C, #$00D5, #$00D6, #$00D7, 
      #$0172, #$0141, #$015A, #$016A, #$00DC, #$017B, #$017D, #$00DF, 
      #$0105, #$012F, #$0101, #$0107, #$00E4, #$00E5, #$0119, #$0113, 
      #$010D, #$00E9, #$017A, #$0117, #$0123, #$0137, #$012B, #$013C, 
      #$0161, #$0144, #$0146, #$00F3, #$014D, #$00F5, #$00F6, #$00F7,
      #$0173, #$0142, #$015B, #$016B, #$00FC, #$017C, #$017E, #$02D9);

function TWindows1257Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1257Map[Ord(P)];
end;

function TWindows1257Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1257Map, 'Windows-1257');
end;



{                                                                              }
{ Windows-1258                                                                 }
{                                                                              }
class function TWindows1258Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoWindows1258;
end;

const
  Windows1258Map : AnsiCharHighMap = (
      #$20AC, #$0081, #$201A, #$0192, #$201E, #$2026, #$2020, #$2021,
      #$02C6, #$2030, #$008A, #$2039, #$0152, #$008D, #$008E, #$008F,
      #$0090, #$2018, #$2019, #$201C, #$201D, #$2022, #$2013, #$2014,
      #$02DC, #$2122, #$009A, #$203A, #$0153, #$009D, #$009E, #$0178,
      #$00A0, #$00A1, #$00A2, #$00A3, #$00A4, #$00A5, #$00A6, #$00A7,
      #$00A8, #$00A9, #$00AA, #$00AB, #$00AC, #$00AD, #$00AE, #$00AF,
      #$00B0, #$00B1, #$00B2, #$00B3, #$00B4, #$00B5, #$00B6, #$00B7,
      #$00B8, #$00B9, #$00BA, #$00BB, #$00BC, #$00BD, #$00BE, #$00BF,
      #$00C0, #$00C1, #$00C2, #$0102, #$00C4, #$00C5, #$00C6, #$00C7,
      #$00C8, #$00C9, #$00CA, #$00CB, #$0300, #$00CD, #$00CE, #$00CF,
      #$0110, #$00D1, #$0309, #$00D3, #$00D4, #$01A0, #$00D6, #$00D7,
      #$00D8, #$00D9, #$00DA, #$00DB, #$00DC, #$01AF, #$0303, #$00DF,
      #$00E0, #$00E1, #$00E2, #$0103, #$00E4, #$00E5, #$00E6, #$00E7,
      #$00E8, #$00E9, #$00EA, #$00EB, #$0301, #$00ED, #$00EE, #$00EF,
      #$0111, #$00F1, #$0323, #$00F3, #$00F4, #$01A1, #$00F6, #$00F7,
      #$00F8, #$00F9, #$00FA, #$00FB, #$00FC, #$01B0, #$20AB, #$00FF);

function TWindows1258Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := Windows1258Map[Ord(P)];
end;

function TWindows1258Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, Windows1258Map, 'Windows-1258');
end;



{                                                                              }
{ Mac Latin-2                                                                  }
{                                                                              }
const
  MacLatin2Map : AnsiCharHighMap = (
    #$00C4, #$0100, #$0101, #$00C9, #$0104, #$00D6, #$00DC, #$00E1,
    #$0105, #$010C, #$00E4, #$010D, #$0106, #$0107, #$00E9, #$0179,
    #$017A, #$010E, #$00ED, #$010F, #$0112, #$0113, #$0116, #$00F3,
    #$0117, #$00F4, #$00F6, #$00F5, #$00FA, #$011A, #$011B, #$00FC,
    #$2020, #$00B0, #$0118, #$00A3, #$00A7, #$2022, #$00B6, #$00DF,
    #$00AE, #$00A9, #$2122, #$0119, #$00A8, #$2260, #$0123, #$012E,
    #$012F, #$012A, #$2264, #$2265, #$012B, #$0136, #$2202, #$2211,
    #$0142, #$013B, #$013C, #$013D, #$013E, #$0139, #$013A, #$0145,
    #$0146, #$0143, #$00AC, #$221A, #$0144, #$0147, #$2206, #$00AB,
    #$00BB, #$2026, #$00A0, #$0148, #$0150, #$00D5, #$0151, #$014C,
    #$2013, #$2014, #$201C, #$201D, #$2018, #$2019, #$00F7, #$25CA,
    #$014D, #$0154, #$0155, #$0158, #$2039, #$203A, #$0159, #$0156,
    #$0157, #$0160, #$201A, #$201E, #$0161, #$015A, #$015B, #$00C1,
    #$0164, #$0165, #$00CD, #$017D, #$017E, #$016A, #$00D3, #$00D4,
    #$016B, #$016E, #$00DA, #$016F, #$0170, #$0171, #$0172, #$0173,
    #$00DD, #$00FD, #$0137, #$017B, #$0141, #$017C, #$0122, #$02C7);

function TMacLatin2Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := MacLatin2Map[Ord(P)];
end;

function TMacLatin2Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, MacLatin2Map, 'MacLatin2');
end;

class function TMacLatin2Codec.Name: string;
begin
  Result := 'MacLatin2';
end;



{                                                                              }
{ Mac Roman                                                                    }
{                                                                              }
const
  MacRomanMap : AnsiCharHighMap = (
    #$00C4, #$00C5, #$00C7, #$00C9, #$00D1, #$00D6, #$00DC, #$00E1,
    #$00E0, #$00E2, #$00E4, #$00E3, #$00E5, #$00E7, #$00E9, #$00E8,
    #$00EA, #$00EB, #$00ED, #$00EC, #$00EE, #$00EF, #$00F1, #$00F3,
    #$00F2, #$00F4, #$00F6, #$00F5, #$00FA, #$00F9, #$00FB, #$00FC,
    #$2020, #$00B0, #$00A2, #$00A3, #$00A7, #$2022, #$00B6, #$00DF,
    #$00AE, #$00A9, #$2122, #$00B4, #$00A8, #$2260, #$00C6, #$00D8,
    #$221E, #$00B1, #$2264, #$2265, #$00A5, #$00B5, #$2202, #$2211,
    #$220F, #$03C0, #$222B, #$00AA, #$00BA, #$2126, #$00E6, #$00F8,
    #$00BF, #$00A1, #$00AC, #$221A, #$0192, #$2248, #$2206, #$00AB,
    #$00BB, #$2026, #$00A0, #$00C0, #$00C3, #$00D5, #$0152, #$0153,
    #$2013, #$2014, #$201C, #$201D, #$2018, #$2019, #$00F7, #$25CA,
    #$00FF, #$0178, #$2044, #$00A4, #$2039, #$203A, #$FB01, #$FB02,
    #$2021, #$00B7, #$201A, #$201E, #$2030, #$00C2, #$00CA, #$00C1,
    #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, #$00CC, #$00D3, #$00D4,
    #$FFFF, #$00D2, #$00DA, #$00DB, #$00D9, #$0131, #$02C6, #$02DC,
    #$00AF, #$02D8, #$02D9, #$02DA, #$00B8, #$02DD, #$02DB, #$02C7);

function TMacRomanCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := MacRomanMap[Ord(P)];
end;

function TMacRomanCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, MacRomanMap, 'MacRoman');
end;

class function TMacRomanCodec.Name: string;
begin
  Result := 'MacRoman';
end;



{                                                                              }
{ Mac Cyrillic                                                                 }
{                                                                              }
const
  MacCyrillicMap : AnsiCharHighMap = (
    #$0410, #$0411, #$0412, #$0413, #$0414, #$0415, #$0416, #$0417,
    #$0418, #$0419, #$041A, #$041B, #$041C, #$041D, #$041E, #$041F,
    #$0420, #$0421, #$0422, #$0423, #$0424, #$0425, #$0426, #$0427,
    #$0428, #$0429, #$042A, #$042B, #$042C, #$042D, #$042E, #$042F,
    #$2020, #$00B0, #$00A2, #$00A3, #$00A7, #$2022, #$00B6, #$0406,
    #$00AE, #$00A9, #$2122, #$0402, #$0452, #$2260, #$0403, #$0453,
    #$221E, #$00B1, #$2264, #$2265, #$0456, #$00B5, #$2202, #$0408,
    #$0404, #$0454, #$0407, #$0457, #$0409, #$0459, #$040A, #$045A,
    #$0458, #$0405, #$00AC, #$221A, #$0192, #$2248, #$2206, #$00AB,
    #$00BB, #$2026, #$00A0, #$040B, #$045B, #$040C, #$045C, #$0455,
    #$2013, #$2014, #$201C, #$201D, #$2018, #$2019, #$00F7, #$201E,
    #$040E, #$045E, #$040F, #$045F, #$2116, #$0401, #$0451, #$044F,
    #$0430, #$0431, #$0432, #$0433, #$0434, #$0435, #$0436, #$0437,
    #$0438, #$0439, #$043A, #$043B, #$043C, #$043D, #$043E, #$043F,
    #$0440, #$0441, #$0442, #$0443, #$0444, #$0445, #$0446, #$0447,
    #$0448, #$0449, #$044A, #$044B, #$044C, #$044D, #$044E, #$00A4);

function TMacCyrillicCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := MacCyrillicMap[Ord(P)];
end;

function TMacCyrillicCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, MacCyrillicMap, 'MacCyrillic');
end;

class function TMacCyrillicCodec.Name: string;
begin
  Result := 'MacCyrillic';
end;



{                                                                              }
{ Mac Greek                                                                    }
{                                                                              }
const
  MacGreekMap : AnsiCharHighMap = (
    #$00C4, #$00B9, #$00B2, #$00C9, #$00B3, #$00D6, #$00DC, #$0385,
    #$00E0, #$00E2, #$00E4, #$0384, #$00A8, #$00E7, #$00E9, #$00E8,
    #$00EA, #$00EB, #$00A3, #$2122, #$00EE, #$00EF, #$2022, #$00BD,
    #$2030, #$00F4, #$00F6, #$00A6, #$00AD, #$00F9, #$00FB, #$00FC,
    #$2020, #$0393, #$0394, #$0398, #$039B, #$039E, #$03A0, #$00DF,
    #$00AE, #$00A9, #$03A3, #$03AA, #$00A7, #$2260, #$00B0, #$0387,
    #$0391, #$00B1, #$2264, #$2265, #$00A5, #$0392, #$0395, #$0396,
    #$0397, #$0399, #$039A, #$039C, #$03A6, #$03AB, #$03A8, #$03A9,
    #$03AC, #$039D, #$00AC, #$039F, #$03A1, #$2248, #$03A4, #$00AB,
    #$00BB, #$2026, #$00A0, #$03A5, #$03A7, #$0386, #$0388, #$0153,
    #$2013, #$2015, #$201C, #$201D, #$2018, #$2019, #$00F7, #$0389,
    #$038A, #$038C, #$038E, #$03AD, #$03AE, #$03AF, #$03CC, #$038F,
    #$03CD, #$03B1, #$03B2, #$03C8, #$03B4, #$03B5, #$03C6, #$03B3,
    #$03B7, #$03B9, #$03BE, #$03BA, #$03BB, #$03BC, #$03BD, #$03BF,
    #$03C0, #$03CE, #$03C1, #$03C3, #$03C4, #$03B8, #$03C9, #$03C2,
    #$03C7, #$03C5, #$03B6, #$03CA, #$03CB, #$0390, #$03B0, #$FFFF);

function TMacGreekCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := MacGreekMap[Ord(P)];
end;

function TMacGreekCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, MacGreekMap, 'MacGreek');
end;

class function TMacGreekCodec.Name: string;
begin
  Result := 'MacGreek';
end;



{                                                                              }
{ Mac Icelandic                                                                }
{                                                                              }
const
  MacIcelandicMap : AnsiCharHighMap = (
    #$00C4, #$00C5, #$00C7, #$00C9, #$00D1, #$00D6, #$00DC, #$00E1,
    #$00E0, #$00E2, #$00E4, #$00E3, #$00E5, #$00E7, #$00E9, #$00E8,
    #$00EA, #$00EB, #$00ED, #$00EC, #$00EE, #$00EF, #$00F1, #$00F3,
    #$00F2, #$00F4, #$00F6, #$00F5, #$00FA, #$00F9, #$00FB, #$00FC,
    #$00DD, #$00B0, #$00A2, #$00A3, #$00A7, #$2022, #$00B6, #$00DF,
    #$00AE, #$00A9, #$2122, #$00B4, #$00A8, #$2260, #$00C6, #$00D8,
    #$221E, #$00B1, #$2264, #$2265, #$00A5, #$00B5, #$2202, #$2211,
    #$220F, #$03C0, #$222B, #$00AA, #$00BA, #$2126, #$00E6, #$00F8,
    #$00BF, #$00A1, #$00AC, #$221A, #$0192, #$2248, #$2206, #$00AB,
    #$00BB, #$2026, #$00A0, #$00C0, #$00C3, #$00D5, #$0152, #$0153,
    #$2013, #$2014, #$201C, #$201D, #$2018, #$2019, #$00F7, #$25CA,
    #$00FF, #$0178, #$2044, #$00A4, #$00D0, #$00F0, #$00DE, #$00FE,
    #$00FD, #$00B7, #$201A, #$201E, #$2030, #$00C2, #$00CA, #$00C1,
    #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, #$00CC, #$00D3, #$00D4,
    #$FFFF, #$00D2, #$00DA, #$00DB, #$00D9, #$0131, #$02C6, #$02DC,
    #$00AF, #$02D8, #$02D9, #$02DA, #$00B8, #$02DD, #$02DB, #$02C7);

function TMacIcelandicCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := MacIcelandicMap[Ord(P)];
end;

function TMacIcelandicCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, MacIcelandicMap, 'MacIcelandic');
end;

class function TMacIcelandicCodec.Name: string;
begin
  Result := 'MacIcelandic';
end;



{                                                                              }
{ Mac Turkish                                                                  }
{                                                                              }
const
  MacTurkishMap : AnsiCharHighMap = (
    #$00C4, #$00C5, #$00C7, #$00C9, #$00D1, #$00D6, #$00DC, #$00E1,
    #$00E0, #$00E2, #$00E4, #$00E3, #$00E5, #$00E7, #$00E9, #$00E8,
    #$00EA, #$00EB, #$00ED, #$00EC, #$00EE, #$00EF, #$00F1, #$00F3,
    #$00F2, #$00F4, #$00F6, #$00F5, #$00FA, #$00F9, #$00FB, #$00FC,
    #$2020, #$00B0, #$00A2, #$00A3, #$00A7, #$2022, #$00B6, #$00DF,
    #$00AE, #$00A9, #$2122, #$00B4, #$00A8, #$2260, #$00C6, #$00D8,
    #$221E, #$00B1, #$2264, #$2265, #$00A5, #$00B5, #$2202, #$2211,
    #$220F, #$03C0, #$222B, #$00AA, #$00BA, #$2126, #$00E6, #$00F8,
    #$00BF, #$00A1, #$00AC, #$221A, #$0192, #$2248, #$2206, #$00AB,
    #$00BB, #$2026, #$00A0, #$00C0, #$00C3, #$00D5, #$0152, #$0153,
    #$2013, #$2014, #$201C, #$201D, #$2018, #$2019, #$00F7, #$25CA,
    #$00FF, #$0178, #$011E, #$011F, #$0130, #$0131, #$015E, #$015F,
    #$2021, #$00B7, #$201A, #$201E, #$2030, #$00C2, #$00CA, #$00C1,
    #$00CB, #$00C8, #$00CD, #$00CE, #$00CF, #$00CC, #$00D3, #$00D4,
    #$FFFF, #$00D2, #$00DA, #$00DB, #$00D9, #$FFFF, #$02C6, #$02DC,
    #$00AF, #$02D8, #$02D9, #$02DA, #$00B8, #$02DD, #$02DB, #$02C7);

function TMacTurkishCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := MacTurkishMap[Ord(P)];
end;

function TMacTurkishCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, MacTurkishMap, 'MacTurkish');
end;

class function TMacTurkishCodec.Name: string;
begin
  Result := 'MacTurkish';
end;



{                                                                              }
{ US-ASCII                                                                     }
{                                                                              }
class function TUSASCIICodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoAscii;
end;

class function TUSASCIICodec.CheckA(const S: AnsiString): Boolean;
var
  I: Integer;
  P: PAnsiChar;
begin
  P := Pointer(S);
  for I := 1 to Length(S) do
    if Ord(P^) >= $80 then
      begin
        Result := False;
        Exit;
      end
    else
      Inc(P);
  Result := True;
end;

class function TUSASCIICodec.CheckW(const S: WideString): Boolean;
var
  I: Integer;
  P: PWideChar;
begin
  P := Pointer(S);
  for I := 1 to Length(S) do
    if Ord(P^) >= $80 then
      begin
        Result := False;
        Exit;
      end
    else
      Inc(P);
  Result := True;
end;

function TUSASCIICodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    raise EConvertError.CreateFmt(SInvalidCodePoint, [Ord(P), 'US-ASCII']);
end;

function TUSASCIICodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  if Ord(Ch) < $80 then
    Result := AnsiChar(Ch)
  else
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'US-ASCII']);
end;



{                                                                              }
{ EBCDIC-US                                                                    }
{                                                                              }
class function TEBCDIC_USCodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoEBCDICUS;
end;

function TEBCDIC_USCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $40      : Result := #$0020;                         // SPACE
    $4A      : Result := #$00A2;                         // CENT SIGN
    $4B      : Result := #$002E;                         // FULL STOP
    $4C      : Result := #$003C;                         // LESS-THAN SIGN
    $4D      : Result := #$0028;                         // LEFT PARENTHESIS
    $4E      : Result := #$002B;                         // PLUS SIGN
    $4F      : Result := #$007C;                         // VERTICAL LINE
    $50      : Result := #$0026;                         // AMPERSAND
    $5A      : Result := #$0021;                         // EXCLAMATION MARK
    $5B      : Result := #$0024;                         // DOLLAR SIGN
    $5C      : Result := #$002A;                         // ASTERISK
    $5D      : Result := #$0029;                         // RIGHT PARENTHESIS
    $5E      : Result := #$003B;                         // SEMICOLON
    $5F      : Result := #$00AC;                         // NOT SIGN
    $60      : Result := #$002D;                         // HYPHEN-MINUS
    $61      : Result := #$002F;                         // SOLIDUS
    $6A      : Result := #$00A6;                         // BROKEN BAR
    $6B      : Result := #$002C;                         // COMMA
    $6C      : Result := #$0025;                         // PERCENT SIGN
    $6D      : Result := #$005F;                         // LOW LINE
    $6E      : Result := #$003E;                         // GREATER-THAN SIGN
    $6F      : Result := #$003F;                         // QUESTION MARK
    $79      : Result := #$0060;                         // GRAVE ACCENT
    $7A      : Result := #$003A;                         // COLON
    $7B      : Result := #$0023;                         // NUMBER SIGN
    $7C      : Result := #$0040;                         // COMMERCIAL AT
    $7D      : Result := #$0027;                         // APOSTROPHE
    $7E      : Result := #$003D;                         // EQUALS SIGN
    $7F      : Result := #$0022;                         // QUOTATION MARK
    $81..$89 : Result := WideChar(Ord(P) - $81 + $0061); // LATIN SMALL LETTER A..I
    $91..$99 : Result := WideChar(Ord(P) - $91 + $006A); // LATIN SMALL LETTER J..R
    $A1      : Result := #$007E;                         // TILDE
    $A2..$A9 : Result := WideChar(Ord(P) - $A2 + $0073); // LATIN SMALL LETTER S..Z
    $C0      : Result := #$007B;                         // LEFT CURLY BRACKET
    $C1..$C9 : Result := WideChar(Ord(P) - $C1 + $0041); // LATIN CAPITAL LETTER A..I
    $D0      : Result := #$007D;                         // RIGHT CURLY BRACKET
    $D1..$D9 : Result := WideChar(Ord(P) - $D1 + $004A); // LATIN CAPITAL LETTER J..R
    $E0      : Result := #$005C;                         // REVERSE SOLIDUS
    $E2..$E9 : Result := WideChar(Ord(P) - $E2 + $0053); // LATIN CAPITAL LETTER S
    $F0..$F9 : Result := WideChar(Ord(P) - $F0 + $0030); // DIGIT ZERO
  else
    Result := #$FFFF;
  end;
end;

function TEBCDIC_USCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $0020        : Result := #$40;                            // SPACE
    $0021        : Result := #$5A;                            // EXCLAMATION MARK
    $0022        : Result := #$7F;                            // QUOTATION MARK
    $0023        : Result := #$7B;                            // NUMBER SIGN
    $0024        : Result := #$5B;                            // DOLLAR SIGN
    $0025        : Result := #$6C;                            // PERCENT SIGN
    $0026        : Result := #$50;                            // AMPERSAND
    $0027        : Result := #$7D;                            // APOSTROPHE
    $0028        : Result := #$4D;                            // LEFT PARENTHESIS
    $0029        : Result := #$5D;                            // RIGHT PARENTHESIS
    $002A        : Result := #$5C;                            // ASTERISK
    $002B        : Result := #$4E;                            // PLUS SIGN
    $002C        : Result := #$6B;                            // COMMA
    $002D        : Result := #$60;                            // HYPHEN-MINUS
    $002E        : Result := #$4B;                            // FULL STOP
    $002F        : Result := #$61;                            // SOLIDUS
    $0030..$0039 : Result := AnsiChar(Ord(Ch) - $0030 + $F0); // DIGIT ZERO-NINE
    $003A        : Result := #$7A;                            // COLON
    $003B        : Result := #$5E;                            // SEMICOLON
    $003C        : Result := #$4C;                            // LESS-THAN SIGN
    $003D        : Result := #$7E;                            // EQUALS SIGN
    $003E        : Result := #$6E;                            // GREATER-THAN SIGN
    $003F        : Result := #$6F;                            // QUESTION MARK
    $0040        : Result := #$7C;                            // COMMERCIAL AT
    $0041..$0049 : Result := AnsiChar(Ord(Ch) - $0041 + $C1); // LATIN CAPITAL LETTER A..I
    $004A..$0052 : Result := AnsiChar(Ord(Ch) - $004A + $D1); // LATIN CAPITAL LETTER J..R
    $0053..$005A : Result := AnsiChar(Ord(Ch) - $0053 + $E2); // LATIN CAPITAL LETTER S..Z
    $005C        : Result := #$E0;                            // REVERSE SOLIDUS
    $005F        : Result := #$6D;                            // LOW LINE
    $0060        : Result := #$79;                            // GRAVE ACCENT
    $0061..$0069 : Result := AnsiChar(Ord(Ch) - $0061 + $81); // LATIN SMALL LETTER A..I
    $006A..$0072 : Result := AnsiChar(Ord(Ch) - $006A + $91); // LATIN SMALL LETTER J..R
    $0073..$007A : Result := AnsiChar(Ord(Ch) - $0073 + $A2); // LATIN SMALL LETTER S..Z
    $007B        : Result := #$C0;                            // LEFT CURLY BRACKET
    $007C        : Result := #$4F;                            // VERTICAL LINE
    $007D        : Result := #$D0;                            // RIGHT CURLY BRACKET
    $007E        : Result := #$A1;                            // TILDE
    $00A2        : Result := #$4A;                            // CENT SIGN
    $00A6        : Result := #$6A;                            // BROKEN BAR
    $00AC        : Result := #$5F;                            // NOT SIGN
  else
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'EBCDIC-US']);
  end;
end;



{                                                                              }
{ KOI8-R                                                                       }
{                                                                              }
class function TKOI8_RCodec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoKOI8R;
end;

const
  KOI8_RMap : AnsiCharHighMap = (
    #$2500, #$2502, #$250C, #$2510, #$2514, #$2518, #$251C, #$2524,
    #$252C, #$2534, #$253C, #$2580, #$2584, #$2588, #$258C, #$2590,
    #$2591, #$2592, #$2593, #$2320, #$25A0, #$2219, #$221A, #$2248,
    #$2264, #$2265, #$00A0, #$2321, #$00B0, #$00B2, #$00B7, #$00F7,
    #$2550, #$2551, #$2552, #$0451, #$2553, #$2554, #$2555, #$2556,
    #$2557, #$2558, #$2559, #$255A, #$255B, #$255C, #$255D, #$255E,
    #$255F, #$2560, #$2561, #$0401, #$2562, #$2563, #$2564, #$2565,
    #$2566, #$2567, #$2568, #$2569, #$256A, #$256B, #$256C, #$00A9,
    #$044E, #$0430, #$0431, #$0446, #$0434, #$0435, #$0444, #$0433,
    #$0445, #$0438, #$0439, #$043A, #$043B, #$043C, #$043D, #$043E,
    #$043F, #$044F, #$0440, #$0441, #$0442, #$0443, #$0436, #$0432,
    #$044C, #$044B, #$0437, #$0448, #$044D, #$0449, #$0447, #$044A,
    #$042E, #$0410, #$0411, #$0426, #$0414, #$0415, #$0424, #$0413,
    #$0425, #$0418, #$0419, #$041A, #$041B, #$041C, #$041D, #$041E,
    #$041F, #$042F, #$0420, #$0421, #$0422, #$0423, #$0416, #$0412,
    #$042C, #$042B, #$0417, #$0428, #$042D, #$0429, #$0427, #$042A);

function TKOI8_RCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := KOI8_RMap[Ord(P)];
end;

function TKOI8_RCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  Result := CharFromHighMap(Ch, KOI8_RMap, 'KOI8-R');
end;



{                                                                              }
{ JIS_X0201                                                                    }
{                                                                              }
class function TJIS_X0201Codec.EncodingInfoClass: TEncodingInfoClass;
begin
  Result := TEncodingInfoHalfWidthKatakana;
end;

function TJIS_X0201Codec.DecodeChar(const P: AnsiChar): WideChar;
begin
  case Ord(P) of
    $20..$5B,
    $5D..$7D  : Result := WideChar(P);
    $5C       : Result := #$00A5;  //  YEN SIGN
    $7E       : Result := #$203E;  //  OVERLINE
    $A1..$DF  : Result := WideChar(Ord(P) + $FEC0);
  else
    Result := #$FFFF; // <not a character>
  end;
end;

function TJIS_X0201Codec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $0020..$005B,
    $005D..$007D  : Result := AnsiChar(Ch);
    $00A5         : Result := #$5C;  //  YEN SIGN
    $203E         : Result := #$7E;  //  OVERLINE
    $FF61..$FF9F  : Result := AnsiChar(Ord(Ch) - $FEC0);
  else
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'JIS_X0201']);
  end;
end;



{                                                                              }
{ NextStep                                                                     }
{                                                                              }
const
  NextStepMap : AnsiCharHighMap = (
    #$00A0,  //  NO-BREAK SPACE
    #$00C0,  //  LATIN CAPITAL LETTER A WITH GRAVE
    #$00C1,  //  LATIN CAPITAL LETTER A WITH ACUTE
    #$00C2,  //  LATIN CAPITAL LETTER A WITH CIRCUMFLEX
    #$00C3,  //  LATIN CAPITAL LETTER A WITH TILDE
    #$00C4,  //  LATIN CAPITAL LETTER A WITH DIAERESIS
    #$00C5,  //  LATIN CAPITAL LETTER A WITH RING
    #$00C7,  //  LATIN CAPITAL LETTER C WITH CEDILLA
    #$00C8,  //  LATIN CAPITAL LETTER E WITH GRAVE
    #$00C9,  //  LATIN CAPITAL LETTER E WITH ACUTE
    #$00CA,  //  LATIN CAPITAL LETTER E WITH CIRCUMFLEX
    #$00CB,  //  LATIN CAPITAL LETTER E WITH DIAERESIS
    #$00CC,  //  LATIN CAPITAL LETTER I WITH GRAVE
    #$00CD,  //  LATIN CAPITAL LETTER I WITH ACUTE
    #$00CE,  //  LATIN CAPITAL LETTER I WITH CIRCUMFLEX
    #$00CF,  //  LATIN CAPITAL LETTER I WITH DIAERESIS
    #$00D0,  //  LATIN CAPITAL LETTER ETH
    #$00D1,  //  LATIN CAPITAL LETTER N WITH TILDE
    #$00D2,  //  LATIN CAPITAL LETTER O WITH GRAVE
    #$00D3,  //  LATIN CAPITAL LETTER O WITH ACUTE
    #$00D4,  //  LATIN CAPITAL LETTER O WITH CIRCUMFLEX
    #$00D5,  //  LATIN CAPITAL LETTER O WITH TILDE
    #$00D6,  //  LATIN CAPITAL LETTER O WITH DIAERESIS
    #$00D9,  //  LATIN CAPITAL LETTER U WITH GRAVE
    #$00DA,  //  LATIN CAPITAL LETTER U WITH ACUTE
    #$00DB,  //  LATIN CAPITAL LETTER U WITH CIRCUMFLEX
    #$00DC,  //  LATIN CAPITAL LETTER U WITH DIAERESIS
    #$00DD,  //  LATIN CAPITAL LETTER Y WITH ACUTE
    #$00DE,  //  LATIN CAPITAL LETTER THORN
    #$00B5,  //  MICRO SIGN
    #$00D7,  //  MULTIPLICATION SIGN
    #$00F7,  //  DIVISION SIGN
    #$00A9,  //  COPYRIGHT SIGN
    #$00A1,
    #$00A2,
    #$00A3,
    #$2044,  //  FRACTION SLASH
    #$00A5,
    #$0192,  //  LATIN SMALL LETTER F WITH HOOK
    #$00A7,
    #$00A4,  //  CURRENCY SIGN
    #$2019,  //  RIGHT SINGLE QUOTATION MARK
    #$201C,  //  LEFT DOUBLE QUOTATION MARK
    #$00AB,
    #$2039,  //  LATIN SMALL LETTER
    #$203A,  //  LATIN SMALL LETTER
    #$FB01,  //  LATIN SMALL LIGATURE FI
    #$FB02,  //  LATIN SMALL LIGATURE FL
    #$00AE,  //  REGISTERED SIGN
    #$2013,  //  EN DASH
    #$2020,  //  DAGGER
    #$2021,  //  DOUBLE DAGGER
    #$00B7,  //  MIDDLE DOT
    #$00A6,  //  BROKEN BAR
    #$00B6,
    #$2022,  //  BULLET
    #$201A,  //  SINGLE LOW-9 QUOTATION MARK
    #$201E,  //  DOUBLE LOW-9 QUOTATION MARK
    #$201D,  //  RIGHT DOUBLE QUOTATION MARK
    #$00BB,
    #$2026,  //  HORIZONTAL ELLIPSIS
    #$2030,  //  PER MILLE SIGN
    #$00AC,  //  NOT SIGN
    #$00BF,
    #$00B9,  //  SUPERSCRIPT ONE
    #$02CB,  //  MODIFIER LETTER GRAVE ACCENT
    #$00B4,  //  ACUTE ACCENT
    #$02C6,  //  MODIFIER LETTER CIRCUMFLEX ACCENT
    #$02DC,  //  SMALL TILDE
    #$00AF,  //  MACRON
    #$02D8,  //  BREVE
    #$02D9,  //  DOT ABOVE
    #$00A8,  //  DIAERESIS
    #$00B2,  //  SUPERSCRIPT TWO
    #$02DA,  //  RING ABOVE
    #$00B8,  //  CEDILLA
    #$00B3,  //  SUPERSCRIPT THREE
    #$02DD,  //  DOUBLE ACUTE ACCENT
    #$02DB,  //  OGONEK
    #$02C7,  //  CARON
    #$2014,  //  EM DASH
    #$00B1,  //  PLUS-MINUS SIGN
    #$00BC,  //  VULGAR FRACTION ONE QUARTER
    #$00BD,  //  VULGAR FRACTION ONE HALF
    #$00BE,  //  VULGAR FRACTION THREE QUARTERS
    #$00E0,  //  LATIN SMALL LETTER A WITH GRAVE
    #$00E1,  //  LATIN SMALL LETTER A WITH ACUTE
    #$00E2,  //  LATIN SMALL LETTER A WITH CIRCUMFLEX
    #$00E3,  //  LATIN SMALL LETTER A WITH TILDE
    #$00E4,  //  LATIN SMALL LETTER A WITH DIAERESIS
    #$00E5,  //  LATIN SMALL LETTER A WITH RING ABOVE
    #$00E7,  //  LATIN SMALL LETTER C WITH CEDILLA
    #$00E8,  //  LATIN SMALL LETTER E WITH GRAVE
    #$00E9,  //  LATIN SMALL LETTER E WITH ACUTE
    #$00EA,  //  LATIN SMALL LETTER E WITH CIRCUMFLEX
    #$00EB,  //  LATIN SMALL LETTER E WITH DIAERESIS
    #$00EC,  //  LATIN SMALL LETTER I WITH GRAVE
    #$00C6,  //  LATIN CAPITAL LETTER AE
    #$00ED,  //  LATIN SMALL LETTER I WITH ACUTE
    #$00AA,  //  FEMININE ORDINAL INDICATOR
    #$00EE,  //  LATIN SMALL LETTER I WITH CIRCUMFLEX
    #$00EF,  //  LATIN SMALL LETTER I WITH DIAERESIS
    #$00F0,  //  LATIN SMALL LETTER ETH
    #$00F1,  //  LATIN SMALL LETTER N WITH TILDE
    #$0141,  //  LATIN CAPITAL LETTER L WITH STROKE
    #$00D8,  //  LATIN CAPITAL LETTER O WITH STROKE
    #$0152,  //  LATIN CAPITAL LIGATURE OE
    #$00BA,  //  MASCULINE ORDINAL INDICATOR
    #$00F2,  //  LATIN SMALL LETTER O WITH GRAVE
    #$00F3,  //  LATIN SMALL LETTER O WITH ACUTE
    #$00F4,  //  LATIN SMALL LETTER O WITH CIRCUMFLEX
    #$00F5,  //  LATIN SMALL LETTER O WITH TILDE
    #$00F6,  //  LATIN SMALL LETTER O WITH DIAERESIS
    #$00E6,  //  LATIN SMALL LETTER AE
    #$00F9,  //  LATIN SMALL LETTER U WITH GRAVE
    #$00FA,  //  LATIN SMALL LETTER U WITH ACUTE
    #$00FB,  //  LATIN SMALL LETTER U WITH CIRCUMFLEX
    #$0131,  //  LATIN SMALL LETTER DOTLESS I
    #$00FC,  //  LATIN SMALL LETTER U WITH DIAERESIS
    #$00FD,  //  LATIN SMALL LETTER Y WITH ACUTE
    #$0142,  //  LATIN SMALL LETTER L WITH STROKE
    #$00F8,  //  LATIN SMALL LETTER O WITH STROKE
    #$0153,  //  LATIN SMALL LIGATURE OE
    #$00DF,  //  LATIN SMALL LETTER SHARP S
    #$00FE,  //  LATIN SMALL LETTER THORN
    #$00FF,  //  LATIN SMALL LETTER Y WITH DIAERESIS
    #$FFFF,  //  Not defined
    #$FFFF   //  Not defined
  );

function TNextStepCodec.DecodeChar(const P: AnsiChar): WideChar;
begin
  if Ord(P) < $80 then
    Result := WideChar(P)
  else
    Result := NextStepMap[Ord(P)];
end;

function TNextStepCodec.EncodeChar(const Ch: WideChar): AnsiChar;
begin
  case Ord(Ch) of
    $0000..$007F,
    $00A1..$00A3,
    $00A5,
    $00A7,
    $00AB,
    $00B6,
    $00BB,
    $00BF : Result := AnsiChar(Ch);
    $00A0 : Result := #$80;  //  NO-BREAK SPACE
    $00A4 : Result := #$A8;  //  CURRENCY SIGN
    $00A6 : Result := #$B5;  //  BROKEN BAR
    $00A8 : Result := #$C8;  //  DIAERESIS
    $00A9 : Result := #$A0;  //  COPYRIGHT SIGN
    $00AA : Result := #$E3;  //  FEMININE ORDINAL INDICATOR
    $00AC : Result := #$BE;  //  NOT SIGN
    $00AE : Result := #$B0;  //  REGISTERED SIGN
    $00AF : Result := #$C5;  //  MACRON
    $00B1 : Result := #$D1;  //  PLUS-MINUS SIGN
    $00B2 : Result := #$C9;  //  SUPERSCRIPT TWO
    $00B3 : Result := #$CC;  //  SUPERSCRIPT THREE
    $00B4 : Result := #$C2;  //  ACUTE ACCENT
    $00B5 : Result := #$9D;  //  MICRO SIGN
    $00B7 : Result := #$B4;  //  MIDDLE DOT
    $00B8 : Result := #$CB;  //  CEDILLA
    $00B9 : Result := #$C0;  //  SUPERSCRIPT ONE
    $00BA : Result := #$EB;  //  MASCULINE ORDINAL INDICATOR
    $00BC : Result := #$D2;  //  VULGAR FRACTION ONE QUARTER
    $00BD : Result := #$D3;  //  VULGAR FRACTION ONE HALF
    $00BE : Result := #$D4;  //  VULGAR FRACTION THREE QUARTERS
    $00C0 : Result := #$81;  //  LATIN CAPITAL LETTER A WITH GRAVE
    $00C1 : Result := #$82;  //  LATIN CAPITAL LETTER A WITH ACUTE
    $00C2 : Result := #$83;  //  LATIN CAPITAL LETTER A WITH CIRCUMFLEX
    $00C3 : Result := #$84;  //  LATIN CAPITAL LETTER A WITH TILDE
    $00C4 : Result := #$85;  //  LATIN CAPITAL LETTER A WITH DIAERESIS
    $00C5 : Result := #$86;  //  LATIN CAPITAL LETTER A WITH RING
    $00C6 : Result := #$E1;  //  LATIN CAPITAL LETTER AE
    $00C7 : Result := #$87;  //  LATIN CAPITAL LETTER C WITH CEDILLA
    $00C8 : Result := #$88;  //  LATIN CAPITAL LETTER E WITH GRAVE
    $00C9 : Result := #$89;  //  LATIN CAPITAL LETTER E WITH ACUTE
    $00CA : Result := #$8A;  //  LATIN CAPITAL LETTER E WITH CIRCUMFLEX
    $00CB : Result := #$8B;  //  LATIN CAPITAL LETTER E WITH DIAERESIS
    $00CC : Result := #$8C;  //  LATIN CAPITAL LETTER I WITH GRAVE
    $00CD : Result := #$8D;  //  LATIN CAPITAL LETTER I WITH ACUTE
    $00CE : Result := #$8E;  //  LATIN CAPITAL LETTER I WITH CIRCUMFLEX
    $00CF : Result := #$8F;  //  LATIN CAPITAL LETTER I WITH DIAERESIS
    $00D0 : Result := #$90;  //  LATIN CAPITAL LETTER ETH
    $00D1 : Result := #$91;  //  LATIN CAPITAL LETTER N WITH TILDE
    $00D2 : Result := #$92;  //  LATIN CAPITAL LETTER O WITH GRAVE
    $00D3 : Result := #$93;  //  LATIN CAPITAL LETTER O WITH ACUTE
    $00D4 : Result := #$94;  //  LATIN CAPITAL LETTER O WITH CIRCUMFLEX
    $00D5 : Result := #$95;  //  LATIN CAPITAL LETTER O WITH TILDE
    $00D6 : Result := #$96;  //  LATIN CAPITAL LETTER O WITH DIAERESIS
    $00D7 : Result := #$9E;  //  MULTIPLICATION SIGN
    $00D8 : Result := #$E9;  //  LATIN CAPITAL LETTER O WITH STROKE
    $00D9 : Result := #$97;  //  LATIN CAPITAL LETTER U WITH GRAVE
    $00DA : Result := #$98;  //  LATIN CAPITAL LETTER U WITH ACUTE
    $00DB : Result := #$99;  //  LATIN CAPITAL LETTER U WITH CIRCUMFLEX
    $00DC : Result := #$9A;  //  LATIN CAPITAL LETTER U WITH DIAERESIS
    $00DD : Result := #$9B;  //  LATIN CAPITAL LETTER Y WITH ACUTE
    $00DE : Result := #$9C;  //  LATIN CAPITAL LETTER THORN
    $00DF : Result := #$FB;  //  LATIN SMALL LETTER SHARP S
    $00E0 : Result := #$D5;  //  LATIN SMALL LETTER A WITH GRAVE
    $00E1 : Result := #$D6;  //  LATIN SMALL LETTER A WITH ACUTE
    $00E2 : Result := #$D7;  //  LATIN SMALL LETTER A WITH CIRCUMFLEX
    $00E3 : Result := #$D8;  //  LATIN SMALL LETTER A WITH TILDE
    $00E4 : Result := #$D9;  //  LATIN SMALL LETTER A WITH DIAERESIS
    $00E5 : Result := #$DA;  //  LATIN SMALL LETTER A WITH RING ABOVE
    $00E6 : Result := #$F1;  //  LATIN SMALL LETTER AE
    $00E7 : Result := #$DB;  //  LATIN SMALL LETTER C WITH CEDILLA
    $00E8 : Result := #$DC;  //  LATIN SMALL LETTER E WITH GRAVE
    $00E9 : Result := #$DD;  //  LATIN SMALL LETTER E WITH ACUTE
    $00EA : Result := #$DE;  //  LATIN SMALL LETTER E WITH CIRCUMFLEX
    $00EB : Result := #$DF;  //  LATIN SMALL LETTER E WITH DIAERESIS
    $00EC : Result := #$E0;  //  LATIN SMALL LETTER I WITH GRAVE
    $00ED : Result := #$E2;  //  LATIN SMALL LETTER I WITH ACUTE
    $00EE : Result := #$E4;  //  LATIN SMALL LETTER I WITH CIRCUMFLEX
    $00EF : Result := #$E5;  //  LATIN SMALL LETTER I WITH DIAERESIS
    $00F0 : Result := #$E6;  //  LATIN SMALL LETTER ETH
    $00F1 : Result := #$E7;  //  LATIN SMALL LETTER N WITH TILDE
    $00F2 : Result := #$EC;  //  LATIN SMALL LETTER O WITH GRAVE
    $00F3 : Result := #$ED;  //  LATIN SMALL LETTER O WITH ACUTE
    $00F4 : Result := #$EE;  //  LATIN SMALL LETTER O WITH CIRCUMFLEX
    $00F5 : Result := #$EF;  //  LATIN SMALL LETTER O WITH TILDE
    $00F6 : Result := #$F0;  //  LATIN SMALL LETTER O WITH DIAERESIS
    $00F7 : Result := #$9F;  //  DIVISION SIGN
    $00F8 : Result := #$F9;  //  LATIN SMALL LETTER O WITH STROKE
    $00F9 : Result := #$F2;  //  LATIN SMALL LETTER U WITH GRAVE
    $00FA : Result := #$F3;  //  LATIN SMALL LETTER U WITH ACUTE
    $00FB : Result := #$F4;  //  LATIN SMALL LETTER U WITH CIRCUMFLEX
    $00FC : Result := #$F6;  //  LATIN SMALL LETTER U WITH DIAERESIS
    $00FD : Result := #$F7;  //  LATIN SMALL LETTER Y WITH ACUTE
    $00FE : Result := #$FC;  //  LATIN SMALL LETTER THORN
    $00FF : Result := #$FD;  //  LATIN SMALL LETTER Y WITH DIAERESIS
    $0131 : Result := #$F5;  //  LATIN SMALL LETTER DOTLESS I
    $0141 : Result := #$E8;  //  LATIN CAPITAL LETTER L WITH STROKE
    $0142 : Result := #$F8;  //  LATIN SMALL LETTER L WITH STROKE
    $0152 : Result := #$EA;  //  LATIN CAPITAL LIGATURE OE
    $0153 : Result := #$FA;  //  LATIN SMALL LIGATURE OE
    $0192 : Result := #$A6;  //  LATIN SMALL LETTER F WITH HOOK
    $02C6 : Result := #$C3;  //  MODIFIER LETTER CIRCUMFLEX ACCENT
    $02C7 : Result := #$CF;  //  CARON
    $02CB : Result := #$C1;  //  MODIFIER LETTER GRAVE ACCENT
    $02D8 : Result := #$C6;  //  BREVE
    $02D9 : Result := #$C7;  //  DOT ABOVE
    $02DA : Result := #$CA;  //  RING ABOVE
    $02DB : Result := #$CE;  //  OGONEK
    $02DC : Result := #$C4;  //  SMALL TILDE
    $02DD : Result := #$CD;  //  DOUBLE ACUTE ACCENT
    $2013 : Result := #$B1;  //  EN DASH
    $2014 : Result := #$D0;  //  EM DASH
    $2019 : Result := #$A9;  //  RIGHT SINGLE QUOTATION MARK
    $201A : Result := #$B8;  //  SINGLE LOW-9 QUOTATION MARK
    $201C : Result := #$AA;  //  LEFT DOUBLE QUOTATION MARK
    $201D : Result := #$BA;  //  RIGHT DOUBLE QUOTATION MARK
    $201E : Result := #$B9;  //  DOUBLE LOW-9 QUOTATION MARK
    $2020 : Result := #$B2;  //  DAGGER
    $2021 : Result := #$B3;  //  DOUBLE DAGGER
    $2022 : Result := #$B7;  //  BULLET
    $2026 : Result := #$BC;  //  HORIZONTAL ELLIPSIS
    $2030 : Result := #$BD;  //  PER MILLE SIGN
    $2039 : Result := #$AC;  //  LATIN SMALL LETTER
    $203A : Result := #$AD;  //  LATIN SMALL LETTER
    $2044 : Result := #$A4;  //  FRACTION SLASH
    $FB01 : Result := #$AE;  //  LATIN SMALL LIGATURE FI
    $FB02 : Result := #$AF;  //  LATIN SMALL LIGATURE FL
    $FFFD : Result := #$FE;  //  Not Defined
    $FFFF : Result := #$FE;  //  Not Defined
  else
    raise EConvertError.CreateFmt(SCannotConvert, [Ord(Ch), 'NextStep']);
  end;
end;

class function TNextStepCodec.Name: string;
begin
  Result := 'NextStep';
end;


end.
