const assert = require('assert');
const fs = require('fs');
const karel = require('../js/karel.js');
const DOMParser = require('xmldom').DOMParser;

describe('kareljava compilation', function () {
  it('should detect duplicate methods', function () {
    const code =
      'class program {\n' +
      'define turnright() {\nturnleft();\n}\n' +
      'define turnright() {\nturnleft();\n}\n' +
      'program() {\nturnright();\n}\n' +
      '}';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Function redefinition: turnright');
        assert.equal(e.hash.text, 'turnright');
        assert.equal(e.hash.line + 1, 5);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect mismatched parameters', function () {
    const code =
      'class program {\n' +
      'define turnright(x) {\nturnleft();\n}\n' +
      'program() {\nturnright();\n}\n' +
      '}';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Function parameter mismatch: turnright');
        assert.equal(e.hash.text, 'turnright');
        assert.equal(e.hash.line + 1, 6);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect undefined functions', function () {
    const code = 'class program {\n' + 'program() {\nturnright();\n}\n' + '}';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Undefined function: turnright');
        assert.equal(e.hash.text, 'turnright');
        assert.equal(e.hash.line + 1, 3);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect undefined parameters', function () {
    const code =
      'class program {\n' + 'program() {\niterate(x) turnleft();\n}\n' + '}';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Unknown variable: x');
        assert.equal(e.hash.text, 'x');
        assert.equal(e.hash.line + 1, 3);

        return true;
      },
      'Should have failed to compile',
    );
  });
});

describe('karelpascal compilation', function () {
  it('should detect duplicate prototypes', function () {
    const code =
      'iniciar-programa\n' +
      'define-prototipo-instruccion gira-derecha;\n' +
      'define-prototipo-instruccion gira-derecha;\n' +
      'inicia-ejecucion\ngira-izquierda;\ntermina-ejecucion\n' +
      'finalizar-programa';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Prototype redefinition: gira-derecha');
        assert.equal(e.hash.text, 'gira-derecha');
        assert.equal(e.hash.line + 1, 3);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect duplicate methods', function () {
    const code =
      'iniciar-programa\n' +
      'define-nueva-instruccion gira-derecha como inicio\ngira-izquierda;\nfin;\n' +
      'define-nueva-instruccion gira-derecha como inicio\ngira-izquierda;\nfin;\n' +
      'inicia-ejecucion\ngira-izquierda;\ntermina-ejecucion\n' +
      'finalizar-programa';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Function redefinition: gira-derecha');
        assert.equal(e.hash.text, 'gira-derecha');
        assert.equal(e.hash.line + 1, 5);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect prototype mismatches', function () {
    const code =
      'iniciar-programa\n' +
      'define-prototipo-instruccion gira-derecha(x);\n' +
      'define-nueva-instruccion gira-derecha como inicio\ngira-izquierda;\nfin;\n' +
      'inicia-ejecucion\ngira-izquierda;\ntermina-ejecucion\n' +
      'finalizar-programa';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Prototype parameter mismatch: gira-derecha');
        assert.equal(e.hash.text, 'gira-derecha');
        assert.equal(e.hash.line + 1, 3);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect mismatched parameters', function () {
    const code =
      'iniciar-programa\n' +
      'define-nueva-instruccion gira-derecha(x) como inicio\ngira-izquierda;\nfin;\n' +
      'inicia-ejecucion\ngira-derecha;\ntermina-ejecucion\n' +
      'finalizar-programa';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Function parameter mismatch: gira-derecha');
        assert.equal(e.hash.text, 'gira-derecha');
        assert.equal(e.hash.line + 1, 6);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect undefined functions', function () {
    const code =
      'iniciar-programa\n' +
      'inicia-ejecucion\ngira-derecha;\ntermina-ejecucion\n' +
      'finalizar-programa';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Undefined function: gira-derecha');
        assert.equal(e.hash.text, 'gira-derecha');
        assert.equal(e.hash.line + 1, 3);

        return true;
      },
      'Should have failed to compile',
    );
  });

  it('should detect undefined parameters', function () {
    const code =
      'iniciar-programa\n' +
      'inicia-ejecucion\ngira-derecha(x);\ntermina-ejecucion\n' +
      'finalizar-programa';

    assert.throws(
      function () {
        karel.compile(code);
      },
      function (e) {
        assert.equal(e.message, 'Unknown variable: x');
        assert.equal(e.hash.text, 'x');
        assert.equal(e.hash.line + 1, 3);

        return true;
      },
      'Should have failed to compile',
    );
  });
});
