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
    const element = document.getElementById('header-payload');
    if (element) {
      document.body.removeChild(element);
    }
  });

  const addHeaderPayload = (currentUsername: string) => {
    const element = document.createElement('script');
    element.id = 'header-payload';
    element.setAttribute('type', 'text/json');
    element.textContent = JSON.stringify({ currentUsername });
    document.body.appendChild(element);
  };

  it('Should return no username when the header payload is missing', () => {
    expect(getCurrentUsername()).toBeNull();
  });

  it('Should read the username from the header payload', () => {
    addHeaderPayload('omegaup');
    expect(getCurrentUsername()).toBe('omegaup');
  });

  it('Should namespace keys by username when available', () => {
    expect(codeTemplateKey('py', 'omegaup')).toBe('codeTemplates:omegaup:py');
    expect(codeTemplateKey('py', null)).toBe('codeTemplates:py');
  });

  it('Should save and retrieve a template for the current user', () => {
    addHeaderPayload('omegaup');
    expect(getCodeTemplate('py')).toBeNull();
    expect(setCodeTemplate('py', 'print(1)')).toBe(true);
    expect(localStorage.getItem('codeTemplates:omegaup:py')).toBe('print(1)');
    expect(getCodeTemplate('py')).toBe('print(1)');
  });

  it('Should save and retrieve a template without a logged in user', () => {
    expect(setCodeTemplate('cpp', 'int main() {}')).toBe(true);
    expect(localStorage.getItem('codeTemplates:cpp')).toBe('int main() {}');
    expect(getCodeTemplate('cpp')).toBe('int main() {}');
  });

  it('Should not share templates between users', () => {
    expect(setCodeTemplate('py', 'print(1)', 'user_a')).toBe(true);
    expect(getCodeTemplate('py', 'user_b')).toBeNull();
    expect(getCodeTemplate('py', 'user_a')).toBe('print(1)');
  });

  it('Should remove a stored template', () => {
    addHeaderPayload('omegaup');
    setCodeTemplate('py', 'print(1)');
    expect(removeCodeTemplate('py')).toBe(true);
    expect(getCodeTemplate('py')).toBeNull();
  });

  it('Should ignore empty extensions', () => {
    expect(getCodeTemplate('')).toBeNull();
    expect(setCodeTemplate('', 'code')).toBe(false);
    expect(removeCodeTemplate('')).toBe(false);
  });
});
