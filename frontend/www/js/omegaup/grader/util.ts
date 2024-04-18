export interface LanguageInfo {
  extension: string;
  name: string;
  modelMapping: string;
  language: string;
}
export const supportedLanguages: Record<string, LanguageInfo> = {
  '': { extension: '', name: '', modelMapping: '', language: '' },
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
  c: {
    extension: 'c',
    name: 'C11 (gcc 10.3)',
    modelMapping: 'cpp',
    language: 'c',
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
  cpp: {
    extension: 'cpp',
    name: 'C++03 (g++ 10.3)',
    modelMapping: 'cpp',
    language: 'cpp',
  },
  cpp11: {
    extension: 'cpp',
    name: 'C++11 (g++ 10.3)',
    modelMapping: 'cpp',
    language: 'cpp11',
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
  py: {
    extension: 'py',
    name: 'Python (2.7)',
    modelMapping: 'python',
    language: 'py',
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
};
export const supportedExtensions: string[] = [
  ...new Set(
    Object.values(supportedLanguages).map((language) => language.extension),
  ),
];
