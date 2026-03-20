import linksConfig from '../../../templates/external-links-config.json';

export type ExternalLinkKey = keyof typeof linksConfig;

/**
 * Gets an external URL from the centralized configuration.
 * @param key - The key for the external link
 * @returns The URL string
 */
export function getExternalUrl(key: ExternalLinkKey): string {
  const url = linksConfig[key];
  if (!url) {
    console.error(
      `External link key "${key}" not found in external-links-config.json`,
    );
    return linksConfig['OmegaUpBlogURL']; // Fallback
  }
  return url;
}

/**
 * Gets a blog URL from the centralized configuration.
 * @deprecated Use getExternalUrl instead. This is kept for backwards compatibility.
 * @param key - The key for the blog link
 * @returns The URL string
 */
export function getBlogUrl(key: ExternalLinkKey): string {
  return getExternalUrl(key);
}

// Legacy type alias for backwards compatibility
export type BlogLinkKey = ExternalLinkKey;
