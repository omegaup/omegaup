import linksConfig from '../../../templates/blog-links-config.json';

export type BlogLinkKey = keyof typeof linksConfig;

export function getBlogUrl(key: BlogLinkKey): string {
  const url = linksConfig[key];
  if (!url) {
    console.error(`Blog link key "${key}" not found in blog-links-config.json`);
    return linksConfig['OmegaUpBlogURL']; // Fallback
  }
  return url;
}
