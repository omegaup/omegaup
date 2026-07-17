import JSZip from 'jszip';

import T from '../../lang';
import * as ui from '../../ui';
import {
  downloadInputFile,
  downloadZipFile,
  showUpdateSuccessMessage,
} from './downloadHandlers';

describe('downloadHandlers', () => {
  it('showUpdateSuccessMessage shows the success notification', () => {
    const successSpy = jest.spyOn(ui, 'success').mockImplementation(() => {});

    showUpdateSuccessMessage();

    expect(successSpy).toHaveBeenCalledWith(T.problemCreatorUpdateAlert);
    successSpy.mockRestore();
  });

  it('downloadInputFile triggers a download of the input file', () => {
    const click = jest.fn();
    const anchor = {
      href: '',
      download: '',
      click,
    } as unknown as HTMLAnchorElement;
    const createElement = jest
      .spyOn(document, 'createElement')
      .mockReturnValue(anchor);
    const appendChild = jest
      .spyOn(document.body, 'appendChild')
      .mockImplementation((node) => node);
    const removeChild = jest
      .spyOn(document.body, 'removeChild')
      .mockImplementation((node) => node);
    (global.URL as any).createObjectURL = jest.fn(() => 'blob:input');
    (global.URL as any).revokeObjectURL = jest.fn();

    downloadInputFile({ fileName: 'cases/1.in', fileContent: 'content' });

    expect(anchor.download).toBe('cases/1.in');
    expect(click).toHaveBeenCalled();

    createElement.mockRestore();
    appendChild.mockRestore();
    removeChild.mockRestore();
  });

  it('downloadZipFile generates and downloads the zip file', async () => {
    const click = jest.fn();
    const anchor = {
      href: '',
      download: '',
      click,
    } as unknown as HTMLAnchorElement;
    const createElement = jest
      .spyOn(document, 'createElement')
      .mockReturnValue(anchor);
    const appendChild = jest
      .spyOn(document.body, 'appendChild')
      .mockImplementation((node) => node);
    const removeChild = jest
      .spyOn(document.body, 'removeChild')
      .mockImplementation((node) => node);
    (global.URL as any).createObjectURL = jest.fn(() => 'blob:zip');
    (global.URL as any).revokeObjectURL = jest.fn();

    const zipContent = {
      generateAsync: jest.fn().mockResolvedValue(new Blob()),
    };
    downloadZipFile({
      fileName: 'problem',
      zipContent: zipContent as unknown as JSZip,
    });
    await Promise.resolve();
    await Promise.resolve();

    expect(zipContent.generateAsync).toHaveBeenCalledWith({ type: 'blob' });
    expect(anchor.download).toBe('problem.zip');
    expect(click).toHaveBeenCalled();

    createElement.mockRestore();
    appendChild.mockRestore();
    removeChild.mockRestore();
  });
});
