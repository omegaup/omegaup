import {
  codeTemplateKey,
  getCodeTemplate,
  getCurrentUsername,
  removeCodeTemplate,
  setCodeTemplate,
} from './code_templates';

describe('code_templates', () => {
  afterEach(() => {
    localStorage.clear();
    document.getElementById('header-payload')?.remove();
  });

  function addHeaderPayload(currentUsername: string): void {
    const element = document.createElement('script');
    element.id = 'header-payload';
    element.type = 'text/json';
    element.textContent = JSON.stringify({ currentUsername });
    document.body.appendChild(element);
  }

  it('reads the current username from the header payload', () => {
    expect(getCurrentUsername()).toBeNull();
    addHeaderPayload('omegaup');
    expect(getCurrentUsername()).toBe('omegaup');
  });

  it('names templates by username when available', () => {
    expect(codeTemplateKey('py', 'omegaup')).toBe('codeTemplates:omegaup:py');
    expect(codeTemplateKey('py', null)).toBe('codeTemplates:py');
  });

  it('stores and retrieves a template for the current user', () => {
    addHeaderPayload('omegaup');
    expect(getCodeTemplate('py')).toBeNull();
    expect(setCodeTemplate('py', 'print(1)')).toBe(true);
    expect(getCodeTemplate('py')).toBe('print(1)');
    expect(localStorage.getItem('codeTemplates:omegaup:py')).toBe('print(1)');
  });

  it('removes a stored template', () => {
    addHeaderPayload('omegaup');
    setCodeTemplate('py', 'print(1)');
    expect(removeCodeTemplate('py')).toBe(true);
    expect(getCodeTemplate('py')).toBeNull();
  });

  it('does not accept empty extensions', () => {
    expect(getCodeTemplate('')).toBeNull();
    expect(setCodeTemplate('', 'code')).toBe(false);
    expect(removeCodeTemplate('')).toBe(false);
  });
});
