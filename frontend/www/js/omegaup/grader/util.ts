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
export const supportedLanguages: Record<string, LanguageInfo> = {
  kp: {
    extension: 'kp',
    name: 'Karel (Pascal)',
    modelMapping: '',
    language: 'kp',
  },
  kj: {
    extension: 'kj',
    name: 'Karel (Java)',
    modelMapping: '',
    language: 'kj',
  },
  'c11-gcc': {
    extension: 'c',
    name: 'C11 (gcc 10.3)',
    modelMapping: 'cpp',
    language: 'c11-gcc',
  },
  'c11-clang': {
    extension: 'c',
    name: 'C11 (clang 10.0)',
    modelMapping: 'cpp',
    language: 'c11-clang',
  },
  'cpp11-gcc': {
    extension: 'cpp',
    name: 'C++11 (g++ 10.3)',
    modelMapping: 'cpp',
    language: 'cpp11-gcc',
  },
  'cpp11-clang': {
    extension: 'cpp',
    name: 'C++11 (clang++ 10.0)',
    modelMapping: 'cpp',
    language: 'cpp11-clang',
  },
  'cpp17-gcc': {
    extension: 'cpp',
    name: 'C++17 (g++ 10.3)',
    modelMapping: 'cpp',
    language: 'cpp17-gcc',
  },
  'cpp17-clang': {
    extension: 'cpp',
    name: 'C++17 (clang++ 10.0)',
    modelMapping: 'cpp',
    language: 'cpp17-clang',
  },
  'cpp20-gcc': {
    extension: 'cpp',
    name: 'C++20 (g++ 10.3)',
    modelMapping: 'cpp',
    language: 'cpp20-gcc',
  },
  'cpp20-clang': {
    extension: 'cpp',
    name: 'C++20 (clang++ 10.0)',
    modelMapping: 'cpp',
    language: 'cpp20-clang',
  },
  java: {
    extension: 'java',
    name: 'Java (openjdk 16.0)',
    modelMapping: 'java',
    language: 'java',
  },
  kt: {
    extension: 'kt',
    name: 'Kotlin (1.6.10)',
    modelMapping: 'kotlin',
    language: 'kt',
  },
  py2: {
    extension: 'py',
    name: 'Python (2.7)',
    modelMapping: 'python',
    language: 'py2',
  },
  py3: {
    extension: 'py',
    name: 'Python (3.9)',
    modelMapping: 'python',
    language: 'py3',
  },
  rb: {
    extension: 'rb',
    name: 'Ruby (2.7)',
    modelMapping: 'ruby',
    language: 'rb',
  },
  cs: {
    extension: 'cs',
    name: 'C# (10, dotnet 6.0)',
    modelMapping: 'csharp',
    language: 'cs',
  },
  pas: {
    extension: 'pas',
    name: 'Pascal (fpc 3.0)',
    modelMapping: 'pascal',
    language: 'pas',
  },
  hs: {
    extension: 'hs',
    name: 'Haskell (ghc 8.8)',
    modelMapping: 'haskell',
    language: 'hs',
  },
  lua: {
    extension: 'lua',
    name: 'Lua (5.3)',
    modelMapping: 'lua',
    language: 'lua',
  },
  go: {
    extension: 'go',
    name: 'Go (1.18.beta2)',
    modelMapping: 'go',
    language: 'go',
  },
  rs: {
    extension: 'rs',
    name: 'Rust (1.56.1)',
    modelMapping: 'rust',
    language: 'rs',
  },
  js: {
    extension: 'js',
    name: 'JavaScript (Node.js 16)',
    modelMapping: 'javascript',
    language: 'js',
  },
  cat: {
    extension: 'cat',
    name: 'Output Only',
    modelMapping: '',
    language: 'cat',
  },
};
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
