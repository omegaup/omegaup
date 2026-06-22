import languages from '../../../../data/languages.json';
import { types } from '../api_types';

export function vuexGet(store: any, name: string) {
  if (typeof store.getters[name] !== 'undefined') return store.getters[name];
  let o = store.state;
  for (const p of name.split('.')) {
    if (typeof o === 'undefined') return undefined;
    if (!Object.prototype.hasOwnProperty.call(o, p)) return undefined;
    o = o[p];
  }
  return o;
}

export function vuexSet(store: any, name: string, value: any) {
  store.commit(name, value);
}
export const units: { [key: string]: number } = {
  ns: 1e-9,
  us: 1e-6,
  µs: 1e-6,
  ms: 1e-3,
  s: 1,
  m: 60,
  '': 1,
};

export const LANGUAGE_FAMILIES: Record<string, string> = {
  'cpp11-gcc': 'cpp',
  'cpp11-clang': 'cpp',
  'cpp17-gcc': 'cpp',
  'cpp17-clang': 'cpp',
  'cpp20-gcc': 'cpp',
  'cpp20-clang': 'cpp',
  'c11-gcc': 'c',
  'c11-clang': 'c',
  py2: 'python',
  py3: 'python',
};

interface LanguagePattern {
  language: string;
  displayName: string;
  patterns: RegExp[];
  priority: number;
}

const LANGUAGE_PATTERNS: LanguagePattern[] = [
  {
    language: 'cpp20-gcc',
    displayName: 'C++20 (g++ 10.3)',
    patterns: [
      /#include\s*<(iostream|vector|algorithm|string|map|set|queue|stack|cmath|bits\/stdc\+\+\.h)>/,
      /\b(std::cout|std::cin|std::endl|std::vector|std::string|namespace\s+std)\b/,
      /using\s+namespace\s+std/,
    ],
    priority: 95,
  },
  {
    language: 'c11-gcc',
    displayName: 'C11 (gcc 10.3)',
    patterns: [
      /#include\s*<(stdio\.h|stdlib\.h|string\.h|math\.h|stdbool\.h)>/,
      /\b(printf|scanf|malloc|free|sizeof)\s*\(/,
    ],
    priority: 90,
  },
  {
    language: 'java',
    displayName: 'Java (openjdk 16.0)',
    patterns: [
      /^\s*public\s+class\s+\w+/m,
      /^\s*import\s+java\./m,
      /public\s+static\s+void\s+main\s*\(/,
    ],
    priority: 88,
  },
  {
    language: 'kt',
    displayName: 'Kotlin (1.6.10)',
    patterns: [/\bfun\s+main\s*\(/, /\b(val|var)\s+\w+\s*[:=]/, /println\s*\(/],
    priority: 87,
  },
  {
    language: 'cs',
    displayName: 'C# (10, dotnet 6.0)',
    patterns: [/^\s*using\s+System/m, /namespace\s+\w+/, /Console\.WriteLine/],
    priority: 86,
  },
  {
    language: 'py3',
    displayName: 'Python (3.9)',
    patterns: [
      /^(?:import|from)\s+\w+/m,
      /^def\s+\w+\s*\(/m,
      /\bprint\s*\(/,
      /if\s+__name__\s*==\s*['"]__main__['"]/,
    ],
    priority: 85,
  },
  {
    language: 'go',
    displayName: 'Go (1.18.beta2)',
    patterns: [
      /^\s*package\s+\w+/m,
      /^\s*func\s+main\s*\(/m,
      /fmt\.(Println|Printf)/,
    ],
    priority: 84,
  },
  {
    language: 'rs',
    displayName: 'Rust (1.56.1)',
    patterns: [
      /^\s*fn\s+main\s*\(/m,
      /println!\s*\(/,
      /\blet\b.*\bmut\b|use\s+std::/,
    ],
    priority: 83,
  },
  {
    language: 'js',
    displayName: 'JavaScript (Node.js 16)',
    patterns: [
      /\bconsole\.log\b|\bmodule\.exports\b|\brequire\s*\(/,
      /=>\s*\{/,
      /\b(const|let|var)\s+\w+\s*=\s*/,
    ],
    priority: 82,
  },
  {
    language: 'hs',
    displayName: 'Haskell (ghc 8.8)',
    patterns: [/^\s*module\s+\w+\s+where/m, /::/, /\bwhere\b|\bdata\b/],
    priority: 81,
  },
  {
    language: 'rb',
    displayName: 'Ruby (2.7)',
    patterns: [
      /^\s*(require|require_relative)\s+['"]/m,
      /^\s*def\s+\w+/m,
      /\b(puts|gets)\b/,
    ],
    priority: 78,
  },
  {
    language: 'pas',
    displayName: 'Pascal (fpc 3.0)',
    patterns: [
      /\b(program|unit)\s+\w+\s*;/i,
      /\b(begin|end)\b/i,
      /writeln\s*\(/i,
    ],
    priority: 77,
  },
  {
    language: 'lua',
    displayName: 'Lua (5.3)',
    patterns: [
      /\blocal\s+\w+/,
      /\bfunction\s+\w+\s*\(/,
      /\b(print|io\.write)\b/,
    ],
    priority: 76,
  },
];

export function detectLanguageFromCode(
  code: string,
): { language: string; displayName: string } | null {
  if (!code || code.trim().length < 10) return null;
  const trimmed = code.trim();

  let bestLanguage = '';
  let bestDisplayName = '';
  let bestScore = 0;
  let bestPriority = 0;

  for (const pat of LANGUAGE_PATTERNS) {
    let matches = 0;
    for (const r of pat.patterns) {
      if (r.test(trimmed)) matches++;
    }
    if (matches > 0) {
      const score = (matches / pat.patterns.length) * pat.priority;
      if (score > bestScore) {
        bestScore = score;
        bestLanguage = pat.language;
        bestDisplayName = pat.displayName;
        bestPriority = pat.priority;
      }
    }
  }

  if (!bestLanguage) return null;
  const confidence = Math.min(
    100,
    Math.round((bestScore / bestPriority) * 100),
  );
  if (confidence < 30) return null;
  if (!supportedLanguages[bestLanguage]) return null;
  return { language: bestLanguage, displayName: bestDisplayName };
}

export const splitMeasurement = (
  measurement: string,
): {
  numericalValue: number;
  unit: string;
} => {
  for (const unit in units) {
    if (measurement.endsWith(unit)) {
      const numberPart = measurement.slice(0, -unit.length);
      if (!isNaN(parseFloat(numberPart))) {
        return { numericalValue: parseFloat(numberPart), unit };
      } else {
        throw new Error('Invalid input format');
      }
    }
  }

  if (isNaN(parseFloat(measurement))) {
    throw new Error('Invalid input format');
  }
  return { numericalValue: parseFloat(measurement), unit: '' };
};

export function parseDuration(value: number | string) {
  if (typeof value === 'number') {
    return value;
  }

  const { numericalValue, unit } = splitMeasurement(value);
  return numericalValue * units[unit];
}

export interface LanguageInfo {
  extension: string;
  name: string;
  modelMapping: string;
  language: string;
}

export const supportedLanguages: Record<string, LanguageInfo> = languages;

export const supportedExtensions: string[] = [
  ...new Set(
    Object.values(supportedLanguages).map((language) => language.extension),
  ),
];
export const extensionToLanguages: {
  [key: string]: string[];
} = Object.values(supportedLanguages).reduce<{
  [key: string]: string[];
}>((acc, languageInfo) => {
  const { extension, language } = languageInfo;
  if (!acc[extension]) {
    acc[extension] = [];
  }
  acc[extension].push(language);
  return acc;
}, {});

export function asyncError(err: Error) {
  console.error('Async error', err);
}

// Wraps a function `f(...args)` into `f(key)(...args)` that is called at most
// once every `delay` milliseconds. `f(key).flush()` will cause the function to
// be called immediately.
type ThrottledFunction<T extends any[]> = {
  (...args: T): void;
  flush?: () => void;
};

export function throttle<T extends any[]>(
  f: (...args: T) => void,
  delay: number,
): (key: string) => ThrottledFunction<T> {
  const timeouts: {
    [key: string]: { timeout: NodeJS.Timeout; args: T | null };
  } = {};

  const throttled = (key: string): ThrottledFunction<T> => {
    let wrapped: ThrottledFunction<T>;

    if (key in timeouts) {
      wrapped = (...args: T) => {
        timeouts[key].args = args;
      };
    } else {
      wrapped = (...args: T) => {
        f(...args);
        timeouts[key] = {
          timeout: setTimeout(() => {
            const { args } = timeouts[key];
            delete timeouts[key];
            if (args !== null) {
              throttled(key)(...args);
            }
          }, delay),
          args: null,
        };
      };
    }

    wrapped.flush = () => {
      if (!(key in timeouts)) {
        return;
      }
      const { timeout, args } = timeouts[key];
      delete timeouts[key];
      clearTimeout(timeout);
      if (args !== null) {
        f(...args);
      }
    };

    return wrapped;
  };

  return throttled;
}

export enum MonacoThemes {
  VSLight = 'vs',
  VSDark = 'vs-dark',
}
export const DUMMY_PROBLEM: types.ProblemInfo = {
  alias: 'dummy-problem',
  settings: {
    cases: {
      sample: {
        in: '1 2\n',
        out: '3\n',
        weight: 1,
      },
      long: {
        in: '123456789012345678 123456789012345678\n',
        out: '246913578024691356\n',
        weight: 1,
      },
    },
    limits: {
      ExtraWallTime: '5s',
      MemoryLimit: 33554432,
      OutputLimit: 10240,
      OverallWallTimeLimit: '3s',
      TimeLimit: '1s',
    },
    validator: {
      name: 'token-numeric',
      tolerance: 1e-9,
    },
  },
  // the only attributes required for full IDE are the above
  accepts_submissions: false,
  karel_problem: false,
  commit: 'NA',
  languages: [],
  limits: {
    input_limit: '10 KiB',
    memory_limit: '32 MiB',
    overall_wall_time_limit: '1s',
    time_limit: '1s',
  },
  points: 100,
  problem_id: 1,
  problemsetter: {
    classname: 'user-rank-unranked',
    creation_date: new Date(),
    name: 'omegaUp admin',
    username: 'omegaup',
  },
  quality_seal: false,
  sample_input: undefined,
  source: 'omegaUp classics',
  statement: {
    images: {},
    sources: {},
    language: 'en',
    markdown: `# test with embed code
Here we can add code.
<details>
<summary>
  Example:
</summary>

{{sample.cpp}}

</details>
    `,
  },
  title: 'Dummy Problem',
  visibility: 2,
  input_limit: 1000,
};
