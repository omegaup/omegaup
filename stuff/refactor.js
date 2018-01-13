#!/usr/bin/env node

// Multi-purpose refactor script.
// Currently it only enforces that all Promise objects have a .fail().

const babylon = require('babylon');
const fs = require('fs');
const process = require('process');
const t = require('babel-types');
const traverse = require('babel-traverse').default;

if (process.argv.length != 4) {
  console.error('Usage: ' + process.argv[1] + ' <filename> <original filename>');
  process.exit(1);
}

const filename = process.argv[2];
const buf = fs.readFileSync(filename, 'utf8');
const isModule = process.argv[3].indexOf('/js/omegaup/') != -1;
const ast = babylon.parse(buf, {
  sourceType: isModule ? 'module' : 'script',
});

const fixes = [];
const promiseVisitor = {
  CallExpression(path) {
    const callee = path.node.callee;
    if (callee.type != 'MemberExpression') {
      return;
    }
    if (callee.property.name != 'then' && callee.property.name != 'fail') {
      return;
    }
    const p = path.getStatementParent();
    if (p.node.type != 'ExpressionStatement') {
      throw new Error('Unexpected statement type: ' + p.node.type);
    }
    if (callee.property.name == 'fail') {
      p.hasFail = true;
    } else if (callee.property.name == 'then') {
      if (p.visited) {
        return;
      }
      p.visited = true;
      if (!p.hasFail) {
        fixes.push({
          start: path.node.end,
          end: path.node.end,
          contents: '.fail(' + (isModule ? 'UI' : 'omegaup.UI') + '.apiError)',
        });
        p.hasFail = true;
        valid = false;
      }
    }
  },
};
traverse(ast, promiseVisitor);

// From https://github.com/jquery/jquery/blob/305f193aa57014dc7d8fa0739a3fefd47166cd44/src/event/alias.js
const jQueryDeprecatedFunctions = [
  'blur',      'focus',      'focusin',  'focusout',   'resize',
  'scroll',    'click',      'dblclick', 'mousedown',  'mouseup',
  'mousemove', 'mouseover',  'mouseout', 'mouseenter', 'mouseleave',
  'change',    'select',     'submit',   'keydown',    'keypress',
  'keyup',     'contextmenu'
];
const jQueryVisitor = {
  CallExpression(path) {
    let callee = path.node.callee;
    if (callee.type != 'MemberExpression') {
      return;
    }
    const identifier = callee.property;
    if (jQueryDeprecatedFunctions.indexOf(identifier.name) == -1) {
      return;
    }
    if (path.node.arguments.length == 0) {
      // This is a trigger.
      fixes.push({
        start: identifier.start,
        end: path.node.end,
        contents: 'trigger(\'' + identifier.name + '\')',
      });
      return;
    }
    // This is an event handler.
    while (callee.object && callee.object.type == 'CallExpression') {
      callee = callee.object.callee;
      if (callee.type != 'Identifier') {
        continue;
      }
      if (callee.name != '$' && callee.name != 'jQuery') {
        continue;
      }
      fixes.push({
        start: identifier.start,
        end: identifier.end + 1,
        contents: 'on(\'' + identifier.name + '\', ',
      });
      return;
    }
  },
};
traverse(ast, jQueryVisitor);

fixes.sort(function(a, b) { return a.start - b.start; });

// Manually write the results instead of relying on babel-generator.
// babel-generator moves stuff around too much :/
var lastPos = 0;
const fd = fs.openSync(filename, 'w');
for (var i = 0; i < fixes.length; i++) {
  fs.writeSync(fd, buf.slice(lastPos, fixes[i].start));
  fs.writeSync(fd, fixes[i].contents);
  lastPos = fixes[i].end;
}
fs.writeSync(fd, buf.slice(lastPos));
fs.closeSync(fd);
