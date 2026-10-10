import T from '../../lang';
import * as ui from '../../ui';
import JSZip from 'jszip';

export function showUpdateSuccessMessage(): void {
  ui.success(T.problemCreatorUpdateAlert);
}

export function downloadInputFile({
  fileName,
  fileContent,
}: {
  fileName: string;
  fileContent: string;
}): void {
  const link = document.createElement('a');
  const blob = new Blob([fileContent], { type: 'text/plain' });
  link.href = URL.createObjectURL(blob);
  link.download = fileName;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(link.href);
}

export function downloadZipFile({
  fileName,
  zipContent,
}: {
  fileName: string;
  zipContent: JSZip;
}): void {
  zipContent.generateAsync({ type: 'blob' }).then((content) => {
    const link = document.createElement('a');
    link.href = URL.createObjectURL(content);
    link.download = `${fileName}.zip`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
  });
}
