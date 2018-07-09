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

  let scale = 1.0;
  if (value.indexOf('ns') === value.length - 2) {
    scale = 1e-9;
  } else if (value.indexOf('us') === value.length - 2 ||
             value.indexOf('µs') === value.length - 2) {
    scale = 1e-6;
  } else if (value.indexOf('ms') === value.length - 2) {
    scale = 1e-3;
  }
  return scale * parseFloat(value);
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
