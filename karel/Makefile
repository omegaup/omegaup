all: kcl karel

lib/x86_64-linux:
	mkdir -p lib/x86_64-linux

karel: CodecUtilsWin32.pas EncodingUtils.pas FastStringFuncs.pas FastStrings.pas karel.lpi karel.lpr karel.manifest karel.rc karel.res NativeXmlObjectStorage.pas NativeXmlOld.pas NativeXml.pas NativeXmlWin32Compat.pas sdDebug.pas sdSortedLists.pas sdStreams.pas sdStringTable.pas simdesign.inc UKEntorno.pas UKMundo.pas UKProgramaCompilado.pas lib/x86_64-linux
	lazbuild karel.lpi

kcl: CodecUtilsWin32.pas EncodingUtils.pas kcl.lpi kcl.lpr kcl.manifest kcl.rc kcl.res NativeXmlObjectStorage.pas NativeXmlOld.pas NativeXml.pas NativeXmlWin32Compat.pas sdDebug.pas sdSortedLists.pas sdStreams.pas sdStringTable.pas simdesign.inc UCompilador_V3.pas UKProgramaCompilado.pas lib/x86_64-linux
	lazbuild kcl.lpi

clean:
	rm -rf lib
	rm -f kcl
	rm -f karel
