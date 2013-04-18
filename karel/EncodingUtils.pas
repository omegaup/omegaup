unit EncodingUtils;

// EncodingUtils 4.0.6
// Delphi 4 to 2009 and Kylix 3 Implementation
// February 2009
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
// The Original Code is "UnicodeUtilsRTL.pas".
//
// The Initial Developer of the Original Code is Dieter Köhler (Heidelberg,
// Germany, "http://www.philo.de/"). Portions created by the Initial Developer
// are Copyright (C) 2003-2009 Dieter Köhler. All Rights Reserved.
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
//
// 2009-02-23 4.0.6 Small revisions.
// 2008-12-03 4.0.5 Made BCB5 compliant.
// 2008-09-28 4.0.4 AnsiString properties changed to string.
// 2008-07-07 4.0.3 General reconstruction.
// 2007-12-03 4.0.2 Made .NET compliant. Conversion stream classes deleted.
// 2004-09-19 4.0.1 Bug fixed.
// 2004-06-01 4.0.0 General reconstruction.

interface

uses
  SysUtils, Classes;

type
  TEncodingInfo = class
  protected
    class procedure Error; virtual;
  public
    class function Alias(I: Integer): string; virtual; {$IFNDEF BCB5}abstract;{$ENDIF}
    class function AliasCount: Integer; virtual; {$IFNDEF BCB5}abstract;{$ENDIF}
    class function Name: string; virtual; {$IFNDEF BCB5}abstract;{$ENDIF}
    class function MIBenum: Integer; virtual; {$IFNDEF BCB5}abstract;{$ENDIF}
    class function PreferredMIMEName: string; virtual; {$IFNDEF BCB5}abstract;{$ENDIF}
  end;

  TEncodingInfoAscii = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin3 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin4 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatinCyrillic = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatinArabic = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatinGreek = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatinHebrew = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin5 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin6 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoTextComm = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHalfWidthKatakana = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoJISEncoding = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoShiftJIS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEUCPPkdFmtJapanese = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEUCFixWidJapanese = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO4UnitedKingdom = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO11SwedishForNames = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO15Italian = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO17Spanish = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO21German = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO60DanishNorwegian = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO69French = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO10646UTF1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO646basic1983 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoInvariant = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO2Int1RefVersion = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoNATSSEFI = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoNATSSEFIADD = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoNATSDANO = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoNATSDANOADD = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO10Swedish = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoKSC56011987 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO2022KR = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEUCKR = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO2022JP = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO2022JP2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO13JISC6220jp = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO14JISC6220ro = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO16Portuguese = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO18Greek7Old = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO19LatinGreek = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO25French = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO27LatinGreek1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO5427Cyrillic = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO42JISC62261978 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO47BSViewdata = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO49INIS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO50INIS8 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO51INISCyrillic = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO5427Cyrillic1981 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO5428Greek = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO57GB1988 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO58GB231280 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO61Norwegian2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO70VideotexSupp1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO84Portuguese2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO85Spanish2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO86Hungarian = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO87JISX0208 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO88Greek7 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO89ASMO449 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO90 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO91JISC62291984a = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO92JISC62991984b = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO93JIS62291984badd = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO94JIS62291984hand = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO95JIS62291984handadd = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO96JISC62291984kana = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO2033 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO99NAPLPS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO102T617bit = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO103T618bit = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO111ECMACyrillic = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO121Canadian1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO122Canadian2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO123CSAZ24341985gr = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO88596E = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO88596I = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO128T101G2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO88598E = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO88598I = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO139CSN369103 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO141JUSIB1002 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO143IECP271 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO146Serbian = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO147Macedonian = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO150GreekCCITT = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO151Cuba = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO6937Add = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO153GOST1976874 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO8859Supp = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO10367Box = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO158Lap = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO159JISX02121990 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO646Danish = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUSDK = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoDKUS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoKSC5636 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicode11UTF7 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO2022CN = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO2022CNEXT = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF8 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO885913 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin8 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin9 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIsoLatin10 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoGBK = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoGB18030 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoOSD_EBCDIC_DF04_15 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoOSD_EBCDIC_DF03_IRV = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoOSD_EBCDIC_DF04_1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO115481 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoKZ1048 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUCS2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUCS4 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicodeASCII = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicodeLatin1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoISO10646J1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicodeIBM1261 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicodeIBM1268 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicodeIBM1276 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicodeIBM1264 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicodeIBM1265 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnicode11 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoSCSU = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF7 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF16BE = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF16LE = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF16 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoCESU8 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF32 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF32BE = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUTF32LE = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoBOCU1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows30Latin1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows31Latin1 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows31Latin2 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows31Latin5 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHPRoman8 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoAdobeStandardEncoding = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoVenturaUS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoVenturaInternational = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoDECMCS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPC850Multilingual = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPCp852 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPC8CodePage437 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPC8DanishNorwegian = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPC862LatinHebrew = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPC8Turkish = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBMSymbols = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBMThai = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHPLegal = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHPPiFont = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHPMath8 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHPPSMath = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHPDesktop = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoVenturaMath = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoMicrosoftPublishing = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows31J = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoGB2312 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoBig5 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoMacintosh = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM037 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM038 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM273 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM274 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM275 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM277 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM278 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM280 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM281 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM284 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM285 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM290 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM297 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM420 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM423 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM424 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM500 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM851 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM855 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM857 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM860 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM861 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM863 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM864 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM865 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM868 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM869 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM870 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM871 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM880 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM891 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM903 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM904 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM905 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM918 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM1026 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBMEBCDICATDE = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICATDEA = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICCAFR = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICDKNO = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICDKNOA = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICFISE = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICFISEA = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICFR = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICIT = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICPT = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICES = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICESA = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICESS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICUK = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoEBCDICUS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoUnknown8Bit = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoMnemonic = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoMnem = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoVISCII = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoVIQR = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoKOI8R = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoHZGB2312 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM866 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPC775Baltic = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoKOI8U = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM00858 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM00924 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01140 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01141 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01142 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01143 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01144 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01145 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01146 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01147 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01148 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM01149 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoBig5HKSCS = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoIBM1047 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoPTCP154 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoAmiga1251 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoKOI7switched = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoBRF = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoTSCII = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1250 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1251 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1252 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1253 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1254 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1255 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1256 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1257 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoWindows1258 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;

  TEncodingInfoTIS620 = class(TEncodingInfo)
  public
    class function Alias(I: Integer): string; override;
    class function AliasCount: Integer; override;
    class function Name: string; override;
    class function MIBenum: Integer; override;
    class function PreferredMIMEName: string; override;
  end;


// List of all CodecInfos

{$IFDEF CLR}
const
  Encodings: array[0..253] of TEncodingInfo = (
    TEncodingInfoAscii.Create, TEncodingInfoIsoLatin1.Create, TEncodingInfoIsoLatin2.Create,
    TEncodingInfoIsoLatin3.Create, TEncodingInfoIsoLatin4.Create, TEncodingInfoIsoLatinCyrillic.Create,
    TEncodingInfoIsoLatinArabic.Create, TEncodingInfoIsoLatinGreek.Create, TEncodingInfoIsoLatinHebrew.Create,
    TEncodingInfoIsoLatin5.Create, TEncodingInfoIsoLatin6.Create, TEncodingInfoIsoTextComm.Create,
    TEncodingInfoHalfWidthKatakana.Create, TEncodingInfoJISEncoding.Create,
    TEncodingInfoShiftJIS.Create, TEncodingInfoEUCPPkdFmtJapanese.Create,
    TEncodingInfoEUCFixWidJapanese.Create, TEncodingInfoISO4UnitedKingdom.Create,
    TEncodingInfoISO11SwedishForNames.Create, TEncodingInfoISO15Italian.Create,
    TEncodingInfoISO17Spanish.Create, TEncodingInfoISO21German.Create,
    TEncodingInfoISO60DanishNorwegian.Create, TEncodingInfoISO69French.Create,
    TEncodingInfoISO10646UTF1.Create, TEncodingInfoISO646basic1983.Create,
    TEncodingInfoInvariant.Create, TEncodingInfoISO2Int1RefVersion.Create, TEncodingInfoNATSSEFI.Create,
    TEncodingInfoNATSSEFIADD.Create, TEncodingInfoNATSDANO.Create, TEncodingInfoNATSDANOADD.Create,
    TEncodingInfoISO10Swedish.Create, TEncodingInfoKSC56011987.Create, TEncodingInfoISO2022KR.Create,
    TEncodingInfoEUCKR.Create, TEncodingInfoISO2022JP.Create, TEncodingInfoISO2022JP2.Create,
    TEncodingInfoISO13JISC6220jp.Create, TEncodingInfoISO14JISC6220ro.Create,
    TEncodingInfoISO16Portuguese.Create, TEncodingInfoISO18Greek7Old.Create,
    TEncodingInfoISO19LatinGreek.Create, TEncodingInfoISO25French.Create,
    TEncodingInfoISO27LatinGreek1.Create, TEncodingInfoISO5427Cyrillic.Create,
    TEncodingInfoISO42JISC62261978.Create, TEncodingInfoISO47BSViewdata.Create, TEncodingInfoISO49INIS.Create,
    TEncodingInfoISO50INIS8.Create, TEncodingInfoISO51INISCyrillic.Create,
    TEncodingInfoISO5427Cyrillic1981.Create, TEncodingInfoISO5428Greek.Create,
    TEncodingInfoISO57GB1988.Create, TEncodingInfoISO58GB231280.Create, TEncodingInfoISO61Norwegian2.Create,
    TEncodingInfoISO70VideotexSupp1.Create, TEncodingInfoISO84Portuguese2.Create,
    TEncodingInfoISO85Spanish2.Create, TEncodingInfoISO86Hungarian.Create, TEncodingInfoISO87JISX0208.Create,
    TEncodingInfoISO88Greek7.Create, TEncodingInfoISO89ASMO449.Create, TEncodingInfoISO90.Create,
    TEncodingInfoISO91JISC62291984a.Create, TEncodingInfoISO92JISC62991984b.Create,
    TEncodingInfoISO93JIS62291984badd.Create, TEncodingInfoISO94JIS62291984hand.Create,
    TEncodingInfoISO95JIS62291984handadd.Create, TEncodingInfoISO96JISC62291984kana.Create,
    TEncodingInfoISO2033.Create, TEncodingInfoISO99NAPLPS.Create, TEncodingInfoISO102T617bit.Create,
    TEncodingInfoISO103T618bit.Create, TEncodingInfoISO111ECMACyrillic.Create,
    TEncodingInfoISO121Canadian1.Create, TEncodingInfoISO122Canadian2.Create,
    TEncodingInfoISO123CSAZ24341985gr.Create, TEncodingInfoISO88596E.Create, TEncodingInfoISO88596I.Create,
    TEncodingInfoISO128T101G2.Create, TEncodingInfoISO88598E.Create, TEncodingInfoISO88598I.Create,
    TEncodingInfoISO139CSN369103.Create, TEncodingInfoISO141JUSIB1002.Create,
    TEncodingInfoISO143IECP271.Create, TEncodingInfoISO146Serbian.Create,
    TEncodingInfoISO147Macedonian.Create, TEncodingInfoISO150GreekCCITT.Create,
    TEncodingInfoISO151Cuba.Create, TEncodingInfoISO6937Add.Create, TEncodingInfoISO153GOST1976874.Create,
    TEncodingInfoISO8859Supp.Create, TEncodingInfoISO10367Box.Create, TEncodingInfoISO158Lap.Create,
    TEncodingInfoISO159JISX02121990.Create, TEncodingInfoISO646Danish.Create, TEncodingInfoUSDK.Create,
    TEncodingInfoDKUS.Create, TEncodingInfoKSC5636.Create, TEncodingInfoUnicode11UTF7.Create,
    TEncodingInfoISO2022CN.Create, TEncodingInfoISO2022CNEXT.Create, TEncodingInfoUTF8.Create,
    TEncodingInfoISO885913.Create, TEncodingInfoIsoLatin8.Create, TEncodingInfoIsoLatin9.Create,
    TEncodingInfoIsoLatin10.Create, TEncodingInfoGBK.Create, TEncodingInfoGB18030.Create,
    TEncodingInfoOSD_EBCDIC_DF04_15.Create, TEncodingInfoOSD_EBCDIC_DF03_IRV.Create,
    TEncodingInfoOSD_EBCDIC_DF04_1.Create, TEncodingInfoISO115481.Create, TEncodingInfoKZ1048.Create,
    TEncodingInfoUCS2.Create, TEncodingInfoUCS4.Create, TEncodingInfoUnicodeASCII.Create,
    TEncodingInfoUnicodeLatin1.Create, TEncodingInfoISO10646J1.Create, TEncodingInfoUnicodeIBM1261.Create,
    TEncodingInfoUnicodeIBM1268.Create, TEncodingInfoUnicodeIBM1276.Create,
    TEncodingInfoUnicodeIBM1264.Create, TEncodingInfoUnicodeIBM1265.Create, TEncodingInfoUnicode11.Create,
    TEncodingInfoSCSU.Create, TEncodingInfoUTF7.Create, TEncodingInfoUTF16BE.Create, TEncodingInfoUTF16LE.Create,
    TEncodingInfoUTF16.Create, TEncodingInfoCESU8.Create, TEncodingInfoUTF32.Create, TEncodingInfoUTF32BE.Create,
    TEncodingInfoUTF32LE.Create, TEncodingInfoBOCU1.Create, TEncodingInfoWindows30Latin1.Create,
    TEncodingInfoWindows31Latin1.Create, TEncodingInfoWindows31Latin2.Create,
    TEncodingInfoWindows31Latin5.Create, TEncodingInfoHPRoman8.Create,
    TEncodingInfoAdobeStandardEncoding.Create, TEncodingInfoVenturaUS.Create,
    TEncodingInfoVenturaInternational.Create, TEncodingInfoDECMCS.Create,
    TEncodingInfoPC850Multilingual.Create, TEncodingInfoPCp852.Create, TEncodingInfoPC8CodePage437.Create,
    TEncodingInfoPC8DanishNorwegian.Create, TEncodingInfoPC862LatinHebrew.Create,
    TEncodingInfoPC8Turkish.Create, TEncodingInfoIBMSymbols.Create, TEncodingInfoIBMThai.Create,
    TEncodingInfoHPLegal.Create, TEncodingInfoHPPiFont.Create, TEncodingInfoHPMath8.Create,
    TEncodingInfoHPPSMath.Create, TEncodingInfoHPDesktop.Create, TEncodingInfoVenturaMath.Create,
    TEncodingInfoMicrosoftPublishing.Create, TEncodingInfoWindows31J.Create, TEncodingInfoGB2312.Create,
    TEncodingInfoBig5.Create, TEncodingInfoMacintosh.Create, TEncodingInfoIBM037.Create, TEncodingInfoIBM038.Create,
    TEncodingInfoIBM273.Create, TEncodingInfoIBM274.Create, TEncodingInfoIBM275.Create, TEncodingInfoIBM277.Create,
    TEncodingInfoIBM278.Create, TEncodingInfoIBM280.Create, TEncodingInfoIBM281.Create, TEncodingInfoIBM284.Create,
    TEncodingInfoIBM285.Create, TEncodingInfoIBM290.Create, TEncodingInfoIBM297.Create, TEncodingInfoIBM420.Create,
    TEncodingInfoIBM423.Create, TEncodingInfoIBM424.Create, TEncodingInfoIBM500.Create, TEncodingInfoIBM851.Create,
    TEncodingInfoIBM855.Create, TEncodingInfoIBM857.Create, TEncodingInfoIBM860.Create, TEncodingInfoIBM861.Create,
    TEncodingInfoIBM863.Create, TEncodingInfoIBM864.Create, TEncodingInfoIBM865.Create, TEncodingInfoIBM868.Create,
    TEncodingInfoIBM869.Create, TEncodingInfoIBM870.Create, TEncodingInfoIBM871.Create, TEncodingInfoIBM880.Create,
    TEncodingInfoIBM891.Create, TEncodingInfoIBM903.Create, TEncodingInfoIBM904.Create, TEncodingInfoIBM905.Create,
    TEncodingInfoIBM918.Create, TEncodingInfoIBM1026.Create, TEncodingInfoIBMEBCDICATDE.Create,
    TEncodingInfoEBCDICATDEA.Create, TEncodingInfoEBCDICCAFR.Create, TEncodingInfoEBCDICDKNO.Create,
    TEncodingInfoEBCDICDKNOA.Create, TEncodingInfoEBCDICFISE.Create, TEncodingInfoEBCDICFISEA.Create,
    TEncodingInfoEBCDICFR.Create, TEncodingInfoEBCDICIT.Create, TEncodingInfoEBCDICPT.Create,
    TEncodingInfoEBCDICES.Create, TEncodingInfoEBCDICESA.Create, TEncodingInfoEBCDICESS.Create,
    TEncodingInfoEBCDICUK.Create, TEncodingInfoEBCDICUS.Create, TEncodingInfoUnknown8Bit.Create,
    TEncodingInfoMnemonic.Create, TEncodingInfoMnem.Create, TEncodingInfoVISCII.Create, TEncodingInfoVIQR.Create,
    TEncodingInfoKOI8R.Create, TEncodingInfoHZGB2312.Create, TEncodingInfoIBM866.Create,
    TEncodingInfoPC775Baltic.Create, TEncodingInfoKOI8U.Create, TEncodingInfoIBM00858.Create,
    TEncodingInfoIBM00924.Create, TEncodingInfoIBM01140.Create, TEncodingInfoIBM01141.Create,
    TEncodingInfoIBM01142.Create, TEncodingInfoIBM01143.Create, TEncodingInfoIBM01144.Create,
    TEncodingInfoIBM01145.Create, TEncodingInfoIBM01146.Create, TEncodingInfoIBM01147.Create,
    TEncodingInfoIBM01148.Create, TEncodingInfoIBM01149.Create, TEncodingInfoBig5HKSCS.Create,
    TEncodingInfoIBM1047.Create, TEncodingInfoPTCP154.Create, TEncodingInfoAmiga1251.Create,
    TEncodingInfoKOI7switched.Create, TEncodingInfoBRF.Create, TEncodingInfoTSCII.Create,
    TEncodingInfoWindows1250.Create, TEncodingInfoWindows1251.Create, TEncodingInfoWindows1252.Create,
    TEncodingInfoWindows1253.Create, TEncodingInfoWindows1254.Create, TEncodingInfoWindows1255.Create,
    TEncodingInfoWindows1256.Create, TEncodingInfoWindows1257.Create, TEncodingInfoWindows1258.Create,
    TEncodingInfoTIS620.Create
  );
{$ELSE}
type
  TEncodingInfoClass = class of TEncodingInfo;

const
  Encodings: array[0..253] of TEncodingInfoClass = (
    TEncodingInfoAscii, TEncodingInfoIsoLatin1, TEncodingInfoIsoLatin2,
    TEncodingInfoIsoLatin3, TEncodingInfoIsoLatin4, TEncodingInfoIsoLatinCyrillic,
    TEncodingInfoIsoLatinArabic, TEncodingInfoIsoLatinGreek, TEncodingInfoIsoLatinHebrew,
    TEncodingInfoIsoLatin5, TEncodingInfoIsoLatin6, TEncodingInfoIsoTextComm,
    TEncodingInfoHalfWidthKatakana, TEncodingInfoJISEncoding,
    TEncodingInfoShiftJIS, TEncodingInfoEUCPPkdFmtJapanese,
    TEncodingInfoEUCFixWidJapanese, TEncodingInfoISO4UnitedKingdom,
    TEncodingInfoISO11SwedishForNames, TEncodingInfoISO15Italian,
    TEncodingInfoISO17Spanish, TEncodingInfoISO21German,
    TEncodingInfoISO60DanishNorwegian, TEncodingInfoISO69French,
    TEncodingInfoISO10646UTF1, TEncodingInfoISO646basic1983,
    TEncodingInfoInvariant, TEncodingInfoISO2Int1RefVersion, TEncodingInfoNATSSEFI,
    TEncodingInfoNATSSEFIADD, TEncodingInfoNATSDANO, TEncodingInfoNATSDANOADD,
    TEncodingInfoISO10Swedish, TEncodingInfoKSC56011987, TEncodingInfoISO2022KR,
    TEncodingInfoEUCKR, TEncodingInfoISO2022JP, TEncodingInfoISO2022JP2,
    TEncodingInfoISO13JISC6220jp, TEncodingInfoISO14JISC6220ro,
    TEncodingInfoISO16Portuguese, TEncodingInfoISO18Greek7Old,
    TEncodingInfoISO19LatinGreek, TEncodingInfoISO25French,
    TEncodingInfoISO27LatinGreek1, TEncodingInfoISO5427Cyrillic,
    TEncodingInfoISO42JISC62261978, TEncodingInfoISO47BSViewdata, TEncodingInfoISO49INIS,
    TEncodingInfoISO50INIS8, TEncodingInfoISO51INISCyrillic,
    TEncodingInfoISO5427Cyrillic1981, TEncodingInfoISO5428Greek,
    TEncodingInfoISO57GB1988, TEncodingInfoISO58GB231280, TEncodingInfoISO61Norwegian2,
    TEncodingInfoISO70VideotexSupp1, TEncodingInfoISO84Portuguese2,
    TEncodingInfoISO85Spanish2, TEncodingInfoISO86Hungarian, TEncodingInfoISO87JISX0208,
    TEncodingInfoISO88Greek7, TEncodingInfoISO89ASMO449, TEncodingInfoISO90,
    TEncodingInfoISO91JISC62291984a, TEncodingInfoISO92JISC62991984b,
    TEncodingInfoISO93JIS62291984badd, TEncodingInfoISO94JIS62291984hand,
    TEncodingInfoISO95JIS62291984handadd, TEncodingInfoISO96JISC62291984kana,
    TEncodingInfoISO2033, TEncodingInfoISO99NAPLPS, TEncodingInfoISO102T617bit,
    TEncodingInfoISO103T618bit, TEncodingInfoISO111ECMACyrillic,
    TEncodingInfoISO121Canadian1, TEncodingInfoISO122Canadian2,
    TEncodingInfoISO123CSAZ24341985gr, TEncodingInfoISO88596E, TEncodingInfoISO88596I,
    TEncodingInfoISO128T101G2, TEncodingInfoISO88598E, TEncodingInfoISO88598I,
    TEncodingInfoISO139CSN369103, TEncodingInfoISO141JUSIB1002,
    TEncodingInfoISO143IECP271, TEncodingInfoISO146Serbian,
    TEncodingInfoISO147Macedonian, TEncodingInfoISO150GreekCCITT,
    TEncodingInfoISO151Cuba, TEncodingInfoISO6937Add, TEncodingInfoISO153GOST1976874,
    TEncodingInfoISO8859Supp, TEncodingInfoISO10367Box, TEncodingInfoISO158Lap,
    TEncodingInfoISO159JISX02121990, TEncodingInfoISO646Danish, TEncodingInfoUSDK,
    TEncodingInfoDKUS, TEncodingInfoKSC5636, TEncodingInfoUnicode11UTF7,
    TEncodingInfoISO2022CN, TEncodingInfoISO2022CNEXT, TEncodingInfoUTF8,
    TEncodingInfoISO885913, TEncodingInfoIsoLatin8, TEncodingInfoIsoLatin9,
    TEncodingInfoIsoLatin10, TEncodingInfoGBK, TEncodingInfoGB18030,
    TEncodingInfoOSD_EBCDIC_DF04_15, TEncodingInfoOSD_EBCDIC_DF03_IRV,
    TEncodingInfoOSD_EBCDIC_DF04_1, TEncodingInfoISO115481, TEncodingInfoKZ1048,
    TEncodingInfoUCS2, TEncodingInfoUCS4, TEncodingInfoUnicodeASCII,
    TEncodingInfoUnicodeLatin1, TEncodingInfoISO10646J1, TEncodingInfoUnicodeIBM1261,
    TEncodingInfoUnicodeIBM1268, TEncodingInfoUnicodeIBM1276,
    TEncodingInfoUnicodeIBM1264, TEncodingInfoUnicodeIBM1265, TEncodingInfoUnicode11,
    TEncodingInfoSCSU, TEncodingInfoUTF7, TEncodingInfoUTF16BE, TEncodingInfoUTF16LE,
    TEncodingInfoUTF16, TEncodingInfoCESU8, TEncodingInfoUTF32, TEncodingInfoUTF32BE,
    TEncodingInfoUTF32LE, TEncodingInfoBOCU1, TEncodingInfoWindows30Latin1,
    TEncodingInfoWindows31Latin1, TEncodingInfoWindows31Latin2,
    TEncodingInfoWindows31Latin5, TEncodingInfoHPRoman8,
    TEncodingInfoAdobeStandardEncoding, TEncodingInfoVenturaUS,
    TEncodingInfoVenturaInternational, TEncodingInfoDECMCS,
    TEncodingInfoPC850Multilingual, TEncodingInfoPCp852, TEncodingInfoPC8CodePage437,
    TEncodingInfoPC8DanishNorwegian, TEncodingInfoPC862LatinHebrew,
    TEncodingInfoPC8Turkish, TEncodingInfoIBMSymbols, TEncodingInfoIBMThai,
    TEncodingInfoHPLegal, TEncodingInfoHPPiFont, TEncodingInfoHPMath8,
    TEncodingInfoHPPSMath, TEncodingInfoHPDesktop, TEncodingInfoVenturaMath,
    TEncodingInfoMicrosoftPublishing, TEncodingInfoWindows31J, TEncodingInfoGB2312,
    TEncodingInfoBig5, TEncodingInfoMacintosh, TEncodingInfoIBM037, TEncodingInfoIBM038,
    TEncodingInfoIBM273, TEncodingInfoIBM274, TEncodingInfoIBM275, TEncodingInfoIBM277,
    TEncodingInfoIBM278, TEncodingInfoIBM280, TEncodingInfoIBM281, TEncodingInfoIBM284,
    TEncodingInfoIBM285, TEncodingInfoIBM290, TEncodingInfoIBM297, TEncodingInfoIBM420,
    TEncodingInfoIBM423, TEncodingInfoIBM424, TEncodingInfoIBM500, TEncodingInfoIBM851,
    TEncodingInfoIBM855, TEncodingInfoIBM857, TEncodingInfoIBM860, TEncodingInfoIBM861,
    TEncodingInfoIBM863, TEncodingInfoIBM864, TEncodingInfoIBM865, TEncodingInfoIBM868,
    TEncodingInfoIBM869, TEncodingInfoIBM870, TEncodingInfoIBM871, TEncodingInfoIBM880,
    TEncodingInfoIBM891, TEncodingInfoIBM903, TEncodingInfoIBM904, TEncodingInfoIBM905,
    TEncodingInfoIBM918, TEncodingInfoIBM1026, TEncodingInfoIBMEBCDICATDE,
    TEncodingInfoEBCDICATDEA, TEncodingInfoEBCDICCAFR, TEncodingInfoEBCDICDKNO,
    TEncodingInfoEBCDICDKNOA, TEncodingInfoEBCDICFISE, TEncodingInfoEBCDICFISEA,
    TEncodingInfoEBCDICFR, TEncodingInfoEBCDICIT, TEncodingInfoEBCDICPT,
    TEncodingInfoEBCDICES, TEncodingInfoEBCDICESA, TEncodingInfoEBCDICESS,
    TEncodingInfoEBCDICUK, TEncodingInfoEBCDICUS, TEncodingInfoUnknown8Bit,
    TEncodingInfoMnemonic, TEncodingInfoMnem, TEncodingInfoVISCII, TEncodingInfoVIQR,
    TEncodingInfoKOI8R, TEncodingInfoHZGB2312, TEncodingInfoIBM866,
    TEncodingInfoPC775Baltic, TEncodingInfoKOI8U, TEncodingInfoIBM00858,
    TEncodingInfoIBM00924, TEncodingInfoIBM01140, TEncodingInfoIBM01141,
    TEncodingInfoIBM01142, TEncodingInfoIBM01143, TEncodingInfoIBM01144,
    TEncodingInfoIBM01145, TEncodingInfoIBM01146, TEncodingInfoIBM01147,
    TEncodingInfoIBM01148, TEncodingInfoIBM01149, TEncodingInfoBig5HKSCS,
    TEncodingInfoIBM1047, TEncodingInfoPTCP154, TEncodingInfoAmiga1251,
    TEncodingInfoKOI7switched, TEncodingInfoBRF, TEncodingInfoTSCII,
    TEncodingInfoWindows1250, TEncodingInfoWindows1251, TEncodingInfoWindows1252,
    TEncodingInfoWindows1253, TEncodingInfoWindows1254, TEncodingInfoWindows1255,
    TEncodingInfoWindows1256, TEncodingInfoWindows1257, TEncodingInfoWindows1258,
    TEncodingInfoTIS620
  );
{$ENDIF}

implementation

resourcestring
  SIndexOutOfRange  = 'Index out of range';

{ TEncodingInfo }

class procedure TEncodingInfo.Error;
begin
{$IFDEF CLR}
  raise ArgumentOutOfRangeException.Create;
{$ELSE}
  raise EAccessViolation.Create(SIndexOutOfRange);
{$ENDIF}
end;

{$IFDEF BCB5}
class function TEncodingInfo.Alias(I: Integer): string;
begin
   Error;
end;

class function TEncodingInfo.AliasCount: Integer;
begin
  Result := 0;
end;

class function TEncodingInfo.Name: string;
begin
  Result := '';
end;

class function TEncodingInfo.MIBenum: Integer;
begin
  Result := -1;
end;

class function TEncodingInfo.PreferredMIMEName: string;
begin
  Result := '';
end;
{$ENDIF}

{ TEncodingInfoAscii }

class function TEncodingInfoAscii.Alias(I: Integer): string;
begin
 case I of
    0: Result := 'ANSI_X3.4-1968';
    1: Result := 'iso-ir-6';
    2: Result := 'ANSI_X3.4-1986';
    3: Result := 'ISO_646.irv:1991';
    4: Result := 'ASCII';
    5: Result := 'ISO646-US';
    6: Result := 'US-ASCII';
    7: Result := 'us';
    8: Result := 'IBM367';
    9: Result := 'cp367';
    10: Result := 'csASCII';
    11: Result := 'ISO_646.irv'; // Non-standard alias
    12: Result := 'ISO_646';     // Non-standard alias
    13: Result := 'ISO-646';     // Non-standard alias
    14: Result := 'ISO646';      // Non-standard alias
    15: Result := 'IBM891';      // Non-standard alias
    16: Result := 'IBM903';      // Non-standard alias
  else
    Error;
  end;
end;

class function TEncodingInfoAscii.AliasCount: Integer;
begin
  Result := 17;
end;

class function TEncodingInfoAscii.MIBenum: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoAscii.Name: string;
begin
  Result := 'ANSI_X3.4-1968';
end;

class function TEncodingInfoAscii.PreferredMIMEName: string;
begin
  Result := 'US-ASCII';
end;

{ TEncodingInfoIsoLatin1 }

class function TEncodingInfoIsoLatin1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-1:1987';
    1: Result := 'iso-ir-100';
    2: Result := 'ISO_8859-1';
    3: Result := 'ISO-8859-1';
    4: Result := 'latin1';
    5: Result := 'l1';
    6: Result := 'IBM819';
    7: Result := 'CP819';
    8: Result := 'csISOLatin1';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin1.AliasCount: Integer;
begin
  Result := 9;
end;

class function TEncodingInfoIsoLatin1.MIBenum: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIsoLatin1.Name: string;
begin
  Result := 'ISO_8859-1:1987';
end;

class function TEncodingInfoIsoLatin1.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-1';
end;

{ TEncodingInfoIsoLatin2 }

class function TEncodingInfoIsoLatin2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-2:1987';
    1: Result := 'iso-ir-101';
    2: Result := 'ISO_8859-2';
    3: Result := 'ISO-8859-2';
    4: Result := 'latin2';
    5: Result := 'l2';
    6: Result := 'csISOLatin2';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin2.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIsoLatin2.MIBenum: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIsoLatin2.Name: string;
begin
  Result := 'ISO_8859-2:1987';
end;

class function TEncodingInfoIsoLatin2.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-2';
end;

{ TEncodingInfoIsoLatin3 }

class function TEncodingInfoIsoLatin3.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-3:1988';
    1: Result := 'iso-ir-109';
    2: Result := 'ISO_8859-3';
    3: Result := 'ISO-8859-3';
    4: Result := 'latin3';
    5: Result := 'l3';
    6: Result := 'csISOLatin3';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin3.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIsoLatin3.MIBenum: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoIsoLatin3.Name: string;
begin
  Result := 'ISO_8859-3:1988';
end;

class function TEncodingInfoIsoLatin3.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-3';
end;

{ TEncodingInfoIsoLatin4 }

class function TEncodingInfoIsoLatin4.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-4:1988';
    1: Result := 'iso-ir-110';
    2: Result := 'ISO_8859-4';
    3: Result := 'ISO-8859-4';
    4: Result := 'latin4';
    5: Result := 'l4';
    6: Result := 'csISOLatin4';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin4.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIsoLatin4.MIBenum: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIsoLatin4.Name: string;
begin
  Result := 'ISO_8859-4:1988';
end;

class function TEncodingInfoIsoLatin4.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-4';
end;

{ TEncodingInfoIsoLatinCyrillic }

class function TEncodingInfoIsoLatinCyrillic.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-5:1988';
    1: Result := 'iso-ir-144';
    2: Result := 'ISO_8859-5';
    3: Result := 'ISO-8859-5';
    4: Result := 'cyrillic';
    5: Result := 'csISOLatinCyrillic';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatinCyrillic.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoIsoLatinCyrillic.MIBenum: Integer;
begin
  Result := 8;
end;

class function TEncodingInfoIsoLatinCyrillic.Name: string;
begin
  Result := 'ISO_8859-5:1988';
end;

class function TEncodingInfoIsoLatinCyrillic.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-5';
end;

{ TEncodingInfoIsoLatinArabic }

class function TEncodingInfoIsoLatinArabic.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-6:1987';
    1: Result := 'iso-ir-127';
    2: Result := 'ISO_8859-6';
    3: Result := 'ISO-8859-6';
    4: Result := 'ECMA-114';
    5: Result := 'ASMO-708';
    6: Result := 'arabic';
    7: Result := 'csISOLatinArabic';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatinArabic.AliasCount: Integer;
begin
  Result := 8;
end;

class function TEncodingInfoIsoLatinArabic.MIBenum: Integer;
begin
  Result := 9;
end;

class function TEncodingInfoIsoLatinArabic.Name: string;
begin
  Result := 'ISO_8859-6:1987';
end;

class function TEncodingInfoIsoLatinArabic.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-6';
end;

{ TEncodingInfoIsoLatinGreek }

class function TEncodingInfoIsoLatinGreek.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-7:1987';
    1: Result := 'iso-ir-126';
    2: Result := 'ISO_8859-7';
    3: Result := 'ISO-8859-7';
    4: Result := 'ELOT_928';
    5: Result := 'ECMA-118';
    6: Result := 'greek';
    7: Result := 'greek8';
    8: Result := 'csISOLatinGreek';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatinGreek.AliasCount: Integer;
begin
  Result := 9;
end;

class function TEncodingInfoIsoLatinGreek.MIBenum: Integer;
begin
  Result := 10;
end;

class function TEncodingInfoIsoLatinGreek.Name: string;
begin
  Result := 'ISO_8859-7:1987';
end;

class function TEncodingInfoIsoLatinGreek.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-7';
end;

{ TEncodingInfoIsoLatinHebrew }

class function TEncodingInfoIsoLatinHebrew.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-8:1988';
    1: Result := 'iso-ir-138';
    2: Result := 'ISO_8859-8';
    3: Result := 'ISO-8859-8';
    4: Result := 'hebrew';
    5: Result := 'csISOLatinHebrew';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatinHebrew.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoIsoLatinHebrew.MIBenum: Integer;
begin
  Result := 11;
end;

class function TEncodingInfoIsoLatinHebrew.Name: string;
begin
  Result := 'ISO_8859-8:1988';
end;

class function TEncodingInfoIsoLatinHebrew.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-8';
end;

{ TEncodingInfoIsoLatin5 }

class function TEncodingInfoIsoLatin5.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-9:1989';
    1: Result := 'iso-ir-148';
    2: Result := 'ISO_8859-9';
    3: Result := 'ISO-8859-9';
    4: Result := 'latin5';
    5: Result := 'l5';
    6: Result := 'csISOLatin5';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin5.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIsoLatin5.MIBenum: Integer;
begin
  Result := 12;
end;

class function TEncodingInfoIsoLatin5.Name: string;
begin
  Result := 'ISO_8859-9:1989';
end;

class function TEncodingInfoIsoLatin5.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-9';
end;

{ TEncodingInfoIsoLatin6 }

class function TEncodingInfoIsoLatin6.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-10';
    1: Result := 'iso-ir-157';
    2: Result := 'l6';
    3: Result := 'ISO-8859-10:1992';
    4: Result := 'csISOLatin6';
    5: Result := 'latin6';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin6.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoIsoLatin6.MIBenum: Integer;
begin
  Result := 13;
end;

class function TEncodingInfoIsoLatin6.Name: string;
begin
  Result := 'ISO_8859-10';
end;

class function TEncodingInfoIsoLatin6.PreferredMIMEName: string;
begin
  Result := 'ISO_8859-10';
end;

{ TEncodingInfoIsoTextComm }

class function TEncodingInfoIsoTextComm.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_6937-2-add';
    1: Result := 'iso-ir-142';
    2: Result := 'csISOTextComm';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoTextComm.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoIsoTextComm.MIBenum: Integer;
begin
  Result := 14;
end;

class function TEncodingInfoIsoTextComm.Name: string;
begin
  Result := 'ISO_6937-2-add';
end;

class function TEncodingInfoIsoTextComm.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHalfWidthKatakana }

class function TEncodingInfoHalfWidthKatakana.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_X0201';
    1: Result := 'X0201';
    2: Result := 'csHalfWidthKatakana';
  else
    Error;
  end;
end;

class function TEncodingInfoHalfWidthKatakana.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoHalfWidthKatakana.MIBenum: Integer;
begin
  Result := 15;
end;

class function TEncodingInfoHalfWidthKatakana.Name: string;
begin
  Result := 'JIS_X0201';
end;

class function TEncodingInfoHalfWidthKatakana.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoJISEncoding }

class function TEncodingInfoJISEncoding.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_Encoding';
    1: Result := 'csJISEncoding';
  else
    Error;
  end;
end;

class function TEncodingInfoJISEncoding.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoJISEncoding.MIBenum: Integer;
begin
  Result := 16;
end;

class function TEncodingInfoJISEncoding.Name: string;
begin
  Result := 'JIS_Encoding';
end;

class function TEncodingInfoJISEncoding.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoShiftJIS }

class function TEncodingInfoShiftJIS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Shift_JIS';
    1: Result := 'MS_Kanji';
    2: Result := 'csShiftJIS';
  else
    Error;
  end;
end;

class function TEncodingInfoShiftJIS.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoShiftJIS.MIBenum: Integer;
begin
  Result := 17;
end;

class function TEncodingInfoShiftJIS.Name: string;
begin
  Result := 'Shift_JIS';
end;

class function TEncodingInfoShiftJIS.PreferredMIMEName: string;
begin
  Result := 'Shift_JIS';
end;

{ TEncodingInfoEUCPPkdFmtJapanese }

class function TEncodingInfoEUCPPkdFmtJapanese.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Extended_UNIX_Code_Packed_Format_for_Japanese';
    1: Result := 'csEUCPPkdFmtJapanese';
    2: Result := 'EUC-JP';
  else
    Error;
  end;
end;

class function TEncodingInfoEUCPPkdFmtJapanese.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoEUCPPkdFmtJapanese.MIBenum: Integer;
begin
  Result := 18;
end;

class function TEncodingInfoEUCPPkdFmtJapanese.Name: string;
begin
  Result := 'Extended_UNIX_Code_Packed_Format_for_Japanese';
end;

class function TEncodingInfoEUCPPkdFmtJapanese.PreferredMIMEName: string;
begin
  Result := 'EUC-JP';
end;

{ TEncodingInfoEUCFixWidJapanese }

class function TEncodingInfoEUCFixWidJapanese.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Extended_UNIX_Code_Fixed_Width_for_Japanese';
    1: Result := 'csEUCFixWidJapanese';
  else
    Error;
  end;
end;

class function TEncodingInfoEUCFixWidJapanese.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEUCFixWidJapanese.MIBenum: Integer;
begin
  Result := 19;
end;

class function TEncodingInfoEUCFixWidJapanese.Name: string;
begin
  Result := 'Extended_UNIX_Code_Fixed_Width_for_Japanese';
end;

class function TEncodingInfoEUCFixWidJapanese.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO4UnitedKingdom }

class function TEncodingInfoISO4UnitedKingdom.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'BS_4730';
    1: Result := 'iso-ir-4';
    2: Result := 'ISO646-GB';
    3: Result := 'gb';
    4: Result := 'uk';
    5: Result := 'csISO4UnitedKingdom';
  else
    Error;
  end;
end;

class function TEncodingInfoISO4UnitedKingdom.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoISO4UnitedKingdom.MIBenum: Integer;
begin
  Result := 20;
end;

class function TEncodingInfoISO4UnitedKingdom.Name: string;
begin
  Result := 'BS_4730';
end;

class function TEncodingInfoISO4UnitedKingdom.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO11SwedishForNames }

class function TEncodingInfoISO11SwedishForNames.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'SEN_850200_C';
    1: Result := 'iso-ir-11';
    2: Result := 'ISO646-SE2';
    3: Result := 'se2';
    4: Result := 'csISO11SwedishForNames';
  else
    Error;
  end;
end;

class function TEncodingInfoISO11SwedishForNames.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO11SwedishForNames.MIBenum: Integer;
begin
  Result := 21;
end;

class function TEncodingInfoISO11SwedishForNames.Name: string;
begin
  Result := 'SEN_850200_C';
end;

class function TEncodingInfoISO11SwedishForNames.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO15Italian }

class function TEncodingInfoISO15Italian.Alias(I: Integer): string;
begin
 case I of
    0: Result := 'IT';
    1: Result := 'iso-ir-15';
    2: Result := 'ISO646-IT';
    3: Result := 'csISO15Italian';
  else
    Error;
  end;
end;

class function TEncodingInfoISO15Italian.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO15Italian.MIBenum: Integer;
begin
  Result := 22;
end;

class function TEncodingInfoISO15Italian.Name: string;
begin
  Result := 'IT';
end;

class function TEncodingInfoISO15Italian.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO17Spanish }

class function TEncodingInfoISO17Spanish.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ES';
    1: Result := 'iso-ir-17';
    2: Result := 'ISO646-ES';
    3: Result := 'csISO17Spanish';
  else
    Error;
  end;
end;

class function TEncodingInfoISO17Spanish.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO17Spanish.MIBenum: Integer;
begin
  Result := 23;
end;

class function TEncodingInfoISO17Spanish.Name: string;
begin
  Result := 'ES';
end;

class function TEncodingInfoISO17Spanish.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO21German }

class function TEncodingInfoISO21German.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'DIN_66003';
    1: Result := 'iso-ir-21';
    2: Result := 'de';
    3: Result := 'ISO646-DE';
    4: Result := 'csISO21German';
  else
    Error;
  end;
end;

class function TEncodingInfoISO21German.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO21German.MIBenum: Integer;
begin
  Result := 24;
end;

class function TEncodingInfoISO21German.Name: string;
begin
  Result := 'DIN_66003';
end;

class function TEncodingInfoISO21German.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO60DanishNorwegian }

class function TEncodingInfoISO60DanishNorwegian.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NS_4551-1';
    1: Result := 'iso-ir-60';
    2: Result := 'ISO646-NO';
    3: Result := 'no';
    4: Result := 'csISO60Danish-Norwegian';
    5: Result := 'csISO60Norwegian1';
  else
    Error;
  end;
end;

class function TEncodingInfoISO60DanishNorwegian.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoISO60DanishNorwegian.MIBenum: Integer;
begin
  Result := 25;
end;

class function TEncodingInfoISO60DanishNorwegian.Name: string;
begin
  Result := 'NS_4551-1';
end;

class function TEncodingInfoISO60DanishNorwegian.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO69French }

class function TEncodingInfoISO69French.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NF_Z_62-010';
    1: Result := 'iso-ir-69';
    2: Result := 'ISO646-FR';
    3: Result := 'fr';
    4: Result := 'csISO69French';
  else
    Error;
  end;
end;

class function TEncodingInfoISO69French.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO69French.MIBenum: Integer;
begin
  Result := 26;
end;

class function TEncodingInfoISO69French.Name: string;
begin
  Result := 'NF_Z_62-010';
end;

class function TEncodingInfoISO69French.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO10646UTF1 }

class function TEncodingInfoISO10646UTF1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-10646-UTF-1';
    1: Result := 'csISO10646UTF1';
  else
    Error;
  end;
end;

class function TEncodingInfoISO10646UTF1.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoISO10646UTF1.MIBenum: Integer;
begin
  Result := 27;
end;

class function TEncodingInfoISO10646UTF1.Name: string;
begin
  Result := 'ISO-10646-UTF-1';
end;

class function TEncodingInfoISO10646UTF1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO646basic1983 }

class function TEncodingInfoISO646basic1983.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_646.basic:1983';
    1: Result := 'ref';
    2: Result := 'csISO646basic1983';
  else
    Error;
  end;
end;

class function TEncodingInfoISO646basic1983.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO646basic1983.MIBenum: Integer;
begin
  Result := 28;
end;

class function TEncodingInfoISO646basic1983.Name: string;
begin
  Result := 'ISO_646.basic:1983';
end;

class function TEncodingInfoISO646basic1983.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoInvariant }

class function TEncodingInfoInvariant.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'INVARIANT';
    1: Result := 'csINVARIANT';
  else
    Error;
  end;
end;

class function TEncodingInfoInvariant.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoInvariant.MIBenum: Integer;
begin
  Result := 29;
end;

class function TEncodingInfoInvariant.Name: string;
begin
  Result := 'INVARIANT';
end;

class function TEncodingInfoInvariant.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO2Int1RefVersion }

class function TEncodingInfoISO2Int1RefVersion.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_646.irv:1983';
    1: Result := 'iso-ir-2';
    2: Result := 'irv';
    3: Result := 'csISO2Int1RefVersion';
  else
    Error;
  end;
end;

class function TEncodingInfoISO2Int1RefVersion.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO2Int1RefVersion.MIBenum: Integer;
begin
  Result := 30;
end;

class function TEncodingInfoISO2Int1RefVersion.Name: string;
begin
  Result := 'ISO_646.irv:1983';
end;

class function TEncodingInfoISO2Int1RefVersion.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoNATSSEFI }

class function TEncodingInfoNATSSEFI.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NATS-SEFI';
    1: Result := 'iso-ir-8-1';
    2: Result := 'csNATSSEFI';
  else
    Error;
  end;
end;

class function TEncodingInfoNATSSEFI.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoNATSSEFI.MIBenum: Integer;
begin
  Result := 31;
end;

class function TEncodingInfoNATSSEFI.Name: string;
begin
  Result := 'NATS-SEFI';
end;

class function TEncodingInfoNATSSEFI.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoNATSSEFIADD }

class function TEncodingInfoNATSSEFIADD.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NATS-SEFI-ADD';
    1: Result := 'iso-ir-8-2';
    2: Result := 'csNATSSEFIADD';
  else
    Error;
  end;
end;

class function TEncodingInfoNATSSEFIADD.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoNATSSEFIADD.MIBenum: Integer;
begin
  Result := 32;
end;

class function TEncodingInfoNATSSEFIADD.Name: string;
begin
  Result := 'NATS-SEFI-ADD';
end;

class function TEncodingInfoNATSSEFIADD.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoNATSDANO }

class function TEncodingInfoNATSDANO.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NATS-DANO';
    1: Result := 'iso-ir-9-1';
    2: Result := 'csNATSDANO';
  else
    Error;
  end;
end;

class function TEncodingInfoNATSDANO.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoNATSDANO.MIBenum: Integer;
begin
  Result := 33;
end;

class function TEncodingInfoNATSDANO.Name: string;
begin
  Result := '';
end;

class function TEncodingInfoNATSDANO.PreferredMIMEName: string;
begin

end;

{ TEncodingInfoNATSDANOADD }

class function TEncodingInfoNATSDANOADD.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NATS-DANO-ADD';
    1: Result := 'iso-ir-9-2';
    2: Result := 'csNATSDANOADD';
  else
    Error;
  end;
end;

class function TEncodingInfoNATSDANOADD.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoNATSDANOADD.MIBenum: Integer;
begin
  Result := 34;
end;

class function TEncodingInfoNATSDANOADD.Name: string;
begin
  Result := 'NATS-DANO-ADD';
end;

class function TEncodingInfoNATSDANOADD.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO10Swedish }

class function TEncodingInfoISO10Swedish.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'SEN_850200_B';
    1: Result := 'iso-ir-10';
    2: Result := 'FI';
    3: Result := 'ISO646-FI';
    4: Result := 'ISO646-SE';
    5: Result := 'se';
    6: Result := 'csISO10Swedish';
  else
    Error;
  end;
end;

class function TEncodingInfoISO10Swedish.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoISO10Swedish.MIBenum: Integer;
begin
  Result := 35;
end;

class function TEncodingInfoISO10Swedish.Name: string;
begin
  Result := 'SEN_850200_B';
end;

class function TEncodingInfoISO10Swedish.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoKSC56011987 }

class function TEncodingInfoKSC56011987.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'KS_C_5601-1987';
    1: Result := 'iso-ir-149';
    2: Result := 'KS_C_5601-1989';
    3: Result := 'KSC_5601';
    4: Result := 'korean';
    5: Result := 'csKSC56011987';
  else
    Error;
  end;
end;

class function TEncodingInfoKSC56011987.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoKSC56011987.MIBenum: Integer;
begin
  Result := 36;
end;

class function TEncodingInfoKSC56011987.Name: string;
begin
  Result := 'KS_C_5601-1987';
end;

class function TEncodingInfoKSC56011987.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO2022KR }

class function TEncodingInfoISO2022KR.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-2022-KR';
    1: Result := 'csISO2022KR';
  else
    Error;
  end;
end;

class function TEncodingInfoISO2022KR.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoISO2022KR.MIBenum: Integer;
begin
  Result := 37;
end;

class function TEncodingInfoISO2022KR.Name: string;
begin
  Result := 'ISO-2022-KR';
end;

class function TEncodingInfoISO2022KR.PreferredMIMEName: string;
begin
  Result := 'ISO-2022-KR';
end;

{ TEncodingInfoEUCKR }

class function TEncodingInfoEUCKR.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EUC-KR';
    1: Result := 'csEUCKR';
  else
    Error;
  end;
end;

class function TEncodingInfoEUCKR.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEUCKR.MIBenum: Integer;
begin
  Result := 38;
end;

class function TEncodingInfoEUCKR.Name: string;
begin
  Result := 'EUC-KR';
end;

class function TEncodingInfoEUCKR.PreferredMIMEName: string;
begin
  Result := 'EUC-KR';
end;

{ TEncodingInfoISO2022JP }

class function TEncodingInfoISO2022JP.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-2022-JP';
    1: Result := 'csISO2022JP';
  else
    Error;
  end;
end;

class function TEncodingInfoISO2022JP.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoISO2022JP.MIBenum: Integer;
begin
  Result := 39;
end;

class function TEncodingInfoISO2022JP.Name: string;
begin
  Result := 'ISO-2022-JP';
end;

class function TEncodingInfoISO2022JP.PreferredMIMEName: string;
begin
  Result := 'ISO-2022-JP';
end;

{ TEncodingInfoISO2022JP2 }

class function TEncodingInfoISO2022JP2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-2022-JP-2';
    1: Result := 'csISO2022JP2';
  else
    Error;
  end;
end;

class function TEncodingInfoISO2022JP2.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoISO2022JP2.MIBenum: Integer;
begin
  Result := 40;
end;

class function TEncodingInfoISO2022JP2.Name: string;
begin
  Result := 'ISO-2022-JP-2';
end;

class function TEncodingInfoISO2022JP2.PreferredMIMEName: string;
begin
  Result := 'ISO-2022-JP-2';
end;

{ TEncodingInfoISO13JISC6220jp }

class function TEncodingInfoISO13JISC6220jp.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6220-1969-jp';
    1: Result := 'JIS_C6220-1969';
    2: Result := 'iso-ir-13';
    3: Result := 'katakana';
    4: Result := 'x0201-7';
    5: Result := 'csISO13JISC6220jp';
  else
    Error;
  end;
end;

class function TEncodingInfoISO13JISC6220jp.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoISO13JISC6220jp.MIBenum: Integer;
begin
  Result := 41;
end;

class function TEncodingInfoISO13JISC6220jp.Name: string;
begin
  Result := 'JIS_C6220-1969-jp';
end;

class function TEncodingInfoISO13JISC6220jp.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO14JISC6220ro }

class function TEncodingInfoISO14JISC6220ro.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6220-1969-ro';
    1: Result := 'iso-ir-14';
    2: Result := 'jp';
    3: Result := 'ISO646-JP';
    4: Result := 'csISO14JISC6220ro';
  else
    Error;
  end;
end;

class function TEncodingInfoISO14JISC6220ro.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO14JISC6220ro.MIBenum: Integer;
begin
  Result := 42;
end;

class function TEncodingInfoISO14JISC6220ro.Name: string;
begin
  Result := 'JIS_C6220-1969-ro';
end;

class function TEncodingInfoISO14JISC6220ro.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO16Portuguese }

class function TEncodingInfoISO16Portuguese.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'PT';
    1: Result := 'iso-ir-16';
    2: Result := 'ISO646-PT';
    3: Result := 'csISO16Portuguese';
  else
    Error;
  end;
end;

class function TEncodingInfoISO16Portuguese.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO16Portuguese.MIBenum: Integer;
begin
  Result := 43;
end;

class function TEncodingInfoISO16Portuguese.Name: string;
begin
  Result := 'PT';
end;

class function TEncodingInfoISO16Portuguese.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO18Greek7Old }

class function TEncodingInfoISO18Greek7Old.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'greek7-old';
    1: Result := 'iso-ir-18';
    2: Result := 'csISO18Greek7Old';
  else
    Error;
  end;
end;

class function TEncodingInfoISO18Greek7Old.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO18Greek7Old.MIBenum: Integer;
begin
  Result := 44;
end;

class function TEncodingInfoISO18Greek7Old.Name: string;
begin
  Result := 'greek7-old';
end;

class function TEncodingInfoISO18Greek7Old.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO19LatinGreek }

class function TEncodingInfoISO19LatinGreek.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'latin-greek';
    1: Result := 'iso-ir-19';
    2: Result := 'csISO19LatinGreek';
  else
    Error;
  end;
end;

class function TEncodingInfoISO19LatinGreek.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO19LatinGreek.MIBenum: Integer;
begin
  Result := 45;
end;

class function TEncodingInfoISO19LatinGreek.Name: string;
begin
  Result := 'latin-greek';
end;

class function TEncodingInfoISO19LatinGreek.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO25French }

class function TEncodingInfoISO25French.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NF_Z_62-010_(1973)';
    1: Result := 'iso-ir-25';
    2: Result := 'ISO646-FR1';
    3: Result := 'csISO25French';
  else
    Error;
  end;
end;

class function TEncodingInfoISO25French.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO25French.MIBenum: Integer;
begin
  Result := 46;
end;

class function TEncodingInfoISO25French.Name: string;
begin
  Result := 'NF_Z_62-010_(1973)';
end;

class function TEncodingInfoISO25French.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO27LatinGreek1 }

class function TEncodingInfoISO27LatinGreek1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Latin-greek-1';
    1: Result := 'iso-ir-27';
    2: Result := 'csISO27LatinGreek1';
  else
    Error;
  end;
end;

class function TEncodingInfoISO27LatinGreek1.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO27LatinGreek1.MIBenum: Integer;
begin
  Result := 47;
end;

class function TEncodingInfoISO27LatinGreek1.Name: string;
begin
  Result := 'Latin-greek-1';
end;

class function TEncodingInfoISO27LatinGreek1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO5427Cyrillic }

class function TEncodingInfoISO5427Cyrillic.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_5427';
    1: Result := 'iso-ir-37';
    2: Result := 'csISO5427Cyrillic';
  else
    Error;
  end;
end;

class function TEncodingInfoISO5427Cyrillic.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO5427Cyrillic.MIBenum: Integer;
begin
  Result := 48;
end;

class function TEncodingInfoISO5427Cyrillic.Name: string;
begin
  Result := 'ISO_5427';
end;

class function TEncodingInfoISO5427Cyrillic.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO42JISC62261978 }

class function TEncodingInfoISO42JISC62261978.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6226-1978';
    1: Result := 'iso-ir-42';
    2: Result := 'csISO42JISC62261978';
  else
    Error;
  end;
end;

class function TEncodingInfoISO42JISC62261978.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO42JISC62261978.MIBenum: Integer;
begin
  Result := 49;
end;

class function TEncodingInfoISO42JISC62261978.Name: string;
begin
  Result := 'JIS_C6226-1978';
end;

class function TEncodingInfoISO42JISC62261978.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO47BSViewdata }

class function TEncodingInfoISO47BSViewdata.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'BS_viewdata';
    1: Result := 'iso-ir-47';
    2: Result := 'csISO47BSViewdata';
  else
    Error;
  end;
end;

class function TEncodingInfoISO47BSViewdata.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO47BSViewdata.MIBenum: Integer;
begin
  Result := 50;
end;

class function TEncodingInfoISO47BSViewdata.Name: string;
begin
  Result := 'BS_viewdata';
end;

class function TEncodingInfoISO47BSViewdata.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO49INIS }

class function TEncodingInfoISO49INIS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'INIS';
    1: Result := 'iso-ir-49';
    2: Result := 'csISO49INIS';
  else
    Error;
  end;
end;

class function TEncodingInfoISO49INIS.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO49INIS.MIBenum: Integer;
begin
  Result := 51;
end;

class function TEncodingInfoISO49INIS.Name: string;
begin
  Result := 'INIS';
end;

class function TEncodingInfoISO49INIS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO50INIS8 }

class function TEncodingInfoISO50INIS8.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'INIS-8';
    1: Result := 'iso-ir-50';
    2: Result := 'csISO50INIS8';
  else
    Error;
  end;
end;

class function TEncodingInfoISO50INIS8.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO50INIS8.MIBenum: Integer;
begin
  Result := 52;
end;

class function TEncodingInfoISO50INIS8.Name: string;
begin
  Result := 'INIS-8';
end;

class function TEncodingInfoISO50INIS8.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO51INISCyrillic }

class function TEncodingInfoISO51INISCyrillic.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'INIS-cyrillic';
    1: Result := 'iso-ir-51';
    2: Result := 'csISO51INISCyrillic';
  else
    Error;
  end;
end;

class function TEncodingInfoISO51INISCyrillic.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO51INISCyrillic.MIBenum: Integer;
begin
  Result := 53;
end;

class function TEncodingInfoISO51INISCyrillic.Name: string;
begin
  Result := 'INIS-cyrillic';
end;

class function TEncodingInfoISO51INISCyrillic.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO5427Cyrillic1981 }

class function TEncodingInfoISO5427Cyrillic1981.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_5427:1981';
    1: Result := 'iso-ir-54';
    2: Result := 'ISO5427Cyrillic1981';
  else
    Error;
  end;
end;

class function TEncodingInfoISO5427Cyrillic1981.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO5427Cyrillic1981.MIBenum: Integer;
begin
  Result := 54;
end;

class function TEncodingInfoISO5427Cyrillic1981.Name: string;
begin
  Result := 'ISO_5427:1981';
end;

class function TEncodingInfoISO5427Cyrillic1981.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO5428Greek }

class function TEncodingInfoISO5428Greek.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_5428:1980';
    1: Result := 'iso-ir-55';
    2: Result := 'csISO5428Greek';
  else
    Error;
  end;
end;

class function TEncodingInfoISO5428Greek.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO5428Greek.MIBenum: Integer;
begin
  Result := 55;
end;

class function TEncodingInfoISO5428Greek.Name: string;
begin
  Result := 'ISO_5428:1980';
end;

class function TEncodingInfoISO5428Greek.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO57GB1988 }

class function TEncodingInfoISO57GB1988.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'GB_1988-80';
    1: Result := 'iso-ir-57';
    2: Result := 'cn';
    3: Result := 'ISO646-CN';
    4: Result := 'csISO57GB1988';
  else
    Error;
  end;
end;

class function TEncodingInfoISO57GB1988.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO57GB1988.MIBenum: Integer;
begin
  Result := 56;
end;

class function TEncodingInfoISO57GB1988.Name: string;
begin
  Result := 'GB_1988-80';
end;

class function TEncodingInfoISO57GB1988.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO58GB231280 }

class function TEncodingInfoISO58GB231280.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'GB_2312-80';
    1: Result := 'iso-ir-58';
    2: Result := 'chinese';
    3: Result := 'csISO58GB231280';
  else
    Error;
  end;
end;

class function TEncodingInfoISO58GB231280.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO58GB231280.MIBenum: Integer;
begin
  Result := 57;
end;

class function TEncodingInfoISO58GB231280.Name: string;
begin
  Result := 'GB_2312-80';
end;

class function TEncodingInfoISO58GB231280.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO61Norwegian2 }

class function TEncodingInfoISO61Norwegian2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NS_4551-2';
    1: Result := 'ISO646-NO2';
    2: Result := 'iso-ir-61';
    3: Result := 'no2';
    4: Result := 'csISO61Norwegian2';
  else
    Error;
  end;
end;

class function TEncodingInfoISO61Norwegian2.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO61Norwegian2.MIBenum: Integer;
begin
  Result := 58;
end;

class function TEncodingInfoISO61Norwegian2.Name: string;
begin
  Result := 'NS_4551-2';
end;

class function TEncodingInfoISO61Norwegian2.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO70VideotexSupp1 }

class function TEncodingInfoISO70VideotexSupp1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'videotex-suppl';
    1: Result := 'iso-ir-70';
    2: Result := 'csISO70VideotexSupp1';
  else
    Error;
  end;
end;

class function TEncodingInfoISO70VideotexSupp1.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO70VideotexSupp1.MIBenum: Integer;
begin
  Result := 59;
end;

class function TEncodingInfoISO70VideotexSupp1.Name: string;
begin
  Result := 'videotex-suppl';
end;

class function TEncodingInfoISO70VideotexSupp1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO84Portuguese2 }

class function TEncodingInfoISO84Portuguese2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'PT2';
    1: Result := 'iso-ir-84';
    2: Result := 'ISO646-PT2';
    3: Result := 'csISO84Portuguese2';
  else
    Error;
  end;
end;

class function TEncodingInfoISO84Portuguese2.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO84Portuguese2.MIBenum: Integer;
begin
  Result := 60;
end;

class function TEncodingInfoISO84Portuguese2.Name: string;
begin
  Result := 'PT2';
end;

class function TEncodingInfoISO84Portuguese2.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO85Spanish2 }

class function TEncodingInfoISO85Spanish2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ES2';
    1: Result := 'iso-ir-85';
    2: Result := 'ISO646-ES2';
    3: Result := 'csISO85Spanish2';
  else
    Error;
  end;
end;

class function TEncodingInfoISO85Spanish2.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO85Spanish2.MIBenum: Integer;
begin
  Result := 61;
end;

class function TEncodingInfoISO85Spanish2.Name: string;
begin
  Result := 'ES2';
end;

class function TEncodingInfoISO85Spanish2.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO86Hungarian }

class function TEncodingInfoISO86Hungarian.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'MSZ_7795.3';
    1: Result := 'iso-ir-86';
    2: Result := 'ISO646-HU';
    3: Result := 'hu';
    4: Result := 'csISO86Hungarian';
  else
    Error;
  end;
end;

class function TEncodingInfoISO86Hungarian.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO86Hungarian.MIBenum: Integer;
begin
  Result := 62;
end;

class function TEncodingInfoISO86Hungarian.Name: string;
begin
  Result := 'MSZ_7795.3';
end;

class function TEncodingInfoISO86Hungarian.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO87JISX0208 }

class function TEncodingInfoISO87JISX0208.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6226-1983';
    1: Result := 'iso-ir-87';
    2: Result := 'x0208';
    3: Result := 'JIS_X0208-1983';
    4: Result := 'csISO87JISX0208';
  else
    Error;
  end;
end;

class function TEncodingInfoISO87JISX0208.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO87JISX0208.MIBenum: Integer;
begin
  Result := 63;
end;

class function TEncodingInfoISO87JISX0208.Name: string;
begin
  Result := 'JIS_C6226-1983';
end;

class function TEncodingInfoISO87JISX0208.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO88Greek7 }

class function TEncodingInfoISO88Greek7.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'greek7';
    1: Result := 'iso-ir-88';
    2: Result := 'csISO88Greek7';
  else
    Error;
  end;
end;

class function TEncodingInfoISO88Greek7.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO88Greek7.MIBenum: Integer;
begin
  Result := 64;
end;

class function TEncodingInfoISO88Greek7.Name: string;
begin
  Result := 'greek7';
end;

class function TEncodingInfoISO88Greek7.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO89ASMO449 }

class function TEncodingInfoISO89ASMO449.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ASMO_449';
    1: Result := 'ISO_9036';
    2: Result := 'arabic7';
    3: Result := 'iso-ir-89';
    4: Result := 'csISO89ASMO449';
  else
    Error;
  end;
end;

class function TEncodingInfoISO89ASMO449.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO89ASMO449.MIBenum: Integer;
begin
  Result := 65;
end;

class function TEncodingInfoISO89ASMO449.Name: string;
begin
  Result := 'ASMO_449';
end;

class function TEncodingInfoISO89ASMO449.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO90 }

class function TEncodingInfoISO90.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'iso-ir-90';
    1: Result := 'csISO90';
  else
    Error;
  end;
end;

class function TEncodingInfoISO90.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoISO90.MIBenum: Integer;
begin
  Result := 66;
end;

class function TEncodingInfoISO90.Name: string;
begin
  Result := 'iso-ir-90';
end;

class function TEncodingInfoISO90.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO91JISC62291984a }

class function TEncodingInfoISO91JISC62291984a.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6229-1984-a';
    1: Result := 'iso-ir-91';
    2: Result := 'jp-ocr-a';
    3: Result := 'csISO91JISC62291984a';
  else
    Error;
  end;
end;

class function TEncodingInfoISO91JISC62291984a.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO91JISC62291984a.MIBenum: Integer;
begin
  Result := 67;
end;

class function TEncodingInfoISO91JISC62291984a.Name: string;
begin
  Result := 'JIS_C6229-1984-a';
end;

class function TEncodingInfoISO91JISC62291984a.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO92JISC62991984b }

class function TEncodingInfoISO92JISC62991984b.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6229-1984-b';
    1: Result := 'iso-ir-92';
    2: Result := 'ISO646-JP-OCR-B';
    3: Result := 'jp-ocr-b';
    4: Result := 'csISO92JISC62291984b';
  else
    Error;
  end;
end;

class function TEncodingInfoISO92JISC62991984b.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO92JISC62991984b.MIBenum: Integer;
begin
  Result := 68;
end;

class function TEncodingInfoISO92JISC62991984b.Name: string;
begin
  Result := 'JIS_C6229-1984-b';
end;

class function TEncodingInfoISO92JISC62991984b.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO93JIS62291984badd }

class function TEncodingInfoISO93JIS62291984badd.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6229-1984-b-add';
    1: Result := 'iso-ir-93';
    2: Result := 'jp-ocr-b-add';
    3: Result := 'csISO93JISC62291984badd';
  else
    Error;
  end;
end;

class function TEncodingInfoISO93JIS62291984badd.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO93JIS62291984badd.MIBenum: Integer;
begin
  Result := 69;
end;

class function TEncodingInfoISO93JIS62291984badd.Name: string;
begin
  Result := 'JIS_C6229-1984-b-add';
end;

class function TEncodingInfoISO93JIS62291984badd.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO94JIS62291984hand }

class function TEncodingInfoISO94JIS62291984hand.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6229-1984-hand';
    1: Result := 'iso-ir-94';
    2: Result := 'jp-ocr-hand';
    3: Result := 'csISO94JISC62291984hand';
  else
    Error;
  end;
end;

class function TEncodingInfoISO94JIS62291984hand.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO94JIS62291984hand.MIBenum: Integer;
begin
  Result := 70;
end;

class function TEncodingInfoISO94JIS62291984hand.Name: string;
begin
  Result := 'JIS_C6229-1984-hand';
end;

class function TEncodingInfoISO94JIS62291984hand.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO95JIS62291984handadd }

class function TEncodingInfoISO95JIS62291984handadd.Alias(
  I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6229-1984-hand-add';
    1: Result := 'iso-ir-95';
    2: Result := 'jp-ocr-hand-add';
    3: Result := 'csISO95JISC62291984handadd';
  else
    Error;
  end;
end;

class function TEncodingInfoISO95JIS62291984handadd.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO95JIS62291984handadd.MIBenum: Integer;
begin
  Result := 71;
end;

class function TEncodingInfoISO95JIS62291984handadd.Name: string;
begin
  Result := 'JIS_C6229-1984-hand-add';
end;

class function TEncodingInfoISO95JIS62291984handadd.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO96JISC62291984kana }

class function TEncodingInfoISO96JISC62291984kana.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_C6229-1984-kana';
    1: Result := 'iso-ir-96';
    2: Result := 'jp-ocr-hand';
    3: Result := 'csISO96JISC62291984kana';
  else
    Error;
  end;
end;

class function TEncodingInfoISO96JISC62291984kana.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO96JISC62291984kana.MIBenum: Integer;
begin
  Result := 72;
end;

class function TEncodingInfoISO96JISC62291984kana.Name: string;
begin
  Result := 'JIS_C6229-1984-kana';
end;

class function TEncodingInfoISO96JISC62291984kana.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO2033 }

class function TEncodingInfoISO2033.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_2033-1983';
    1: Result := 'iso-ir-98';
    2: Result := 'e13b';
    3: Result := 'csISO2033';
  else
    Error;
  end;
end;

class function TEncodingInfoISO2033.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO2033.MIBenum: Integer;
begin
  Result := 73;
end;

class function TEncodingInfoISO2033.Name: string;
begin
  Result := 'ISO_2033-1983';
end;

class function TEncodingInfoISO2033.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO99NAPLPS }

class function TEncodingInfoISO99NAPLPS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ANSI_X3.110-1983';
    1: Result := 'iso-ir-99';
    2: Result := 'CSA_T500-1983';
    3: Result := 'NAPLPS';
    4: Result := 'csISO99NAPLPS';
  else
    Error;
  end;
end;

class function TEncodingInfoISO99NAPLPS.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO99NAPLPS.MIBenum: Integer;
begin
  Result := 74;
end;

class function TEncodingInfoISO99NAPLPS.Name: string;
begin
  Result := 'ANSI_X3.110-1983';
end;

class function TEncodingInfoISO99NAPLPS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO102T617bit }

class function TEncodingInfoISO102T617bit.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'T.61-7bit';
    1: Result := 'iso-ir-102';
    2: Result := 'csISO102T617bit';
  else
    Error;
  end;
end;

class function TEncodingInfoISO102T617bit.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO102T617bit.MIBenum: Integer;
begin
  Result := 75;
end;

class function TEncodingInfoISO102T617bit.Name: string;
begin
  Result := 'T.61-7bit';
end;

class function TEncodingInfoISO102T617bit.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO103T618bit }

class function TEncodingInfoISO103T618bit.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'T.61-8bit';
    1: Result := 'T.61';
    2: Result := 'iso-ir-103';
    3: Result := 'csISO103T618bit';
  else
    Error;
  end;
end;

class function TEncodingInfoISO103T618bit.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO103T618bit.MIBenum: Integer;
begin
  Result := 76;
end;

class function TEncodingInfoISO103T618bit.Name: string;
begin
  Result := 'T.61-8bit';
end;

class function TEncodingInfoISO103T618bit.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO111ECMACyrillic }

class function TEncodingInfoISO111ECMACyrillic.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ECMA-cyrillic';
    1: Result := 'iso-ir-111';
    2: Result := 'csISO111ECMACyrillic';
  else
    Error;
  end;
end;

class function TEncodingInfoISO111ECMACyrillic.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO111ECMACyrillic.MIBenum: Integer;
begin
  Result := 77;
end;

class function TEncodingInfoISO111ECMACyrillic.Name: string;
begin
  Result := 'ECMA-cyrillic';
end;

class function TEncodingInfoISO111ECMACyrillic.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO121Canadian1 }

class function TEncodingInfoISO121Canadian1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'CSA_Z243.4-1985-1';
    1: Result := 'iso-ir-121';
    2: Result := 'ISO646-CA';
    3: Result := 'csa7-1';
    4: Result := 'ca';
    5: Result := 'csISO121Canadian1';
  else
    Error;
  end;
end;

class function TEncodingInfoISO121Canadian1.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoISO121Canadian1.MIBenum: Integer;
begin
  Result := 78;
end;

class function TEncodingInfoISO121Canadian1.Name: string;
begin
  Result := 'CSA_Z243.4-1985-1';
end;

class function TEncodingInfoISO121Canadian1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO122Canadian2 }

class function TEncodingInfoISO122Canadian2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'CSA_Z243.4-1985-2';
    1: Result := 'iso-ir-122';
    2: Result := 'ISO646-CA2';
    3: Result := 'csa7-2';
    4: Result := 'csISO122Canadian2';
  else
    Error;
  end;
end;

class function TEncodingInfoISO122Canadian2.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO122Canadian2.MIBenum: Integer;
begin
  Result := 79;
end;

class function TEncodingInfoISO122Canadian2.Name: string;
begin
  Result := 'CSA_Z243.4-1985-2';
end;

class function TEncodingInfoISO122Canadian2.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO123CSAZ24341985gr }

class function TEncodingInfoISO123CSAZ24341985gr.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'CSA_Z243.4-1985-gr';
    1: Result := 'iso-ir-123';
    2: Result := 'csISO123CSAZ24341985gr';
  else
    Error;
  end;
end;

class function TEncodingInfoISO123CSAZ24341985gr.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO123CSAZ24341985gr.MIBenum: Integer;
begin
  Result := 80;
end;

class function TEncodingInfoISO123CSAZ24341985gr.Name: string;
begin
  Result := 'CSA_Z243.4-1985-gr';
end;

class function TEncodingInfoISO123CSAZ24341985gr.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO88596E }

class function TEncodingInfoISO88596E.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-6-E';
    1: Result := 'csISO88596E';
    2: Result := 'ISO-8859-6-E';
  else
    Error;
  end;
end;

class function TEncodingInfoISO88596E.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO88596E.MIBenum: Integer;
begin
  Result := 81;
end;

class function TEncodingInfoISO88596E.Name: string;
begin
  Result := 'ISO_8859-6-E';
end;

class function TEncodingInfoISO88596E.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-6-E';
end;

{ TEncodingInfoISO88596I }

class function TEncodingInfoISO88596I.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-6-I';
    1: Result := 'csISO88596I';
    2: Result := 'ISO-8859-6-I';
  else
    Error;
  end;
end;

class function TEncodingInfoISO88596I.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO88596I.MIBenum: Integer;
begin
  Result := 82;
end;

class function TEncodingInfoISO88596I.Name: string;
begin
  Result := 'ISO_8859-6-I';
end;

class function TEncodingInfoISO88596I.PreferredMIMEName: string;
begin
  Result := 'ISO-8859-6-I';
end;

{ TEncodingInfoISO128T101G2 }

class function TEncodingInfoISO128T101G2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'T.101-G2';
    1: Result := 'iso-ir-128';
    2: Result := 'csISO128T101G2';
  else
    Error;
  end;
end;

class function TEncodingInfoISO128T101G2.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO128T101G2.MIBenum: Integer;
begin
  Result := 83;
end;

class function TEncodingInfoISO128T101G2.Name: string;
begin
  Result := 'T.101-G2';
end;

class function TEncodingInfoISO128T101G2.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO88598E }

class function TEncodingInfoISO88598E.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-8-E';
    1: Result := 'csISO88598E';
    2: Result := 'ISO-8859-8-E';
  else
    Error;
  end;
end;

class function TEncodingInfoISO88598E.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO88598E.MIBenum: Integer;
begin
  Result := 84;
end;

class function TEncodingInfoISO88598E.Name: string;
begin
  Result := 'ISO_8859-8-E';
end;

class function TEncodingInfoISO88598E.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO88598I }

class function TEncodingInfoISO88598I.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-8-I';
    1: Result := 'csISO88598I';
    2: Result := 'ISO-8859-8-I';
  else
    Error;
  end;
end;

class function TEncodingInfoISO88598I.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO88598I.MIBenum: Integer;
begin
  Result := 85;
end;

class function TEncodingInfoISO88598I.Name: string;
begin
  Result := 'ISO_8859-8-I';
end;

class function TEncodingInfoISO88598I.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO139CSN369103 }

class function TEncodingInfoISO139CSN369103.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'CSN_369103';
    1: Result := 'iso-ir-139';
    2: Result := 'csISO139CSN369103';
  else
    Error;
  end;
end;

class function TEncodingInfoISO139CSN369103.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO139CSN369103.MIBenum: Integer;
begin
  Result := 86;
end;

class function TEncodingInfoISO139CSN369103.Name: string;
begin
  Result := 'CSN_369103';
end;

class function TEncodingInfoISO139CSN369103.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO141JUSIB1002 }

class function TEncodingInfoISO141JUSIB1002.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JUS_I.B1.002';
    1: Result := 'iso-ir-141';
    2: Result := 'ISO646-YU';
    3: Result := 'js';
    4: Result := 'yu';
    5: Result := 'csISO141JUSIB1002';
  else
    Error;
  end;
end;

class function TEncodingInfoISO141JUSIB1002.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoISO141JUSIB1002.MIBenum: Integer;
begin
  Result := 87;
end;

class function TEncodingInfoISO141JUSIB1002.Name: string;
begin
  Result := 'JUS_I.B1.002';
end;

class function TEncodingInfoISO141JUSIB1002.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO143IECP271 }

class function TEncodingInfoISO143IECP271.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IEC_P27-1';
    1: Result := 'iso-ir-143';
    2: Result := 'csISO143IECP271';
  else
    Error;
  end;
end;

class function TEncodingInfoISO143IECP271.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO143IECP271.MIBenum: Integer;
begin
  Result := 88;
end;

class function TEncodingInfoISO143IECP271.Name: string;
begin
  Result := 'IEC_P27-1';
end;

class function TEncodingInfoISO143IECP271.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO146Serbian }

class function TEncodingInfoISO146Serbian.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JUS_I.B1.003-serb';
    1: Result := 'iso-ir-146';
    2: Result := 'serbian';
    3: Result := 'csISO146Serbian';
  else
    Error;
  end;
end;

class function TEncodingInfoISO146Serbian.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO146Serbian.MIBenum: Integer;
begin
  Result := 89;
end;

class function TEncodingInfoISO146Serbian.Name: string;
begin
  Result := 'JUS_I.B1.003-serb';
end;

class function TEncodingInfoISO146Serbian.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO147Macedonian }

class function TEncodingInfoISO147Macedonian.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JUS_I.B1.003-mac';
    1: Result := 'macedonian';
    2: Result := 'iso-ir-147';
    3: Result := 'csISO147Macedonian';
  else
    Error;
  end;
end;

class function TEncodingInfoISO147Macedonian.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO147Macedonian.MIBenum: Integer;
begin
  Result := 90;
end;

class function TEncodingInfoISO147Macedonian.Name: string;
begin
  Result := 'JUS_I.B1.003-mac';
end;

class function TEncodingInfoISO147Macedonian.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO150GreekCCITT }

class function TEncodingInfoISO150GreekCCITT.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'greek-ccitt';
    1: Result := 'iso-ir-150';
    2: Result := 'csISO150';
    3: Result := 'csISO150GreekCCITT';
  else
    Error;
  end;
end;

class function TEncodingInfoISO150GreekCCITT.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO150GreekCCITT.MIBenum: Integer;
begin
  Result := 91;
end;

class function TEncodingInfoISO150GreekCCITT.Name: string;
begin
  Result := 'greek-ccitt';
end;

class function TEncodingInfoISO150GreekCCITT.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO151Cuba }

class function TEncodingInfoISO151Cuba.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'NC_NC00-10:81';
    1: Result := 'cuba';
    2: Result := 'iso-ir-151';
    3: Result := 'ISO646-CU';
    4: Result := 'csISO151Cuba';
  else
    Error;
  end;
end;

class function TEncodingInfoISO151Cuba.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO151Cuba.MIBenum: Integer;
begin
  Result := 92;
end;

class function TEncodingInfoISO151Cuba.Name: string;
begin
  Result := 'NC_NC00-10:81';
end;

class function TEncodingInfoISO151Cuba.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO6937Add }

class function TEncodingInfoISO6937Add.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_6937-2-25';
    1: Result := 'iso-ir-152';
    2: Result := 'csISO6937Add';
  else
    Error;
  end;
end;

class function TEncodingInfoISO6937Add.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO6937Add.MIBenum: Integer;
begin
  Result := 93;
end;

class function TEncodingInfoISO6937Add.Name: string;
begin
  Result := 'ISO_6937-2-25';
end;

class function TEncodingInfoISO6937Add.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO153GOST1976874 }

class function TEncodingInfoISO153GOST1976874.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'GOST_19768-74';
    1: Result := 'ST_SEV_358-88';
    2: Result := 'iso-ir-153';
    3: Result := 'csISO153GOST1976874';
  else
    Error;
  end;
end;

class function TEncodingInfoISO153GOST1976874.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO153GOST1976874.MIBenum: Integer;
begin
  Result := 94;
end;

class function TEncodingInfoISO153GOST1976874.Name: string;
begin
  Result := 'GOST_19768-74';
end;

class function TEncodingInfoISO153GOST1976874.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO8859Supp }

class function TEncodingInfoISO8859Supp.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_8859-supp';
    1: Result := 'iso-ir-154';
    2: Result := 'latin1-2-5';
    3: Result := 'csISO8859Supp';
  else
    Error;
  end;
end;

class function TEncodingInfoISO8859Supp.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO8859Supp.MIBenum: Integer;
begin
  Result := 95;
end;

class function TEncodingInfoISO8859Supp.Name: string;
begin
  Result := 'ISO_8859-supp';
end;

class function TEncodingInfoISO8859Supp.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO10367Box }

class function TEncodingInfoISO10367Box.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO_10367-box';
    1: Result := 'iso-ir-155';
    2: Result := 'csISO10367Box';
  else
    Error;
  end;
end;

class function TEncodingInfoISO10367Box.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoISO10367Box.MIBenum: Integer;
begin
  Result := 96;
end;

class function TEncodingInfoISO10367Box.Name: string;
begin
  Result := 'ISO_10367-box';
end;

class function TEncodingInfoISO10367Box.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO158Lap }

class function TEncodingInfoISO158Lap.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'latin-lap';
    1: Result := 'lap';
    2: Result := 'iso-ir-158';
    3: Result := 'csISO158Lap';
  else
    Error;
  end;
end;

class function TEncodingInfoISO158Lap.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO158Lap.MIBenum: Integer;
begin
  Result := 97;
end;

class function TEncodingInfoISO158Lap.Name: string;
begin
  Result := 'latin-lap';
end;

class function TEncodingInfoISO158Lap.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO159JISX02121990 }

class function TEncodingInfoISO159JISX02121990.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'JIS_X0212-1990';
    1: Result := 'x0212';
    2: Result := 'iso-ir-159';
    3: Result := 'csISO159JISX02121990';
  else
    Error;
  end;
end;

class function TEncodingInfoISO159JISX02121990.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO159JISX02121990.MIBenum: Integer;
begin
  Result := 98;
end;

class function TEncodingInfoISO159JISX02121990.Name: string;
begin
  Result := 'JIS_X0212-1990';
end;

class function TEncodingInfoISO159JISX02121990.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO646Danish }

class function TEncodingInfoISO646Danish.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'DS_2089';
    1: Result := 'DS2089';
    2: Result := 'ISO646-DK';
    3: Result := 'dk';
    4: Result := 'csISO646Danish';
  else
    Error;
  end;
end;

class function TEncodingInfoISO646Danish.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoISO646Danish.MIBenum: Integer;
begin
  Result := 99;
end;

class function TEncodingInfoISO646Danish.Name: string;
begin
  Result := 'DS_2089';
end;

class function TEncodingInfoISO646Danish.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUSDK }

class function TEncodingInfoUSDK.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'us-dk';
    1: Result := 'csUSDK';
  else
    Error;
  end;
end;

class function TEncodingInfoUSDK.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUSDK.MIBenum: Integer;
begin
  Result := 100;
end;

class function TEncodingInfoUSDK.Name: string;
begin
  Result := 'us-dk';
end;

class function TEncodingInfoUSDK.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoDKUS }

class function TEncodingInfoDKUS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'dk-us';
    1: Result := 'csDKUS';
  else
    Error;
  end;
end;

class function TEncodingInfoDKUS.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoDKUS.MIBenum: Integer;
begin
  Result := 101;
end;

class function TEncodingInfoDKUS.Name: string;
begin
  Result := 'dk-us';
end;

class function TEncodingInfoDKUS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoKSC5636 }

class function TEncodingInfoKSC5636.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'KSC5636';
    1: Result := 'ISO646-KR';
    2: Result := 'csKSC5636';
  else
    Error;
  end;
end;

class function TEncodingInfoKSC5636.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoKSC5636.MIBenum: Integer;
begin
  Result := 102;
end;

class function TEncodingInfoKSC5636.Name: string;
begin
  Result := 'KSC5636';
end;

class function TEncodingInfoKSC5636.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicode11UTF7 }

class function TEncodingInfoUnicode11UTF7.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UNICODE-1-1-UTF-7';
    1: Result := 'csUnicode11UTF7';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicode11UTF7.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicode11UTF7.MIBenum: Integer;
begin
  Result := 103;
end;

class function TEncodingInfoUnicode11UTF7.Name: string;
begin
  Result := 'UNICODE-1-1-UTF-7';
end;

class function TEncodingInfoUnicode11UTF7.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO2022CN }

class function TEncodingInfoISO2022CN.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-2022-CN';
  else
    Error;
  end;
end;

class function TEncodingInfoISO2022CN.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoISO2022CN.MIBenum: Integer;
begin
  Result := 104;
end;

class function TEncodingInfoISO2022CN.Name: string;
begin
  Result := 'ISO-2022-CN';
end;

class function TEncodingInfoISO2022CN.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO2022CNEXT }

class function TEncodingInfoISO2022CNEXT.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-2022-CN-EXT';
  else
    Error;
  end;
end;

class function TEncodingInfoISO2022CNEXT.AliasCount: Integer;
begin
   Result := 1;
end;

class function TEncodingInfoISO2022CNEXT.MIBenum: Integer;
begin
  Result := 105;
end;

class function TEncodingInfoISO2022CNEXT.Name: string;
begin
  Result := 'ISO-2022-CN-EXT';
end;

class function TEncodingInfoISO2022CNEXT.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF8 }

class function TEncodingInfoUTF8.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-8';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF8.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF8.MIBenum: Integer;
begin
  Result := 106;
end;

class function TEncodingInfoUTF8.Name: string;
begin
  Result := 'UTF-8';
end;

class function TEncodingInfoUTF8.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO885913 }

class function TEncodingInfoISO885913.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-13';
    1: Result := 'ISO_8859-13';  // Non-standard alias
    2: Result := 'latin7';       // Non-standard alias
    3: Result := 'l7';           // Non-standard alias
  else
    Error;
  end;
end;

class function TEncodingInfoISO885913.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO885913.MIBenum: Integer;
begin
  Result := 109;
end;

class function TEncodingInfoISO885913.Name: string;
begin
  Result := 'ISO-8859-13';
end;

class function TEncodingInfoISO885913.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIsoLatin8 }

class function TEncodingInfoIsoLatin8.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-14';
    1: Result := 'iso-ir-199';
    2: Result := 'ISO_8859-14:1998';
    3: Result := 'ISO_8859-14';
    4: Result := 'latin8';
    5: Result := 'iso-celtic';
    6: Result := 'l8';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin8.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIsoLatin8.MIBenum: Integer;
begin
  Result := 110;
end;

class function TEncodingInfoIsoLatin8.Name: string;
begin
  Result := 'ISO-8859-14';
end;

class function TEncodingInfoIsoLatin8.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIsoLatin9 }

class function TEncodingInfoIsoLatin9.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-15';
    1: Result := 'ISO_8869-15';
    2: Result := 'Latin-9';
    3: Result := 'latin9';      // Non-standard alias
    4: Result := 'l9';          // Non-standard alias
    5: Result := 'latin0';      // Non-standard alias
    6: Result := 'l0';          // Non-standard alias
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin9.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIsoLatin9.MIBenum: Integer;
begin
  Result := 111;
end;

class function TEncodingInfoIsoLatin9.Name: string;
begin
  Result := 'ISO-8859-15';
end;

class function TEncodingInfoIsoLatin9.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIsoLatin10 }

class function TEncodingInfoIsoLatin10.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-16';
    1: Result := 'iso-ir-226';
    2: Result := 'ISO_8859-16:2001';
    3: Result := 'ISO_8859-16';
    4: Result := 'latin10';
    5: Result := 'l10';
  else
    Error;
  end;
end;

class function TEncodingInfoIsoLatin10.AliasCount: Integer;
begin
  Result := 6;
end;

class function TEncodingInfoIsoLatin10.MIBenum: Integer;
begin
  Result := 112;
end;

class function TEncodingInfoIsoLatin10.Name: string;
begin
  Result := 'ISO-8859-16';
end;

class function TEncodingInfoIsoLatin10.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoGBK }

class function TEncodingInfoGBK.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'GBK';
    1: Result := 'CP936';
    2: Result := 'MS936';
    3: Result := 'windows-936';
  else
    Error;
  end;
end;

class function TEncodingInfoGBK.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoGBK.MIBenum: Integer;
begin
  Result := 113;
end;

class function TEncodingInfoGBK.Name: string;
begin
  Result := 'GBK';
end;

class function TEncodingInfoGBK.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoGB18030 }

class function TEncodingInfoGB18030.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'GB18030';
  else
    Error;
  end;
end;

class function TEncodingInfoGB18030.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoGB18030.MIBenum: Integer;
begin
  Result := 114;
end;

class function TEncodingInfoGB18030.Name: string;
begin
  Result := 'GB18030';
end;

class function TEncodingInfoGB18030.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoOSD_EBCDIC_DF04_15 }

class function TEncodingInfoOSD_EBCDIC_DF04_15.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'OSD_EBCDIC_DF04_15';
  else
    Error;
  end;
end;

class function TEncodingInfoOSD_EBCDIC_DF04_15.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoOSD_EBCDIC_DF04_15.MIBenum: Integer;
begin
  Result := 115;
end;

class function TEncodingInfoOSD_EBCDIC_DF04_15.Name: string;
begin
  Result := 'OSD_EBCDIC_DF04_15';
end;

class function TEncodingInfoOSD_EBCDIC_DF04_15.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoOSD_EBCDIC_DF03_IRV }

class function TEncodingInfoOSD_EBCDIC_DF03_IRV.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'OSD_EBCDIC_DF03_IRV';
  else
    Error;
  end;
end;

class function TEncodingInfoOSD_EBCDIC_DF03_IRV.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoOSD_EBCDIC_DF03_IRV.MIBenum: Integer;
begin
  Result := 116;
end;

class function TEncodingInfoOSD_EBCDIC_DF03_IRV.Name: string;
begin
  Result := 'OSD_EBCDIC_DF03_IRV';
end;

class function TEncodingInfoOSD_EBCDIC_DF03_IRV.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoOSD_EBCDIC_DF04_1 }

class function TEncodingInfoOSD_EBCDIC_DF04_1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'OSD_EBCDIC_DF04_1';
  else
    Error;
  end;
end;

class function TEncodingInfoOSD_EBCDIC_DF04_1.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoOSD_EBCDIC_DF04_1.MIBenum: Integer;
begin
  Result := 117;
end;

class function TEncodingInfoOSD_EBCDIC_DF04_1.Name: string;
begin
  Result := 'OSD_EBCDIC_DF04_1';
end;

class function TEncodingInfoOSD_EBCDIC_DF04_1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO115481 }

class function TEncodingInfoISO115481.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-11548-1';
    1: Result := 'ISO_11548-1';
    2: Result := 'ISO_TR_11548-1';
    3: Result := 'csISO115481';
  else
    Error;
  end;
end;

class function TEncodingInfoISO115481.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoISO115481.MIBenum: Integer;
begin
  Result := 118;
end;

class function TEncodingInfoISO115481.Name: string;
begin
  Result := 'ISO-11548-1';
end;

class function TEncodingInfoISO115481.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoKZ1048 }

class function TEncodingInfoKZ1048.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'KZ-1048';
    1: Result := 'STRK1048-2002';
    2: Result := 'RK1048';
    3: Result := 'csKZ1048';
  else
    Error;
  end;
end;

class function TEncodingInfoKZ1048.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoKZ1048.MIBenum: Integer;
begin
  Result := 119;
end;

class function TEncodingInfoKZ1048.Name: string;
begin
  Result := 'KZ-1048';
end;

class function TEncodingInfoKZ1048.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUCS2 }

class function TEncodingInfoUCS2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-10646-UCS-2';
    1: Result := 'csUnicode';
  else
    Error;
  end;
end;

class function TEncodingInfoUCS2.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUCS2.MIBenum: Integer;
begin
  Result := 1000;
end;

class function TEncodingInfoUCS2.Name: string;
begin
  Result := 'ISO-10646-UCS-2';
end;

class function TEncodingInfoUCS2.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUCS4 }

class function TEncodingInfoUCS4.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-10646-UCS-4';
    1: Result := 'csUCS4';
  else
    Error;
  end;
end;

class function TEncodingInfoUCS4.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUCS4.MIBenum: Integer;
begin
  Result := 1001;
end;

class function TEncodingInfoUCS4.Name: string;
begin
  Result := 'ISO-10646-UCS-4';
end;

class function TEncodingInfoUCS4.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicodeASCII }

class function TEncodingInfoUnicodeASCII.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-10646-UCS-Basic';
    1: Result := 'csUnicodeASCII';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicodeASCII.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicodeASCII.MIBenum: Integer;
begin
  Result := 1002;
end;

class function TEncodingInfoUnicodeASCII.Name: string;
begin
  Result := 'ISO-10646-UCS-Basic';
end;

class function TEncodingInfoUnicodeASCII.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicodeLatin1 }

class function TEncodingInfoUnicodeLatin1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-10646-Unicode-Latin1';
    1: Result := 'csUnicodeLatin1';
    2: Result := 'ISO-10646';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicodeLatin1.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoUnicodeLatin1.MIBenum: Integer;
begin
  Result := 1003;
end;

class function TEncodingInfoUnicodeLatin1.Name: string;
begin
  Result := 'ISO-10646-Unicode-Latin1';
end;

class function TEncodingInfoUnicodeLatin1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoISO10646J1 }

class function TEncodingInfoISO10646J1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-10646-J-1';
  else
    Error;
  end;
end;

class function TEncodingInfoISO10646J1.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoISO10646J1.MIBenum: Integer;
begin
  Result := 1004;
end;

class function TEncodingInfoISO10646J1.Name: string;
begin
  Result := 'ISO-10646-J-1';
end;

class function TEncodingInfoISO10646J1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicodeIBM1261 }

class function TEncodingInfoUnicodeIBM1261.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-Unicode-IBM-1261';
    1: Result := 'csUnicodeIBM1261';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicodeIBM1261.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicodeIBM1261.MIBenum: Integer;
begin
  Result := 1005;
end;

class function TEncodingInfoUnicodeIBM1261.Name: string;
begin
  Result := 'ISO-Unicode-IBM-1261';
end;

class function TEncodingInfoUnicodeIBM1261.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicodeIBM1268 }

class function TEncodingInfoUnicodeIBM1268.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-Unicode-IBM-1268';
    1: Result := 'csUnicodeIBM1268';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicodeIBM1268.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicodeIBM1268.MIBenum: Integer;
begin
  Result := 1006;
end;

class function TEncodingInfoUnicodeIBM1268.Name: string;
begin
  Result := 'ISO-Unicode-IBM-1268';
end;

class function TEncodingInfoUnicodeIBM1268.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicodeIBM1276 }

class function TEncodingInfoUnicodeIBM1276.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-Unicode-IBM-1276';
    1: Result := 'csUnicodeIBM1276';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicodeIBM1276.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicodeIBM1276.MIBenum: Integer;
begin
  Result := 1007;
end;

class function TEncodingInfoUnicodeIBM1276.Name: string;
begin
  Result := 'ISO-Unicode-IBM-1276';
end;

class function TEncodingInfoUnicodeIBM1276.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicodeIBM1264 }

class function TEncodingInfoUnicodeIBM1264.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-Unicode-IBM-1264';
    1: Result := 'csUnicodeIBM1264';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicodeIBM1264.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicodeIBM1264.MIBenum: Integer;
begin
  Result := 1008;
end;

class function TEncodingInfoUnicodeIBM1264.Name: string;
begin
  Result := 'ISO-Unicode-IBM-1264';
end;

class function TEncodingInfoUnicodeIBM1264.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicodeIBM1265 }

class function TEncodingInfoUnicodeIBM1265.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-Unicode-IBM-1265';
    1: Result := 'csUnicodeIBM1265';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicodeIBM1265.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicodeIBM1265.MIBenum: Integer;
begin
  Result := 1009; 
end;

class function TEncodingInfoUnicodeIBM1265.Name: string;
begin
  Result := 'ISO-Unicode-IBM-1265';
end;

class function TEncodingInfoUnicodeIBM1265.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnicode11 }

class function TEncodingInfoUnicode11.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UNICODE-1-1';
    1: Result := 'csUnicode11';
  else
    Error;
  end;
end;

class function TEncodingInfoUnicode11.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnicode11.MIBenum: Integer;
begin
  Result := 1010;
end;

class function TEncodingInfoUnicode11.Name: string;
begin
  Result := 'UNICODE-1-1';
end;

class function TEncodingInfoUnicode11.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoSCSU }

class function TEncodingInfoSCSU.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'SCSU';
  else
    Error;
  end;
end;

class function TEncodingInfoSCSU.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoSCSU.MIBenum: Integer;
begin
  Result := 1011;
end;

class function TEncodingInfoSCSU.Name: string;
begin
  Result := 'SCSU';
end;

class function TEncodingInfoSCSU.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF7 }

class function TEncodingInfoUTF7.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-7';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF7.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF7.MIBenum: Integer;
begin
  Result := 1012;
end;

class function TEncodingInfoUTF7.Name: string;
begin
  Result := 'UTF-7';
end;

class function TEncodingInfoUTF7.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF16BE }

class function TEncodingInfoUTF16BE.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-16BE';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF16BE.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF16BE.MIBenum: Integer;
begin
  Result := 1013;
end;

class function TEncodingInfoUTF16BE.Name: string;
begin
  Result := 'UTF-16BE';
end;

class function TEncodingInfoUTF16BE.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF16LE }

class function TEncodingInfoUTF16LE.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-16LE';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF16LE.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF16LE.MIBenum: Integer;
begin
  Result := 1014;
end;

class function TEncodingInfoUTF16LE.Name: string;
begin
  Result := 'UTF-16LE';
end;

class function TEncodingInfoUTF16LE.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF16 }

class function TEncodingInfoUTF16.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-16';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF16.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF16.MIBenum: Integer;
begin
  Result := 1015;
end;

class function TEncodingInfoUTF16.Name: string;
begin
  Result := 'UTF-16';
end;

class function TEncodingInfoUTF16.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoCESU8 }

class function TEncodingInfoCESU8.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'CESU-8';
    1: Result := 'csCESU-8';
  else
    Error;
  end;
end;

class function TEncodingInfoCESU8.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoCESU8.MIBenum: Integer;
begin
  Result := 1016;
end;

class function TEncodingInfoCESU8.Name: string;
begin
  Result := 'CESU-8';
end;

class function TEncodingInfoCESU8.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF32 }

class function TEncodingInfoUTF32.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-32';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF32.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF32.MIBenum: Integer;
begin
  Result := 1017;
end;

class function TEncodingInfoUTF32.Name: string;
begin
  Result := 'UTF-32';
end;

class function TEncodingInfoUTF32.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF32BE }

class function TEncodingInfoUTF32BE.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-32BE';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF32BE.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF32BE.MIBenum: Integer;
begin
  Result := 1018;
end;

class function TEncodingInfoUTF32BE.Name: string;
begin
  Result := 'UTF-32BE';
end;

class function TEncodingInfoUTF32BE.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUTF32LE }

class function TEncodingInfoUTF32LE.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UTF-32LE';
  else
    Error;
  end;
end;

class function TEncodingInfoUTF32LE.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoUTF32LE.MIBenum: Integer;
begin
  Result := 1019;
end;

class function TEncodingInfoUTF32LE.Name: string;
begin
  Result := 'UTF-32LE';
end;

class function TEncodingInfoUTF32LE.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoBOCU1 }

class function TEncodingInfoBOCU1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'BOCU-1';
    1: Result := 'csBOCU-1';
  else
    Error;
  end;
end;

class function TEncodingInfoBOCU1.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoBOCU1.MIBenum: Integer;
begin
  Result := 1020;
end;

class function TEncodingInfoBOCU1.Name: string;
begin
  Result := 'BOCU-1';
end;

class function TEncodingInfoBOCU1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows30Latin1 }

class function TEncodingInfoWindows30Latin1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-1-Windows-3.0-Latin-1';
    1: Result := 'csWindows30Latin1';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows30Latin1.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoWindows30Latin1.MIBenum: Integer;
begin
  Result := 2000;
end;

class function TEncodingInfoWindows30Latin1.Name: string;
begin
  Result := 'ISO-8859-1-Windows-3.0-Latin-1';
end;

class function TEncodingInfoWindows30Latin1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows31Latin1 }

class function TEncodingInfoWindows31Latin1.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-1-Windows-3.1-Latin-1';
    1: Result := 'csWindows31Latin1';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows31Latin1.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoWindows31Latin1.MIBenum: Integer;
begin
  Result := 2001;
end;

class function TEncodingInfoWindows31Latin1.Name: string;
begin
  Result := 'ISO-8859-1-Windows-3.1-Latin-1';
end;

class function TEncodingInfoWindows31Latin1.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows31Latin2 }

class function TEncodingInfoWindows31Latin2.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-2-Windows-Latin-2';
    1: Result := 'csWindows31Latin2';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows31Latin2.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoWindows31Latin2.MIBenum: Integer;
begin
  Result := 2002;
end;

class function TEncodingInfoWindows31Latin2.Name: string;
begin
  Result := 'ISO-8859-2-Windows-Latin-2';
end;

class function TEncodingInfoWindows31Latin2.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows31Latin5 }

class function TEncodingInfoWindows31Latin5.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'ISO-8859-9-Windows-Latin-5';
    1: Result := 'csWindows31Latin5';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows31Latin5.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoWindows31Latin5.MIBenum: Integer;
begin
  Result := 2003;
end;

class function TEncodingInfoWindows31Latin5.Name: string;
begin
  Result := 'ISO-8859-9-Windows-Latin-5';
end;

class function TEncodingInfoWindows31Latin5.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHPRoman8 }

class function TEncodingInfoHPRoman8.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'hp-roman8';
    1: Result := 'roman8';
    2: Result := 'r8';
    3: Result := 'csHPRoman8';
  else
    Error;
  end;
end;

class function TEncodingInfoHPRoman8.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoHPRoman8.MIBenum: Integer;
begin
  Result := 2004;
end;

class function TEncodingInfoHPRoman8.Name: string;
begin
  Result := 'hp-roman8';
end;

class function TEncodingInfoHPRoman8.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoAdobeStandardEncoding }

class function TEncodingInfoAdobeStandardEncoding.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Adobe-Standard-Encoding';
    1: Result := 'csAdobeStandardEncoding';
  else
    Error;
  end;
end;

class function TEncodingInfoAdobeStandardEncoding.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoAdobeStandardEncoding.MIBenum: Integer;
begin
  Result := 2005;
end;

class function TEncodingInfoAdobeStandardEncoding.Name: string;
begin
  Result := 'Adobe-Standard-Encoding';
end;

class function TEncodingInfoAdobeStandardEncoding.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoVenturaUS }

class function TEncodingInfoVenturaUS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Ventura-US';
    1: Result := 'csVenturaUS';
  else
    Error;
  end;
end;

class function TEncodingInfoVenturaUS.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoVenturaUS.MIBenum: Integer;
begin
  Result := 2006;
end;

class function TEncodingInfoVenturaUS.Name: string;
begin
  Result := 'Ventura-US';
end;

class function TEncodingInfoVenturaUS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoVenturaInternational }

class function TEncodingInfoVenturaInternational.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Ventura-International';
    1: Result := 'csVenturaInternational';
  else
    Error;
  end;
end;

class function TEncodingInfoVenturaInternational.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoVenturaInternational.MIBenum: Integer;
begin
  Result := 2007;
end;

class function TEncodingInfoVenturaInternational.Name: string;
begin
  Result := 'Ventura-International';
end;

class function TEncodingInfoVenturaInternational.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoDECMCS }

class function TEncodingInfoDECMCS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'DEC-MCS';
    1: Result := 'dec';
    2: Result := 'csDECMCS';
  else
    Error;
  end;
end;

class function TEncodingInfoDECMCS.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoDECMCS.MIBenum: Integer;
begin
  Result := 2008;
end;

class function TEncodingInfoDECMCS.Name: string;
begin
  Result := 'DEC-MCS';
end;

class function TEncodingInfoDECMCS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPC850Multilingual }

class function TEncodingInfoPC850Multilingual.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM850';
    1: Result := 'cp850';
    2: Result := '850';
    3: Result := 'csPC850Multilingual';
  else
    Error;
  end;
end;

class function TEncodingInfoPC850Multilingual.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoPC850Multilingual.MIBenum: Integer;
begin
  Result := 2009;
end;

class function TEncodingInfoPC850Multilingual.Name: string;
begin
  Result := 'IBM850';
end;

class function TEncodingInfoPC850Multilingual.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPCp852 }

class function TEncodingInfoPCp852.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM852';
    1: Result := 'cp852';
    2: Result := '852';
    3: Result := 'csPCp852';
  else
    Error;
  end;
end;

class function TEncodingInfoPCp852.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoPCp852.MIBenum: Integer;
begin
  Result := 2010;
end;

class function TEncodingInfoPCp852.Name: string;
begin
  Result := 'IBM852';
end;

class function TEncodingInfoPCp852.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPC8CodePage437 }

class function TEncodingInfoPC8CodePage437.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM437';
    1: Result := 'cp437';
    2: Result := '437';
    3: Result := 'csPC8CodePage437';
    4: Result := 'DOSLatinUS';        // Non-standard alias
  else
    Error;
  end;
end;

class function TEncodingInfoPC8CodePage437.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoPC8CodePage437.MIBenum: Integer;
begin
  Result := 2011;
end;

class function TEncodingInfoPC8CodePage437.Name: string;
begin
  Result := 'IBM437';
end;

class function TEncodingInfoPC8CodePage437.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPC8DanishNorwegian }

class function TEncodingInfoPC8DanishNorwegian.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'PC8-Danish-Norwegian';
    1: Result := 'csPC8DanishNorwegian';
  else
    Error;
  end;
end;

class function TEncodingInfoPC8DanishNorwegian.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoPC8DanishNorwegian.MIBenum: Integer;
begin
  Result := 2012;
end;

class function TEncodingInfoPC8DanishNorwegian.Name: string;
begin
  Result := 'PC8-Danish-Norwegian';
end;

class function TEncodingInfoPC8DanishNorwegian.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPC862LatinHebrew }

class function TEncodingInfoPC862LatinHebrew.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM862';
    1: Result := 'cp862';
    2: Result := '862';
    3: Result := 'csPC862LatinHebrew';
  else
    Error;
  end;
end;

class function TEncodingInfoPC862LatinHebrew.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoPC862LatinHebrew.MIBenum: Integer;
begin
  Result := 2013;
end;

class function TEncodingInfoPC862LatinHebrew.Name: string;
begin
  Result := 'IBM862';
end;

class function TEncodingInfoPC862LatinHebrew.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPC8Turkish }

class function TEncodingInfoPC8Turkish.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'PC8-Turkish';
    1: Result := 'csPC8Turkish';
  else
    Error;
  end;
end;

class function TEncodingInfoPC8Turkish.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoPC8Turkish.MIBenum: Integer;
begin
  Result := 2014;
end;

class function TEncodingInfoPC8Turkish.Name: string;
begin
  Result := 'PC8-Turkish';
end;

class function TEncodingInfoPC8Turkish.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBMSymbols }

class function TEncodingInfoIBMSymbols.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM-Symbols';
    1: Result := 'csIBMSymbols';
  else
    Error;
  end;
end;

class function TEncodingInfoIBMSymbols.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoIBMSymbols.MIBenum: Integer;
begin
  Result := 2015;
end;

class function TEncodingInfoIBMSymbols.Name: string;
begin
  Result := 'IBM-Symbols';
end;

class function TEncodingInfoIBMSymbols.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBMThai }

class function TEncodingInfoIBMThai.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM-Thai';
    1: Result := 'csIBMThai';
  else
    Error;
  end;
end;

class function TEncodingInfoIBMThai.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoIBMThai.MIBenum: Integer;
begin
  Result := 2016;
end;

class function TEncodingInfoIBMThai.Name: string;
begin
  Result := 'IBM-Thai';
end;

class function TEncodingInfoIBMThai.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHPLegal }

class function TEncodingInfoHPLegal.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'HP-Legal';
    1: Result := 'csHPLegal';
  else
    Error;
  end;
end;

class function TEncodingInfoHPLegal.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoHPLegal.MIBenum: Integer;
begin
  Result := 2017;
end;

class function TEncodingInfoHPLegal.Name: string;
begin
  Result := 'HP-Legal';
end;

class function TEncodingInfoHPLegal.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHPPiFont }

class function TEncodingInfoHPPiFont.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'HP-Pi-font';
    1: Result := 'csHPPiFont';
  else
    Error;
  end;
end;

class function TEncodingInfoHPPiFont.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoHPPiFont.MIBenum: Integer;
begin
  Result := 2018;
end;

class function TEncodingInfoHPPiFont.Name: string;
begin
  Result := 'HP-Pi-font';
end;

class function TEncodingInfoHPPiFont.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHPMath8 }

class function TEncodingInfoHPMath8.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'HP-Math8';
    1: Result := 'csHPMath8';
  else
    Error;
  end;
end;

class function TEncodingInfoHPMath8.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoHPMath8.MIBenum: Integer;
begin
  Result := 2019;
end;

class function TEncodingInfoHPMath8.Name: string;
begin
  Result := 'HP-Math8';
end;

class function TEncodingInfoHPMath8.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHPPSMath }

class function TEncodingInfoHPPSMath.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Adobe-Symbol-Encoding';
    1: Result := 'csHPPSMath';
  else
    Error;
  end;
end;

class function TEncodingInfoHPPSMath.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoHPPSMath.MIBenum: Integer;
begin
  Result := 2020;
end;

class function TEncodingInfoHPPSMath.Name: string;
begin
  Result := 'Adobe-Symbol-Encoding';
end;

class function TEncodingInfoHPPSMath.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHPDesktop }

class function TEncodingInfoHPDesktop.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'HP-DeskTop';
    1: Result := 'csHPDesktop';
  else
    Error;
  end;
end;

class function TEncodingInfoHPDesktop.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoHPDesktop.MIBenum: Integer;
begin
  Result := 2021;
end;

class function TEncodingInfoHPDesktop.Name: string;
begin
  Result := 'HP-DeskTop';
end;

class function TEncodingInfoHPDesktop.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoVenturaMath }

class function TEncodingInfoVenturaMath.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Ventura-Math';
    1: Result := 'csVenturaMath';
  else
    Error;
  end;
end;

class function TEncodingInfoVenturaMath.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoVenturaMath.MIBenum: Integer;
begin
  Result := 2022;
end;

class function TEncodingInfoVenturaMath.Name: string;
begin
  Result := 'Ventura-Math';
end;

class function TEncodingInfoVenturaMath.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoMicrosoftPublishing }

class function TEncodingInfoMicrosoftPublishing.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Microsoft-Publishing';
    1: Result := 'csMicrosoftPublishing';
  else
    Error;
  end;
end;

class function TEncodingInfoMicrosoftPublishing.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoMicrosoftPublishing.MIBenum: Integer;
begin
  Result := 2023;
end;

class function TEncodingInfoMicrosoftPublishing.Name: string;
begin
  Result := 'Microsoft-Publishing';
end;

class function TEncodingInfoMicrosoftPublishing.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows31J }

class function TEncodingInfoWindows31J.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Windows-31J';
    1: Result := 'csWindows31J';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows31J.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoWindows31J.MIBenum: Integer;
begin
  Result := 2024;
end;

class function TEncodingInfoWindows31J.Name: string;
begin
  Result := 'Windows-31J';
end;

class function TEncodingInfoWindows31J.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoGB2312 }

class function TEncodingInfoGB2312.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'GB2312';
    1: Result := 'csGB2312';
  else
    Error;
  end;
end;

class function TEncodingInfoGB2312.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoGB2312.MIBenum: Integer;
begin
  Result := 2025;
end;

class function TEncodingInfoGB2312.Name: string;
begin
  Result := 'GB2312';
end;

class function TEncodingInfoGB2312.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoBig5 }

class function TEncodingInfoBig5.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Big5';
    1: Result := 'csBig5';
  else
    Error;
  end;
end;

class function TEncodingInfoBig5.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoBig5.MIBenum: Integer;
begin
  Result := 2026;
end;

class function TEncodingInfoBig5.Name: string;
begin
  Result := 'Big5';
end;

class function TEncodingInfoBig5.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoMacintosh }

class function TEncodingInfoMacintosh.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'macintosh';
    1: Result := 'mac';
    2: Result := 'csMacintosh';
  else
    Error;
  end;
end;

class function TEncodingInfoMacintosh.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoMacintosh.MIBenum: Integer;
begin
  Result := 2027;
end;

class function TEncodingInfoMacintosh.Name: string;
begin
  Result := 'macintosh';
end;

class function TEncodingInfoMacintosh.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM037 }

class function TEncodingInfoIBM037.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM037';
    1: Result := 'cp037';
    2: Result := 'ebcdic-cp-us';
    3: Result := 'ebcdic-cp-ca';
    4: Result := 'ebcdic-cp-wt';
    5: Result := 'ebcdic-cp-nl';
    6: Result := 'csIBM037';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM037.AliasCount: Integer;
begin
  Result := 7;
end;

class function TEncodingInfoIBM037.MIBenum: Integer;
begin
  Result := 2028;
end;

class function TEncodingInfoIBM037.Name: string;
begin
  Result := 'IBM037';
end;

class function TEncodingInfoIBM037.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM038 }

class function TEncodingInfoIBM038.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM038';
    1: Result := 'EBCDIC-INT';
    2: Result := 'cp038';
    3: Result := 'csIBM038';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM038.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM038.MIBenum: Integer;
begin
  Result := 2029;
end;

class function TEncodingInfoIBM038.Name: string;
begin
  Result := 'IBM038';
end;

class function TEncodingInfoIBM038.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM273 }

class function TEncodingInfoIBM273.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM273';
    1: Result := 'CP273';
    2: Result := 'csIBM273';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM273.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoIBM273.MIBenum: Integer;
begin
  Result := 2030;
end;

class function TEncodingInfoIBM273.Name: string;
begin
  Result := 'IBM273';
end;

class function TEncodingInfoIBM273.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM274 }

class function TEncodingInfoIBM274.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM274';
    1: Result := 'EBCDIC-BE';
    2: Result := 'CP274';
    3: Result := 'csIBM274';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM274.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM274.MIBenum: Integer;
begin
  Result := 2031;
end;

class function TEncodingInfoIBM274.Name: string;
begin
  Result := 'IBM274';
end;

class function TEncodingInfoIBM274.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM275 }

class function TEncodingInfoIBM275.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM275';
    1: Result := 'EBCDIC-BR';
    2: Result := 'cp275';
    3: Result := 'csIBM275';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM275.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM275.MIBenum: Integer;
begin
  Result := 2032;
end;

class function TEncodingInfoIBM275.Name: string;
begin
  Result := 'IBM275';
end;

class function TEncodingInfoIBM275.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM277 }

class function TEncodingInfoIBM277.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM277';
    1: Result := 'EBCDIC-CP-DK';
    2: Result := 'EBCDIC-CP-NO';
    3: Result := 'csIBM277';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM277.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM277.MIBenum: Integer;
begin
  Result := 2033;
end;

class function TEncodingInfoIBM277.Name: string;
begin
  Result := 'IBM277';
end;

class function TEncodingInfoIBM277.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM278 }

class function TEncodingInfoIBM278.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM278';
    1: Result := 'CP278';
    2: Result := 'ebcdic-cp-fi';
    3: Result := 'ebcdic-cp-se';
    4: Result := 'csIBM278';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM278.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIBM278.MIBenum: Integer;
begin
  Result := 2034;
end;

class function TEncodingInfoIBM278.Name: string;
begin
  Result := 'IBM278';
end;

class function TEncodingInfoIBM278.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM280 }

class function TEncodingInfoIBM280.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM280';
    1: Result := 'CP280';
    2: Result := 'ebcdic-cp-it';
    3: Result := 'csIBM280';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM280.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM280.MIBenum: Integer;
begin
  Result := 2035;
end;

class function TEncodingInfoIBM280.Name: string;
begin
  Result := 'IBM280';
end;

class function TEncodingInfoIBM280.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM281 }

class function TEncodingInfoIBM281.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM281';
    1: Result := 'EBCDIC-JP-E';
    2: Result := 'cp281';
    3: Result := 'csIBM281';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM281.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM281.MIBenum: Integer;
begin
  Result := 2036;
end;

class function TEncodingInfoIBM281.Name: string;
begin
  Result := 'IBM281';
end;

class function TEncodingInfoIBM281.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM284 }

class function TEncodingInfoIBM284.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM284';
    1: Result := 'CP284';
    2: Result := 'ebcdic-cp-es';
    3: Result := 'csIBM284';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM284.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM284.MIBenum: Integer;
begin
  Result := 2037;
end;

class function TEncodingInfoIBM284.Name: string;
begin
  Result := 'IBM284';
end;

class function TEncodingInfoIBM284.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM285 }

class function TEncodingInfoIBM285.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM285';
    1: Result := 'CP285';
    2: Result := 'ebcdic-cp-gb';
    3: Result := 'csIBM285';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM285.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM285.MIBenum: Integer;
begin
  Result := 2038;
end;

class function TEncodingInfoIBM285.Name: string;
begin
  Result := 'IBM285';
end;

class function TEncodingInfoIBM285.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM290 }

class function TEncodingInfoIBM290.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM290';
    1: Result := 'cp290';
    2: Result := 'EBCDIC-JP-kana';
    3: Result := 'csIBM290';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM290.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM290.MIBenum: Integer;
begin
  Result := 2039;
end;

class function TEncodingInfoIBM290.Name: string;
begin
  Result := 'IBM290';
end;

class function TEncodingInfoIBM290.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM297 }

class function TEncodingInfoIBM297.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM297';
    1: Result := 'cp297';
    2: Result := 'ebcdic-cp-fr';
    3: Result := 'csIBM297';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM297.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM297.MIBenum: Integer;
begin
  Result := 2040;
end;

class function TEncodingInfoIBM297.Name: string;
begin
  Result := 'IBM297';
end;

class function TEncodingInfoIBM297.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM420 }

class function TEncodingInfoIBM420.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM420';
    1: Result := 'cp420';
    2: Result := 'ebcdic-cp-ar1';
    3: Result := 'csIBM420';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM420.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM420.MIBenum: Integer;
begin
  Result := 2041;
end;

class function TEncodingInfoIBM420.Name: string;
begin
  Result := 'IBM420';
end;

class function TEncodingInfoIBM420.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM423 }

class function TEncodingInfoIBM423.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM423';
    1: Result := 'cp423';
    2: Result := 'ebcdic-cp-gr';
    3: Result := 'csIBM423';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM423.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM423.MIBenum: Integer;
begin
  Result := 2042;
end;

class function TEncodingInfoIBM423.Name: string;
begin
  Result := 'IBM423';
end;

class function TEncodingInfoIBM423.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM424 }

class function TEncodingInfoIBM424.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM424';
    1: Result := 'cp424';
    2: Result := 'ebcdic-cp-he';
    3: Result := 'csIBM424';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM424.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM424.MIBenum: Integer;
begin
  Result := 2043;
end;

class function TEncodingInfoIBM424.Name: string;
begin
  Result := 'IBM424';
end;

class function TEncodingInfoIBM424.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM500 }

class function TEncodingInfoIBM500.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM500';
    1: Result := 'CP500';
    2: Result := 'ebcdic-cp-be';
    3: Result := 'ebcdic-cp-ch';
    4: Result := 'csIBM500';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM500.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIBM500.MIBenum: Integer;
begin
  Result := 2044;
end;

class function TEncodingInfoIBM500.Name: string;
begin
  Result := 'IBM500';
end;

class function TEncodingInfoIBM500.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM851 }

class function TEncodingInfoIBM851.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM851';
    1: Result := 'cp851';
    2: Result := '851';
    3: Result := 'csIBM851';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM851.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM851.MIBenum: Integer;
begin
  Result := 2045;
end;

class function TEncodingInfoIBM851.Name: string;
begin
  Result := 'IBM851';
end;

class function TEncodingInfoIBM851.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM855 }

class function TEncodingInfoIBM855.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM855';
    1: Result := 'cp855';
    2: Result := '855';
    3: Result := 'csIBM855';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM855.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM855.MIBenum: Integer;
begin
  Result := 2046;
end;

class function TEncodingInfoIBM855.Name: string;
begin
  Result := 'IBM855';
end;

class function TEncodingInfoIBM855.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM857 }

class function TEncodingInfoIBM857.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM857';
    1: Result := 'cp857';
    2: Result := '857';
    3: Result := 'csIBM857';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM857.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM857.MIBenum: Integer;
begin
  Result := 2047;
end;

class function TEncodingInfoIBM857.Name: string;
begin
  Result := 'IBM857';
end;

class function TEncodingInfoIBM857.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM860 }

class function TEncodingInfoIBM860.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM860';
    1: Result := 'cp860';
    2: Result := '860';
    3: Result := 'csIBM860';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM860.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM860.MIBenum: Integer;
begin
  Result := 2048;
end;

class function TEncodingInfoIBM860.Name: string;
begin
  Result := 'IBM860';
end;

class function TEncodingInfoIBM860.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM861 }

class function TEncodingInfoIBM861.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM861';
    1: Result := 'cp861';
    2: Result := '861';
    3: Result := 'cp-is';
    4: Result := 'csIBM861';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM861.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIBM861.MIBenum: Integer;
begin
  Result := 2049;
end;

class function TEncodingInfoIBM861.Name: string;
begin
  Result := 'IBM861';
end;

class function TEncodingInfoIBM861.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM863 }

class function TEncodingInfoIBM863.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM863';
    1: Result := 'cp863';
    2: Result := '863';
    3: Result := 'csIBM863';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM863.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM863.MIBenum: Integer;
begin
  Result := 2050;
end;

class function TEncodingInfoIBM863.Name: string;
begin
  Result := 'IBM863';
end;

class function TEncodingInfoIBM863.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM864 }

class function TEncodingInfoIBM864.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM864';
    1: Result := 'cp864';
    2: Result := 'csIBM864';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM864.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoIBM864.MIBenum: Integer;
begin
  Result := 2051;
end;

class function TEncodingInfoIBM864.Name: string;
begin
  Result := 'IBM864';
end;

class function TEncodingInfoIBM864.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM865 }

class function TEncodingInfoIBM865.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM865';
    1: Result := 'cp865';
    2: Result := '865';
    3: Result := 'csIBM865';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM865.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM865.MIBenum: Integer;
begin
  Result := 2052;
end;

class function TEncodingInfoIBM865.Name: string;
begin
  Result := 'IBM865';
end;

class function TEncodingInfoIBM865.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM868 }

class function TEncodingInfoIBM868.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM868';
    1: Result := 'CP868';
    2: Result := 'cp-ar';
    3: Result := 'csIBM868';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM868.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM868.MIBenum: Integer;
begin
  Result := 2053;
end;

class function TEncodingInfoIBM868.Name: string;
begin
  Result := 'IBM868';
end;

class function TEncodingInfoIBM868.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM869 }

class function TEncodingInfoIBM869.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM869';
    1: Result := 'cp869';
    2: Result := '869';
    3: Result := 'cp-gr';
    4: Result := 'csIBM869';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM869.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIBM869.MIBenum: Integer;
begin
  Result := 2054;
end;

class function TEncodingInfoIBM869.Name: string;
begin
  Result := 'IBM869';
end;

class function TEncodingInfoIBM869.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM870 }

class function TEncodingInfoIBM870.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM870';
    1: Result := 'CP870';
    2: Result := 'ebcdic-cp-roece';
    3: Result := 'ebcdic-cp-yu';
    4: Result := 'csIBM870';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM870.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIBM870.MIBenum: Integer;
begin
  Result := 2055;
end;

class function TEncodingInfoIBM870.Name: string;
begin
  Result := 'IBM870';
end;

class function TEncodingInfoIBM870.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM871 }

class function TEncodingInfoIBM871.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM871';
    1: Result := 'CP871';
    2: Result := 'ebcdic-cp-is';
    3: Result := 'csIBM871';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM871.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM871.MIBenum: Integer;
begin
  Result := 2056;
end;

class function TEncodingInfoIBM871.Name: string;
begin
  Result := 'IBM871';
end;

class function TEncodingInfoIBM871.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM880 }

class function TEncodingInfoIBM880.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM880';
    1: Result := 'cp880';
    2: Result := 'EBCDIC-Cyrillic';
    3: Result := 'csIBM880';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM880.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM880.MIBenum: Integer;
begin
  Result := 2057;
end;

class function TEncodingInfoIBM880.Name: string;
begin
  Result := 'IBM880';
end;

class function TEncodingInfoIBM880.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM891 }

class function TEncodingInfoIBM891.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM891';
    1: Result := 'cp891';
    2: Result := 'csIBM891';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM891.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoIBM891.MIBenum: Integer;
begin
  Result := 2058;
end;

class function TEncodingInfoIBM891.Name: string;
begin
  Result := 'IBM891';
end;

class function TEncodingInfoIBM891.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM903 }

class function TEncodingInfoIBM903.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM903';
    1: Result := 'cp903';
    2: Result := 'csIBM903';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM903.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoIBM903.MIBenum: Integer;
begin
  Result := 2059;
end;

class function TEncodingInfoIBM903.Name: string;
begin
  Result := 'IBM903';
end;

class function TEncodingInfoIBM903.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM904 }

class function TEncodingInfoIBM904.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM904';
    1: Result := 'cp904';
    2: Result := '904';
    3: Result := 'csIBM904';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM904.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM904.MIBenum: Integer;
begin
  Result := 2060;
end;

class function TEncodingInfoIBM904.Name: string;
begin
  Result := 'IBM904';
end;

class function TEncodingInfoIBM904.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM905 }

class function TEncodingInfoIBM905.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM905';
    1: Result := 'CP905';
    2: Result := 'ebcdic-cp-tr';
    3: Result := 'csIBM905';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM905.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM905.MIBenum: Integer;
begin
  Result := 2061;
end;

class function TEncodingInfoIBM905.Name: string;
begin
  Result := 'IBM905';
end;

class function TEncodingInfoIBM905.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM918 }

class function TEncodingInfoIBM918.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM918';
    1: Result := 'CP918';
    2: Result := 'ebcdic-cp-ar2';
    3: Result := 'csIBM918';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM918.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM918.MIBenum: Integer;
begin
  Result := 2062;
end;

class function TEncodingInfoIBM918.Name: string;
begin
  Result := 'IBM918';
end;

class function TEncodingInfoIBM918.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM1026 }

class function TEncodingInfoIBM1026.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM1026';
    1: Result := 'CP1026';
    2: Result := 'csIBM1026';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM1026.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoIBM1026.MIBenum: Integer;
begin
  Result := 2063;
end;

class function TEncodingInfoIBM1026.Name: string;
begin
  Result := 'IBM1026';
end;

class function TEncodingInfoIBM1026.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBMEBCDICATDE }

class function TEncodingInfoIBMEBCDICATDE.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-AT-DE';
    1: Result := 'csIBMEBCDICATDE';
  else
    Error;
  end;
end;

class function TEncodingInfoIBMEBCDICATDE.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoIBMEBCDICATDE.MIBenum: Integer;
begin
  Result := 2064;
end;

class function TEncodingInfoIBMEBCDICATDE.Name: string;
begin
  Result := 'EBCDIC-AT-DE';
end;

class function TEncodingInfoIBMEBCDICATDE.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICATDE }

class function TEncodingInfoEBCDICATDEA.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-AT-DE-A';
    1: Result := 'csEBCDICATDEA';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICATDEA.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICATDEA.MIBenum: Integer;
begin
  Result := 2065;
end;

class function TEncodingInfoEBCDICATDEA.Name: string;
begin
  Result := 'EBCDIC-AT-DE-A';
end;

class function TEncodingInfoEBCDICATDEA.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICCAFR }

class function TEncodingInfoEBCDICCAFR.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-CA-FR';
    1: Result := 'csEBCDICCAFR';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICCAFR.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICCAFR.MIBenum: Integer;
begin
  Result := 2066;
end;

class function TEncodingInfoEBCDICCAFR.Name: string;
begin
  Result := 'EBCDIC-CA-FR';
end;

class function TEncodingInfoEBCDICCAFR.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICDKNO }

class function TEncodingInfoEBCDICDKNO.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-DK-NO';
    1: Result := 'csEBCDICDKNO';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICDKNO.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICDKNO.MIBenum: Integer;
begin
  Result := 2067;
end;

class function TEncodingInfoEBCDICDKNO.Name: string;
begin
  Result := 'EBCDIC-DK-NO';
end;

class function TEncodingInfoEBCDICDKNO.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICDKNOA }

class function TEncodingInfoEBCDICDKNOA.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-DK-NO-A';
    1: Result := 'csEBCDICDKNOA';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICDKNOA.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICDKNOA.MIBenum: Integer;
begin
  Result := 2068;
end;

class function TEncodingInfoEBCDICDKNOA.Name: string;
begin
  Result := 'EBCDIC-DK-NO-A';
end;

class function TEncodingInfoEBCDICDKNOA.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICFISE }

class function TEncodingInfoEBCDICFISE.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-FI-SE';
    1: Result := 'csEBCDICFISE';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICFISE.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICFISE.MIBenum: Integer;
begin
  Result := 2069;
end;

class function TEncodingInfoEBCDICFISE.Name: string;
begin
  Result := 'EBCDIC-FI-SE';
end;

class function TEncodingInfoEBCDICFISE.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICFISEA }

class function TEncodingInfoEBCDICFISEA.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-FI-SE-A';
    1: Result := 'csEBCDICFISEA';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICFISEA.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICFISEA.MIBenum: Integer;
begin
  Result := 2070;
end;

class function TEncodingInfoEBCDICFISEA.Name: string;
begin
  Result := 'EBCDIC-FI-SE-A';
end;

class function TEncodingInfoEBCDICFISEA.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICFR }

class function TEncodingInfoEBCDICFR.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-FR';
    1: Result := 'csEBCDICFR';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICFR.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICFR.MIBenum: Integer;
begin
  Result := 2071;
end;

class function TEncodingInfoEBCDICFR.Name: string;
begin
  Result := 'EBCDIC-FR';
end;

class function TEncodingInfoEBCDICFR.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICIT }

class function TEncodingInfoEBCDICIT.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-IT';
    1: Result := 'csEBCDICIT';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICIT.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICIT.MIBenum: Integer;
begin
  Result := 2072;
end;

class function TEncodingInfoEBCDICIT.Name: string;
begin
  Result := 'EBCDIC-IT';
end;

class function TEncodingInfoEBCDICIT.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICPT }

class function TEncodingInfoEBCDICPT.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-PT';
    1: Result := 'csEBCDICPT';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICPT.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICPT.MIBenum: Integer;
begin
  Result := 2073;
end;

class function TEncodingInfoEBCDICPT.Name: string;
begin
  Result := 'EBCDIC-PT';
end;

class function TEncodingInfoEBCDICPT.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICES }

class function TEncodingInfoEBCDICES.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-ES';
    1: Result := 'csEBCDICES';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICES.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICES.MIBenum: Integer;
begin
  Result := 2074;
end;

class function TEncodingInfoEBCDICES.Name: string;
begin
  Result := 'EBCDIC-ES';
end;

class function TEncodingInfoEBCDICES.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICESA }

class function TEncodingInfoEBCDICESA.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-ES-A';
    1: Result := 'csEBCDICESA';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICESA.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICESA.MIBenum: Integer;
begin
  Result := 2075;
end;

class function TEncodingInfoEBCDICESA.Name: string;
begin
  Result := 'EBCDIC-ES-A';
end;

class function TEncodingInfoEBCDICESA.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICESS }

class function TEncodingInfoEBCDICESS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-ES-S';
    1: Result := 'csEBCDICESS';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICESS.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICESS.MIBenum: Integer;
begin
  Result := 2076;
end;

class function TEncodingInfoEBCDICESS.Name: string;
begin
  Result := 'EBCDIC-ES-S';
end;

class function TEncodingInfoEBCDICESS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICUK }

class function TEncodingInfoEBCDICUK.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-UK';
    1: Result := 'csEBCDICUK';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICUK.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICUK.MIBenum: Integer;
begin
  Result := 2077;
end;

class function TEncodingInfoEBCDICUK.Name: string;
begin
  Result := 'EBCDIC-UK';
end;

class function TEncodingInfoEBCDICUK.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoEBCDICUS }

class function TEncodingInfoEBCDICUS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'EBCDIC-US';
    1: Result := 'csEBCDICUS';
  else
    Error;
  end;
end;

class function TEncodingInfoEBCDICUS.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoEBCDICUS.MIBenum: Integer;
begin
  Result := 2078;
end;

class function TEncodingInfoEBCDICUS.Name: string;
begin
  Result := 'EBCDIC-US';
end;

class function TEncodingInfoEBCDICUS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoUnknown8Bit }

class function TEncodingInfoUnknown8Bit.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'UNKNOWN-8BIT';
    1: Result := 'csUnkown8Bit';
  else
    Error;
  end;
end;

class function TEncodingInfoUnknown8Bit.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoUnknown8Bit.MIBenum: Integer;
begin
  Result := 2079;
end;

class function TEncodingInfoUnknown8Bit.Name: string;
begin
  Result := '2';
end;

class function TEncodingInfoUnknown8Bit.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoMnemonic }

class function TEncodingInfoMnemonic.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'MNEMONIC';
    1: Result := 'csMnemonic';
  else
    Error;
  end;
end;

class function TEncodingInfoMnemonic.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoMnemonic.MIBenum: Integer;
begin
  Result := 2080;
end;

class function TEncodingInfoMnemonic.Name: string;
begin
  Result := 'MNEMONIC';
end;

class function TEncodingInfoMnemonic.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoMnem }

class function TEncodingInfoMnem.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'MNEM';
    1: Result := 'csMnem';
  else
    Error;
  end;
end;

class function TEncodingInfoMnem.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoMnem.MIBenum: Integer;
begin
  Result := 2081;
end;

class function TEncodingInfoMnem.Name: string;
begin
  Result := 'MNEM';
end;

class function TEncodingInfoMnem.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoVISCII }

class function TEncodingInfoVISCII.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'VISCII';
    1: Result := 'csVISCII';
  else
    Error;
  end;
end;

class function TEncodingInfoVISCII.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoVISCII.MIBenum: Integer;
begin
  Result := 2082;
end;

class function TEncodingInfoVISCII.Name: string;
begin
  Result := 'VISCII';
end;

class function TEncodingInfoVISCII.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoVIQR }

class function TEncodingInfoVIQR.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'VIQR';
    1: Result := 'csVIQR';
  else
    Error;
  end;
end;

class function TEncodingInfoVIQR.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoVIQR.MIBenum: Integer;
begin
  Result := 2083;
end;

class function TEncodingInfoVIQR.Name: string;
begin
  Result := 'VIQR';
end;

class function TEncodingInfoVIQR.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoKOI8R }

class function TEncodingInfoKOI8R.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'KOI8-R';
    1: Result := 'csKOI8R';
  else
    Error;
  end;
end;

class function TEncodingInfoKOI8R.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoKOI8R.MIBenum: Integer;
begin
  Result := 2084;
end;

class function TEncodingInfoKOI8R.Name: string;
begin
  Result := 'KOI8-R';
end;

class function TEncodingInfoKOI8R.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoHZGB2312 }

class function TEncodingInfoHZGB2312.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'HZ-GB-2312';
  else
    Error;
  end;
end;

class function TEncodingInfoHZGB2312.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoHZGB2312.MIBenum: Integer;
begin
  Result := 2085;
end;

class function TEncodingInfoHZGB2312.Name: string;
begin
  Result := 'HZ-GB-2312';
end;

class function TEncodingInfoHZGB2312.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM866 }

class function TEncodingInfoIBM866.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM866';
    1: Result := 'cp866';
    2: Result := '866';
    3: Result := 'csIBM866';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM866.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM866.MIBenum: Integer;
begin
  Result := 2086;
end;

class function TEncodingInfoIBM866.Name: string;
begin
  Result := 'IBM866';
end;

class function TEncodingInfoIBM866.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPC775Baltic }

class function TEncodingInfoPC775Baltic.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM775';
    1: Result := 'cp775';
    2: Result := 'csPC775Baltic';
  else
    Error;
  end;
end;

class function TEncodingInfoPC775Baltic.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoPC775Baltic.MIBenum: Integer;
begin
  Result := 2087;
end;

class function TEncodingInfoPC775Baltic.Name: string;
begin
  Result := 'IBM775';
end;

class function TEncodingInfoPC775Baltic.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoKOI8U }

class function TEncodingInfoKOI8U.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'KOI8-U';
  else
    Error;
  end;
end;

class function TEncodingInfoKOI8U.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoKOI8U.MIBenum: Integer;
begin
  Result := 2088;
end;

class function TEncodingInfoKOI8U.Name: string;
begin
  Result := 'KOI8-U';
end;

class function TEncodingInfoKOI8U.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM00858 }

class function TEncodingInfoIBM00858.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM00858';
    1: Result := 'CCSID00858';
    2: Result := 'CP00858';
    3: Result := 'PC-Multilingual-850+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM00858.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM00858.MIBenum: Integer;
begin
  Result := 2089;
end;

class function TEncodingInfoIBM00858.Name: string;
begin
  Result := 'IBM00858';
end;

class function TEncodingInfoIBM00858.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM00924 }

class function TEncodingInfoIBM00924.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM00924';
    1: Result := 'CCSID00924';
    2: Result := 'CP00924';
    3: Result := 'ebcdic-Latin9--euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM00924.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM00924.MIBenum: Integer;
begin
  Result := 2090;
end;

class function TEncodingInfoIBM00924.Name: string;
begin
  Result := 'IBM00924';
end;

class function TEncodingInfoIBM00924.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01140 }

class function TEncodingInfoIBM01140.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01140';
    1: Result := 'CCSID01140';
    2: Result := 'CP01140';
    3: Result := 'ebcdic-us-37+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01140.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01140.MIBenum: Integer;
begin
  Result := 2091;
end;

class function TEncodingInfoIBM01140.Name: string;
begin
  Result := 'IBM01140';
end;

class function TEncodingInfoIBM01140.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01141 }

class function TEncodingInfoIBM01141.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01141';
    1: Result := 'CCSID01141';
    2: Result := 'CP01141';
    3: Result := 'ebcdic-de-273+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01141.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01141.MIBenum: Integer;
begin
  Result := 2092;
end;

class function TEncodingInfoIBM01141.Name: string;
begin
  Result := 'IBM01141';
end;

class function TEncodingInfoIBM01141.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01142 }

class function TEncodingInfoIBM01142.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01142';
    1: Result := 'CCSID01142';
    2: Result := 'CP01142';
    3: Result := 'ebcdic-dk-277+euro';
    4: Result := 'ebcdic-no-277+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01142.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIBM01142.MIBenum: Integer;
begin
  Result := 2093;
end;

class function TEncodingInfoIBM01142.Name: string;
begin
  Result := 'IBM01142';
end;

class function TEncodingInfoIBM01142.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01143 }

class function TEncodingInfoIBM01143.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01143';
    1: Result := 'CCSID01143';
    2: Result := 'CP01143';
    3: Result := 'ebcdic-fi-278+euro';
    4: Result := 'ebcdic-se-278+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01143.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoIBM01143.MIBenum: Integer;
begin
  Result := 2094;
end;

class function TEncodingInfoIBM01143.Name: string;
begin
  Result := 'IBM01143';
end;

class function TEncodingInfoIBM01143.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01144 }

class function TEncodingInfoIBM01144.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01144';
    1: Result := 'CCSID01144';
    2: Result := 'CP01144';
    3: Result := 'ebcdic-it-280+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01144.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01144.MIBenum: Integer;
begin
  Result := 2095;
end;

class function TEncodingInfoIBM01144.Name: string;
begin
  Result := 'IBM01144';
end;

class function TEncodingInfoIBM01144.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01145 }

class function TEncodingInfoIBM01145.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01145';
    1: Result := 'CCSID01145';
    2: Result := 'CP01145';
    3: Result := 'ebcdic-es-284+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01145.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01145.MIBenum: Integer;
begin
  Result := 2096;
end;

class function TEncodingInfoIBM01145.Name: string;
begin
  Result := 'IBM01145';
end;

class function TEncodingInfoIBM01145.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01146 }

class function TEncodingInfoIBM01146.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01146';
    1: Result := 'CCSID01146';
    2: Result := 'CP01146';
    3: Result := 'ebcdic-gb-285+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01146.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01146.MIBenum: Integer;
begin
  Result := 2097;
end;

class function TEncodingInfoIBM01146.Name: string;
begin
  Result := 'IBM01146';
end;

class function TEncodingInfoIBM01146.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01147 }

class function TEncodingInfoIBM01147.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01147';
    1: Result := 'CCSID01147';
    2: Result := 'CP01147';
    3: Result := 'ebcdic-fr-297+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01147.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01147.MIBenum: Integer;
begin
  Result := 2098;
end;

class function TEncodingInfoIBM01147.Name: string;
begin
  Result := 'IBM01147';
end;

class function TEncodingInfoIBM01147.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01148 }

class function TEncodingInfoIBM01148.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01148';
    1: Result := 'CCSID01148';
    2: Result := 'CP01148';
    3: Result := 'ebcdic-international-500+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01148.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01148.MIBenum: Integer;
begin
  Result := 2099;
end;

class function TEncodingInfoIBM01148.Name: string;
begin
  Result := 'IBM01148';
end;

class function TEncodingInfoIBM01148.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM01149 }

class function TEncodingInfoIBM01149.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM01149';
    1: Result := 'CCSID01149';
    2: Result := 'CP01149';
    3: Result := 'ebcdic-is-871+euro';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM01149.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoIBM01149.MIBenum: Integer;
begin
  Result := 2100;
end;

class function TEncodingInfoIBM01149.Name: string;
begin
  Result := 'IBM01149';
end;

class function TEncodingInfoIBM01149.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoBig5HKSCS }

class function TEncodingInfoBig5HKSCS.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Big5-HKSCS';
  else
    Error;
  end;
end;

class function TEncodingInfoBig5HKSCS.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoBig5HKSCS.MIBenum: Integer;
begin
  Result := 2101;
end;

class function TEncodingInfoBig5HKSCS.Name: string;
begin
  Result := 'Big5-HKSCS';
end;

class function TEncodingInfoBig5HKSCS.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoIBM1047 }

class function TEncodingInfoIBM1047.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'IBM1047';
    1: Result := 'IBM-1047';
  else
    Error;
  end;
end;

class function TEncodingInfoIBM1047.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoIBM1047.MIBenum: Integer;
begin
  Result := 2102;
end;

class function TEncodingInfoIBM1047.Name: string;
begin
  Result := 'IBM1047';
end;

class function TEncodingInfoIBM1047.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoPTCP154 }

class function TEncodingInfoPTCP154.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'PTCP154';
    1: Result := 'csPTCP154';
    2: Result := 'PT154';
    3: Result := 'CP154';
    4: Result := 'Cyrillic-Asian';
  else
    Error;
  end;
end;

class function TEncodingInfoPTCP154.AliasCount: Integer;
begin
  Result := 5;
end;

class function TEncodingInfoPTCP154.MIBenum: Integer;
begin
  Result := 2103;
end;

class function TEncodingInfoPTCP154.Name: string;
begin
  Result := 'PTCP154';
end;

class function TEncodingInfoPTCP154.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoAmiga1251 }

class function TEncodingInfoAmiga1251.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'Amiga-1251';
    1: Result := 'Ami1251';
    2: Result := 'Amiga1251';
    3: Result := 'Ami-1251';
  else
    Error;
  end;
end;

class function TEncodingInfoAmiga1251.AliasCount: Integer;
begin
  Result := 4;
end;

class function TEncodingInfoAmiga1251.MIBenum: Integer;
begin
  Result := 2104;
end;

class function TEncodingInfoAmiga1251.Name: string;
begin
  Result := 'Amiga-1251';
end;

class function TEncodingInfoAmiga1251.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoKOI7switched }

class function TEncodingInfoKOI7switched.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'KOI7-switched';
  else
    Error;
  end;
end;

class function TEncodingInfoKOI7switched.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoKOI7switched.MIBenum: Integer;
begin
  Result := 2105;
end;

class function TEncodingInfoKOI7switched.Name: string;
begin
  Result := 'KOI7-switched';
end;

class function TEncodingInfoKOI7switched.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoBRF }

class function TEncodingInfoBRF.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'BRF';
    1: Result := 'csBRF';
  else
    Error;
  end;
end;

class function TEncodingInfoBRF.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoBRF.MIBenum: Integer;
begin
  Result := 2106;
end;

class function TEncodingInfoBRF.Name: string;
begin
  Result := 'BRF';
end;

class function TEncodingInfoBRF.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoTSCII }

class function TEncodingInfoTSCII.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'TSCII';
    1: Result := 'csTSCII';
  else
    Error;
  end;
end;

class function TEncodingInfoTSCII.AliasCount: Integer;
begin
  Result := 2;
end;

class function TEncodingInfoTSCII.MIBenum: Integer;
begin
  Result := 2107;
end;

class function TEncodingInfoTSCII.Name: string;
begin
  Result := 'TSCII';
end;

class function TEncodingInfoTSCII.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1250 }

class function TEncodingInfoWindows1250.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1250';
    1: Result := 'cp1250';        // Non-standard alias
    2: Result := 'WinLatin2';     // Non-standard alias
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1250.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoWindows1250.MIBenum: Integer;
begin
  Result := 2250;
end;

class function TEncodingInfoWindows1250.Name: string;
begin
  Result := 'windows-1250';
end;

class function TEncodingInfoWindows1250.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1251 }

class function TEncodingInfoWindows1251.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1251';
    1: Result := 'cp1251';        // Non-standard alias
    2: Result := 'WinCyrillic';   // Non-standard alias
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1251.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoWindows1251.MIBenum: Integer;
begin
  Result := 2251;
end;

class function TEncodingInfoWindows1251.Name: string;
begin
  Result := 'windows-1251';
end;

class function TEncodingInfoWindows1251.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1252 }

class function TEncodingInfoWindows1252.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1252';
    1: Result := 'cp1252';        // Non-standard alias
    2: Result := 'WinLatin1';     // Non-standard alias
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1252.AliasCount: Integer;
begin
  Result := 3;
end;

class function TEncodingInfoWindows1252.MIBenum: Integer;
begin
  Result := 2252;
end;

class function TEncodingInfoWindows1252.Name: string;
begin
  Result := 'windows-1252';
end;

class function TEncodingInfoWindows1252.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1253 }

class function TEncodingInfoWindows1253.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1253';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1253.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoWindows1253.MIBenum: Integer;
begin
  Result := 2253;
end;

class function TEncodingInfoWindows1253.Name: string;
begin
  Result := 'windows-1253';
end;

class function TEncodingInfoWindows1253.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1254 }

class function TEncodingInfoWindows1254.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1254';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1254.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoWindows1254.MIBenum: Integer;
begin
  Result := 2254;
end;

class function TEncodingInfoWindows1254.Name: string;
begin
  Result := 'windows-1254';
end;

class function TEncodingInfoWindows1254.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1255 }

class function TEncodingInfoWindows1255.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1255';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1255.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoWindows1255.MIBenum: Integer;
begin
  Result := 2255;
end;

class function TEncodingInfoWindows1255.Name: string;
begin
  Result := 'windows-1255';
end;

class function TEncodingInfoWindows1255.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1256 }

class function TEncodingInfoWindows1256.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1256';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1256.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoWindows1256.MIBenum: Integer;
begin
  Result := 2256;
end;

class function TEncodingInfoWindows1256.Name: string;
begin
  Result := 'windows-1256';
end;

class function TEncodingInfoWindows1256.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1257 }

class function TEncodingInfoWindows1257.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1257';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1257.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoWindows1257.MIBenum: Integer;
begin
  Result := 2257;
end;

class function TEncodingInfoWindows1257.Name: string;
begin
  Result := 'windows-1257';
end;

class function TEncodingInfoWindows1257.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoWindows1258 }

class function TEncodingInfoWindows1258.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'windows-1258';
  else
    Error;
  end;
end;

class function TEncodingInfoWindows1258.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoWindows1258.MIBenum: Integer;
begin
  Result := 2258;
end;

class function TEncodingInfoWindows1258.Name: string;
begin
  Result := 'windows-1258';
end;

class function TEncodingInfoWindows1258.PreferredMIMEName: string;
begin
  Result := '';
end;

{ TEncodingInfoTIS620 }

class function TEncodingInfoTIS620.Alias(I: Integer): string;
begin
  case I of
    0: Result := 'TIS-620';
  else
    Error;
  end;
end;

class function TEncodingInfoTIS620.AliasCount: Integer;
begin
  Result := 1;
end;

class function TEncodingInfoTIS620.MIBenum: Integer;
begin
  Result := 2259;
end;

class function TEncodingInfoTIS620.Name: string;
begin
  Result := 'TIS-620';
end;

class function TEncodingInfoTIS620.PreferredMIMEName: string;
begin
  Result := '';
end;

end.

