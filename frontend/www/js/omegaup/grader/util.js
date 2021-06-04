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

export const languageMonacoModelMapping = {
  cpp11: 'cpp',
  'cpp11-gcc': 'cpp',
  'cpp11-clang': 'cpp',
  'cpp17-gcc': 'cpp',
  'cpp17-clang': 'cpp',
  cpp: 'cpp',
  cs: 'csharp',
  java: 'java',
  lua: 'lua',
  py: 'python',
  py2: 'python',
  py3: 'python',
  rb: 'ruby',

  // Fake languages.
  idl: 'text',
  in: 'text',
  out: 'text',
  err: 'text',
};

export const languageExtensionMapping = {
  cpp11: 'cpp',
  'cpp11-gcc': 'cpp',
  'cpp11-clang': 'cpp',
  'cpp17-gcc': 'cpp',
  'cpp17-clang': 'cpp',
  cpp: 'cpp',
  cs: 'cs',
  java: 'java',
  lua: 'lua',
  py: 'py',
  py2: 'py',
  py3: 'py',
  rb: 'rb',

  // Fake languages.
  idl: 'idl',
  in: 'in',
  out: 'out',
  err: 'err',
};

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
