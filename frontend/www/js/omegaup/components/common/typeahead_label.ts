export interface TypeaheadLabelPart {
  strong: boolean;
  text: string;
}

// Callers escape the text, then wrap the alias in <strong>. Render that as
// elements so the alias stays bold without v-html.
export function typeaheadLabelParts(label: string): TypeaheadLabelPart[] {
  const parts: TypeaheadLabelPart[] = [];
  const pattern = /<strong>(.*?)<\/strong>/g;
  let lastIndex = 0;
  let match: RegExpExecArray | null;

  while ((match = pattern.exec(label))) {
    if (match.index > lastIndex) {
      parts.push({
        strong: false,
        text: decodeEscapedText(label.slice(lastIndex, match.index)),
      });
    }
    parts.push({ strong: true, text: decodeEscapedText(match[1]) });
    lastIndex = match.index + match[0].length;
  }

  if (lastIndex < label.length) {
    parts.push({
      strong: false,
      text: decodeEscapedText(label.slice(lastIndex)),
    });
  }
  return parts;
}

function decodeEscapedText(text: string): string {
  return text
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
}
