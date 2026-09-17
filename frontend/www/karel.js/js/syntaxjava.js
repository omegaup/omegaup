CodeMirror.defineMode('kareljava', function () {
  function words(str) {
    var obj = {},
      words = str.split(' ');
    for (var i = 0; i < words.length; ++i) obj[words[i]] = true;
    return obj;
  }
  var keywords = words('class if else iterate while void define program');
  var indent = words('{');
  var dedent = words('}');
  var builtin = words('move turnleft turnoff putbeeper pickbeeper return');
  var operator = words('iszero pred succ');
  var atoms = words(
    'frontIsClear frontIsBlocked leftIsClear leftIsBlocked rightIsClear rightIsBlocked nextToABeeper notNextToABeeper anyBeepersInBeeperBag noBeepersInBeeperBag facingNorth facingSouth facingEast facingWest notFacingNorth notFacingSouth notFacingEast notFacingWest',
  );

  function tokenBase(stream, state) {
    var ch = stream.next();
    if (ch == '/' && stream.eat('/')) {
      state.tokenize = tokenSimpleComment;
      return tokenSimpleComment(stream, state);
    }
    if (ch == '/' && stream.eat('*')) {
      state.tokenize = tokenComment;
      return tokenComment(stream, state);
    }
    if (/[\(\);]/.test(ch)) {
      return null;
    }
    if (/[\!\&\|]/.test(ch)) {
      return 'operator';
    }
    if (/\d/.test(ch)) {
      stream.eatWhile(/[\w\.]/);
      return 'number';
    }
    stream.eatWhile(/[\w_]/);
    var cur = stream.current();
    var style = 'variable';
    if (keywords.propertyIsEnumerable(cur)) style = 'keyword';
    else if (builtin.propertyIsEnumerable(cur)) style = 'builtin';
    else if (operator.propertyIsEnumerable(cur)) style = 'operator';
    else if (atoms.propertyIsEnumerable(cur)) style = 'atom';
    else if (indent.propertyIsEnumerable(cur)) style = 'indent';
    else if (dedent.propertyIsEnumerable(cur)) style = 'dedent';
    else if (state.lastTok == 'void' || state.lastTok == 'define')
      style = 'def';
    state.lastTok = cur;
    return style;
  }

  function tokenSimpleComment(stream, state) {
    stream.skipToEnd();
    state.tokenize = null;
    return 'comment';
  }

  function tokenComment(stream, state) {
    var maybeEnd = false,
      ch;
    while ((ch = stream.next())) {
      if (ch == '/' && maybeEnd) {
        state.tokenize = null;
        break;
      }
      maybeEnd = ch == '*';
    }
    return 'comment';
  }

  // Interface

  return {
    startState: function () {
      return { tokenize: null, lastTok: null };
    },

    token: function (stream, state) {
      if (stream.eatSpace()) return null;
      return (state.tokenize || tokenBase)(stream, state);
    },
  };
});

CodeMirror.defineMIME('text/x-kareljava', 'kareljava');
