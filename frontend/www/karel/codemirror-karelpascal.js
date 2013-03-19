CodeMirror.defineMode("karelpascal", function() {
  function words(str) {
    var obj = {}, words = str.split(" ");
    for (var i = 0; i < words.length; ++i) obj[words[i]] = true;
    return obj;
  }
  var keywords = words("iniciar-programa inicia-ejecucion termina-ejecucion finalizar-programa " +
  			"define-nueva-instruccion como entonces mientras hacer repetir veces " +
  			"sino si");
  
  var atoms = words(	"frente-libre frente-bloqueado izquierda-libre izquierda-bloqueada " +
  			"derecha-libre derecha-bloqueada junto-a-zumbador no-junto-a-zumbador " +
  			"algun-zumbador-en-la-mochila ningun-zumbador-en-la-mochila orientado-al-norte " +
  			"orientado-al-sur orientado-al-este orientado-al-oeste no-orientado-al-norte " +
  			"no-orientado-al-sur no-orientado-al-este no-orientado-al-oeste");
  
  var builtins = words("apagate gira-izquierda avanza coge-zumbador " +
  			"deja-zumbador");
  
  var brackets = words("inicio fin");
  			
  var operators = words("si-es-cero no o y precede sucede");

  function tokenBase(stream, state) {
    var ch = stream.next();
    if (ch == '"' || ch == "'") {
      state.tokenize = tokenString(ch);
      return state.tokenize(stream, state);
    }
    if (ch == "(" && stream.eat("*")) {
      state.tokenize = tokenComment;
      return tokenComment(stream, state);
    }
    if (ch == "{") {
      stream.eatWhile(/[^}]/);
      stream.eat("}");
      return "comment";
    }
    if (/\(\);]/.test(ch)) {
      return null;
    }
    if (/\d/.test(ch)) {
      stream.eatWhile(/\d/);
      return "number";
    }
    stream.eatWhile(/[\w-]/);
    var cur = stream.current();
    if (keywords.propertyIsEnumerable(cur)) return "keyword";
    if (atoms.propertyIsEnumerable(cur)) return "atom";
    if (brackets.propertyIsEnumerable(cur)) return "bracket";
    if (operators.propertyIsEnumerable(cur)) return "operator";
    if (builtins.propertyIsEnumerable(cur)) return "builtin";
    return "variable";
  }

  function tokenString(quote) {
    return function(stream, state) {
      var escaped = false, next, end = false;
      while ((next = stream.next()) != null) {
        if (next == quote && !escaped) {end = true; break;}
        escaped = !escaped && next == "\\";
      }
      if (end || !escaped) state.tokenize = null;
      return "string";
    };
  }

  function tokenComment(stream, state) {
    var maybeEnd = false, ch;
    while (ch = stream.next()) {
      if (ch == ")" && maybeEnd) {
        state.tokenize = null;
        break;
      }
      maybeEnd = (ch == "*");
    }
    return "comment";
  }

  // Interface
  return {
    startState: function() {
      return {tokenize: null};
    },

    token: function(stream, state) {
      if (stream.eatSpace()) return null;
      var style = (state.tokenize || tokenBase)(stream, state);
      if (style == "comment" || style == "meta") return style;
      return style;
    }
  };
});

CodeMirror.defineMIME("text/x-karelpascal", "karelpascal");
