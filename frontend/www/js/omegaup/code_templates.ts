import { SafeStorage } from './safe_storage';

const storagePrefix = 'codeTemplates';

export function getCurrentUsername(): string | null {
  const element = document.getElementById('header-payload');
  if (!element || !element.textContent) {
    return null;
  }
  try {
    const payload = JSON.parse(element.textContent);
    if (
      typeof payload === 'object' &&
      payload !== null &&
      typeof payload.currentUsername === 'string' &&
      payload.currentUsername !== ''
    ) {
      return payload.currentUsername;
    }
  } catch (e) {
    return null;
  }
  return null;
}

export function codeTemplateKey(
  extension: string,
  username: string | null = getCurrentUsername(),
): string {
  if (username) {
    return `${storagePrefix}:${username}:${extension}`;
  }
  return `${storagePrefix}:${extension}`;
}

export function getCodeTemplate(
  extension: string,
  username: string | null = getCurrentUsername(),
): string | null {
  if (!extension) {
    return null;
  }
  return SafeStorage.getItem(codeTemplateKey(extension, username));
}

export function setCodeTemplate(
  extension: string,
  source: string,
  username: string | null = getCurrentUsername(),
): boolean {
  if (!extension) {
    return false;
  }
  return SafeStorage.setItem(codeTemplateKey(extension, username), source);
}

export function removeCodeTemplate(
  extension: string,
  username: string | null = getCurrentUsername(),
): boolean {
  if (!extension) {
    return false;
  }
  return SafeStorage.removeItem(codeTemplateKey(extension, username));
}
