import JSZip from 'jszip';

type ProblemState = any;

export async function buildProblemZip(
  problemState: ProblemState,
  originalZipFile?: File | null,
): Promise<Blob> {
  const newZip = new JSZip();
  let originalZip: JSZip | null = null;

  if (originalZipFile) {
    originalZip = await JSZip.loadAsync(originalZipFile);
  }

  newZip
    .folder('statements')
    ?.file('es.markdown', problemState.problemMarkdown);
  newZip
    .folder('solutions')
    ?.file('es.markdown', problemState.problemSolutionMarkdown);

  const casesFolder = newZip.folder('cases');
  let testPlanData: string = '';
  const caseProcessingPromises: Promise<void>[] = [];

  problemState.casesStore.groups.forEach((_group: any) => {
    _group.cases.forEach((_case: any) => {
      let fileName = _case.name;
      if (_group.ungroupedCase === false) {
        fileName = `${_group.name}.${fileName}`;
      }
      testPlanData += `${fileName} ${_case.points}\n`;

      const inPromise = (async () => {
        let inputContent = '';
        if (_case.lines && _case.lines.length > 0) {
          inputContent = _case.lines
            .map((line: any) => line.data.value)
            .join('\n');
        }
        const pathInZip = `${fileName}.in`;

        if (inputContent?.endsWith('...[TRUNCATED]') && originalZip) {
          const originalFile = originalZip.file(`cases/${pathInZip}`);
          if (originalFile) {
            const originalContent = await originalFile.async('blob');
            casesFolder?.file(pathInZip, originalContent);
          }
        } else {
          casesFolder?.file(pathInZip, inputContent);
        }
      })();
      caseProcessingPromises.push(inPromise);

      const outPromise = (async () => {
        const pathInZip = `${fileName}.out`;
        const outputContent = _case.output;

        if (outputContent?.endsWith('...[TRUNCATED]') && originalZip) {
          const originalFile = originalZip.file(`cases/${pathInZip}`);
          if (originalFile) {
            const originalContent = await originalFile.async('blob');
            casesFolder?.file(pathInZip, originalContent);
          }
        } else {
          casesFolder?.file(pathInZip, outputContent);
        }
      })();
      caseProcessingPromises.push(outPromise);
    });
  });

  await Promise.all(caseProcessingPromises);

  newZip.file('testplan', testPlanData);
  newZip.file('cdp.data', JSON.stringify(problemState));

  return newZip.generateAsync({
    type: 'blob',
    compression: 'DEFLATE',
    compressionOptions: { level: 1 },
  });
}
