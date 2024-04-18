export function vuexGet(store, name) {
  if (typeof store.getters[name] !== 'undefined') return store.getters[name];
  var o = store.state;
  for (let p of name.split('.')) {
    if (typeof o === 'undefined') return undefined;
    if (!Object.prototype.hasOwnProperty.call(o, p)) return undefined;
    o = o[p];
  }
  return o;
}

export function vuexSet(store, name, value) {
  store.commit(name, value);
}

export function parseDuration(value) {
  if (typeof value === 'number') {
    return value;
  }

  let result = 0.0;
  for (let chunk of value.match(/\d+(ns|us|µs|m|s)?/g)) {
    let scale = 1.0;
    if (chunk.indexOf('ns') === chunk.length - 2) {
      scale = 1e-9;
    } else if (
      chunk.indexOf('us') === chunk.length - 2 ||
      chunk.indexOf('µs') === chunk.length - 2
    ) {
      scale = 1e-6;
    } else if (chunk.indexOf('ms') === chunk.length - 2) {
      scale = 1e-3;
    } else if (chunk.indexOf('m') === chunk.length - 1) {
      scale = 60;
    }
    result += scale * parseFloat(chunk);
  }
  return result;
}

export const supportedLanguages = {
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
export const validExtensions = [
  ...new Set(
    Object.values(supportedLanguages).map((language) => language.extension),
  ),
];
export function asyncError(err) {
  console.error('Async error', err);
}

// Wraps a function `f(...args)` into `f(key)(...args)` that is called at most
// once every `delay` milliseconds. `f(key).flush()` will cause the function to
// be called immediately.
export function throttle(f, delay) {
  let timeouts = {};
  const throttled = (key) => {
    let wrapped;
    if (Object.prototype.hasOwnProperty.call(timeouts, key)) {
      wrapped = (...args) => {
        timeouts[key].args = args;
      };
    } else {
      wrapped = (...args) => {
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
      if (!Object.prototype.hasOwnProperty.call(timeouts, key)) {
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
