export function vuexGet(store, name) {
  if (typeof store.getters[name] !== 'undefined') return store.getters[name];
  var o = store.state;
  for (let p of name.split('.')) {
    if (typeof o === 'undefined') return undefined;
    if (!o.hasOwnProperty(p)) return undefined;
    o = o[p];
  }
  return o;
}

export function vuexSet(store, name, value) {
  store.commit(name, value);
}

export function parseDuration(value) {
  if (typeof(value) === 'number') {
    return value;
  }

  let result = 0.0;
  for (let chunk of value.match(/\d+(ns|us|µs|m|s)?/g)) {
    let scale = 1.0;
    if (chunk.indexOf('ns') === chunk.length - 2) {
      scale = 1e-9;
    } else if (chunk.indexOf('us') === chunk.length - 2 ||
               chunk.indexOf('µs') === chunk.length - 2) {
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
  cpp: 'cpp',
  cs: 'csharp',
  java: 'java',
  lua: 'lua',
  py: 'python',
  rb: 'ruby',

  // Fake languages.
  idl: 'text',
  in: 'text',
  out: 'text',
  err: 'text',
};

export const languageExtensionMapping = {
  cpp11: 'cpp',
  cpp: 'cpp',
  cs: 'cs',
  java: 'java',
  lua: 'lua',
  py: 'py',
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
