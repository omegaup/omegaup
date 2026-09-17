/* Karel-pascal */

%lex
%options case-insensitive
%options flex
%%

\s+                                         {/* ignore */}
\{[^}]*\}                                   {/* ignore */}
\(\*(?:[^*]|\*(?!\)))*\*\)                  {/* ignore */}
"iniciar-programa"                          { return 'BEGINPROG'; }
"inicia-ejecucion"                          { return 'BEGINEXEC'; }
"inicia-ejecución"                          { return 'BEGINEXEC'; }
"termina-ejecucion"                         { return 'ENDEXEC'; }
"termina-ejecución"                         { return 'ENDEXEC'; }
"finalizar-programa"                        { return 'ENDPROG'; }
"define-nueva-instruccion"                  { return 'DEF'; }
"define-nueva-instrucción"                  { return 'DEF'; }
"define-prototipo-instruccion"              { return 'PROTO'; }
"define-prototipo-instrucción"              { return 'PROTO'; }
"sal-de-instruccion"                        { return 'RET'; }
"sal-de-instrucción"                        { return 'RET'; }
"como"                                      { return 'AS'; }
"apagate"                                   { return 'HALT'; }
"apágate"                                   { return 'HALT'; }
"gira-izquierda"                            { return 'LEFT'; }
"avanza"                                    { return 'FORWARD'; }
"coge-zumbador"                             { return 'PICKBUZZER'; }
"deja-zumbador"                             { return 'LEAVEBUZZER'; }
"inicio"                                    { return 'BEGIN'; }
"fin"                                       { return 'END'; }
"entonces"                                  { return 'THEN'; }
"mientras"                                  { return 'WHILE'; }
"hacer"                                     { return 'DO'; }
"repetir"                                   { return 'REPEAT'; }
"veces"                                     { return 'TIMES'; }
"precede"                                   { return 'DEC'; }
"sucede"                                    { return 'INC'; }
"si-es-cero"                                { return 'IFZ'; }
"frente-libre"                              { return 'IFNFWALL'; }
"frente-bloqueado"                          { return 'IFFWALL'; }
"izquierda-libre"                           { return 'IFNLWALL'; }
"izquierda-bloqueada"                       { return 'IFLWALL'; }
"derecha-libre"                             { return 'IFNRWALL'; }
"derecha-bloqueada"                         { return 'IFRWALL'; }
"junto-a-zumbador"                          { return 'IFWBUZZER'; }
"no-junto-a-zumbador"                       { return 'IFNWBUZZER'; }
"algun-zumbador-en-la-mochila"              { return 'IFBBUZZER'; }
"algún-zumbador-en-la-mochila"              { return 'IFBBUZZER'; }
"ningun-zumbador-en-la-mochila"             { return 'IFNBBUZZER'; }
"ningún-zumbador-en-la-mochila"             { return 'IFNBBUZZER'; }
"orientado-al-norte"                        { return 'IFN'; }
"orientado-al-sur"                          { return 'IFS'; }
"orientado-al-este"                         { return 'IFE'; }
"orientado-al-oeste"                        { return 'IFW'; }
"no-orientado-al-norte"                     { return 'IFNN'; }
"no-orientado-al-sur"                       { return 'IFNS'; }
"no-orientado-al-este"                      { return 'IFNE'; }
"no-orientado-al-oeste"                     { return 'IFNW'; }
"sino"                                      { return 'ELSE'; }
"si-no"                                     { return 'ELSE'; }
"si"                                        { return 'IF'; }
"no"                                        { return 'NOT'; }
"o"                                         { return 'OR'; }
"u"                                         { return 'OR'; }
"y"                                         { return 'AND'; }
"e"                                         { return 'AND'; }
"("                                         { return '('; }
")"                                         { return ')'; }
";"                                         { return ';'; }
[0-9]+                                      { return 'NUM'; }
[A-Za-zÀ-ÖØ-öø-ÿ_][A-Za-zÀ-ÖØ-öø-ÿ0-9_-]*   { return 'VAR'; }
<<EOF>>                                     { return 'EOF'; }
/lex

%nonassoc XIF
%nonassoc ELSE

%{
function validate(function_list, program, yy) {
	var prototypes = {};
	var functions = {};

	for (var i = 0; i < function_list.length; i++) {
		if (function_list[i][1] == null) {
			if (prototypes[function_list[i][0]] || functions[function_list[i][0]]) {
				yy.parser.parseError("Prototype redefinition: " + function_list[i][0], {
					text: function_list[i][0],
					line: function_list[i][3]
				});
			}
			prototypes[function_list[i][0]] = function_list[i][2];
		} else {
			if (functions[function_list[i][0]]) {
				yy.parser.parseError("Function redefinition: " + function_list[i][0], {
					text: function_list[i][0],
					line: function_list[i][3]
				});
			} else if (prototypes[function_list[i][0]]) {
				if (prototypes[function_list[i][0]] != function_list[i][2]) {
					yy.parser.parseError("Prototype parameter mismatch: " + function_list[i][0], {
						text: function_list[i][0],
						line: function_list[i][3]
					});
				}
			}

			prototypes[function_list[i][0]] = function_list[i][2];
			functions[function_list[i][0]] = program.length;
			var current_line = 1;

			// This is only to make sure that any function that is called has been
			// either declared or defined previously. Other validations will be done
			// in the overall program loop below.
			for (var j = 0; j < function_list[i][1].length; j++) {
				if (function_list[i][1][j][0] == 'LINE') {
					current_line = function_list[i][1][j][1];
				} else if (function_list[i][1][j][0] == 'CALL' &&
						!functions[function_list[i][1][j][1]] &&
						!prototypes[function_list[i][1][j][1]]) {
					yy.parser.parseError("Undefined function: " + function_list[i][1][j][1], {
						text: function_list[i][1][j][1],
						line: current_line
					});
				}
			}

			program = program.concat(function_list[i][1]);
		}
	}

	var current_line = 1;
	for (var i = 0; i < program.length; i++) {
		if (program[i][0] == 'LINE') {
			current_line = program[i][1];
		} else if (program[i][0] == 'CALL') {
			if (!functions[program[i][1]]) {
				yy.parser.parseError("Undefined function: " + program[i][1], {
					text: program[i][1],
					line: current_line
				});
			} else if (prototypes[program[i][1]] != program[i][2]) {
				yy.parser.parseError("Function parameter mismatch: " + program[i][1], {
					text: program[i][1],
					line: current_line
				});
			}
			program[i][2] = program[i][1];
			program[i][1] = functions[program[i][1]];
		} else if (program[i][0] == 'PARAM' && program[i][1] != 0) {
			yy.parser.parseError("Unknown variable: " + program[i][1], {
				text: program[i][1],
				line: current_line
			});
		}
	}

	return program;
}
%}

%%

program
  : BEGINPROG def_list BEGINEXEC expr_list ENDEXEC ENDPROG EOF
    { return validate($def_list, $expr_list.concat([['LINE', yylineno], ['HALT']]), yy); }
  | BEGINPROG BEGINEXEC expr_list ENDEXEC ENDPROG EOF
    { return validate([], $expr_list.concat([['LINE', yylineno], ['HALT']]), yy); }
  ;

def_list
  : def_list def ';'
    { $$ = $def_list.concat($def); }
  | def ';'
    { $$ = $def; }
  ;

def
  : PROTO line var
    { $$ = [[$var.toLowerCase(), null, 1, $line[0][1]]]; }
  | PROTO line var '(' var ')'
    { $$ = [[$var.toLowerCase(), null, 2, $line[0][1]]]; }
  | DEF line var AS expr
    { $$ = [[$var.toLowerCase(), $line.concat($expr).concat([['RET']]), 1, $line[0][1]]]; }
  | DEF line var '(' var ')' AS expr
    %{
    	var result = $line.concat($expr).concat([['RET']]);
      var current_line = $line[0][1];
    	for (var i = 0; i < result.length; i++) {
        if (result[i][0] == 'LINE') {
          current_line = result[i][1];
        } else if (result[i][0] == 'PARAM') {
    			if (result[i][1] == $5.toLowerCase()) {
    				result[i][1] = 0;
    			} else {
    				yy.parser.parseError("Unknown variable: " + $5, {
              text: $5,
              line: current_line + 1
            });
    			}
    		}
    	}
    	$$ = [[$var.toLowerCase(), result, 2, $line[0][1]]];
    %}
  ;


expr_list
  : expr_list ';' genexpr
    { $$ = $expr_list.concat($genexpr); }
  | genexpr
    { $$ = $genexpr; }
  ;

genexpr
  : expr
    { $$ = $expr; }
  |
    { $$ = []; }
  ;

expr
  : FORWARD
    { $$ = [['LINE', yylineno], ['WORLDWALLS'], ['ORIENTATION'], ['MASK'], ['AND'], ['NOT'], ['EZ', 'WALL'], ['FORWARD']]; }
  | LEFT
    { $$ = [['LINE', yylineno], ['LEFT']]; }
  | PICKBUZZER
    { $$ = [['LINE', yylineno], ['WORLDBUZZERS'], ['EZ', 'WORLDUNDERFLOW'], ['PICKBUZZER']]; }
  | LEAVEBUZZER
    { $$ = [['LINE', yylineno], ['BAGBUZZERS'], ['EZ', 'BAGUNDERFLOW'], ['LEAVEBUZZER']]; }
  | HALT
    { $$ = [['LINE', yylineno], ['HALT']]; }
  | RET
    { $$ = [['LINE', yylineno], ['RET']]; }
  | call
    { $$ = $call; }
  | cond
    { $$ = $cond; }
  | loop
    { $$ = $loop; }
  | repeat
    { $$ = $repeat; }
  | BEGIN expr_list END
    { $$ = $expr_list; }
  ;

call
  : var
    { $$ = [['LINE', yylineno], ['LOAD', 0], ['CALL', $var.toLowerCase(), 1], ['LINE', yylineno]]; }
  | var '(' integer ')'
    { $$ = [['LINE', yylineno]].concat($integer).concat([['CALL', $var.toLowerCase(), 2], ['LINE', yylineno]]); }
  ;

cond
  : IF line term THEN expr %prec XIF
    { $$ = $line.concat($term).concat([['JZ', $expr.length]]).concat($expr); }
  | IF line term THEN expr ELSE expr
    { $$ = $line.concat($term).concat([['JZ', 1 + $5.length]]).concat($5).concat([['JMP', $7.length]]).concat($7); }
  ;

loop
  : WHILE line term DO expr
    { $$ = $line.concat($term).concat([['JZ', 1 + $expr.length]]).concat($expr).concat([['JMP', -1 -($term.length + $expr.length + 1)]]); }
  ;

repeat
  : REPEAT line integer TIMES expr
    { $$ = $line.concat($integer).concat([['DUP'], ['LOAD', 0], ['EQ'], ['NOT'], ['JZ', $expr.length + 2]]).concat($expr).concat([['DEC'], ['JMP', -1 -($expr.length + 6)], ['POP']]); }
  ;

term
  : term OR and_term
    { $$ = $term.concat($and_term).concat([['OR']]); }
  | and_term
    { $$ = $and_term; }
  ;

and_term
  : and_term AND not_term
    { $$ = $and_term.concat($not_term).concat([['AND']]); }
  | not_term
    { $$ = $not_term; }
  ;

not_term
  : NOT clause
    { $$ = $clause.concat([['NOT']]); }
  | clause
    { $$ = $clause; }
  ;

clause
  : IFZ '(' integer ')'
    { $$ = $integer.concat([['NOT']]); }
  | bool_fun
    { $$ = $bool_fun; }
  | '(' term ')'
    { $$ = $term; }
  ;

bool_fun
  : IFNFWALL
    { $$ = [['WORLDWALLS'], ['ORIENTATION'], ['MASK'], ['AND'], ['NOT']]; }
  | IFFWALL
    { $$ = [['WORLDWALLS'], ['ORIENTATION'], ['MASK'], ['AND']]; }
  | IFNLWALL
    { $$ = [['WORLDWALLS'], ['ORIENTATION'], ['ROTL'], ['MASK'], ['AND'], ['NOT']]; }
  | IFLWALL
    { $$ = [['WORLDWALLS'], ['ORIENTATION'], ['ROTL'], ['MASK'], ['AND']]; }
  | IFNRWALL
    { $$ = [['WORLDWALLS'], ['ORIENTATION'], ['ROTR'], ['MASK'], ['AND'], ['NOT']]; }
  | IFRWALL
    { $$ = [['WORLDWALLS'], ['ORIENTATION'], ['ROTR'], ['MASK'], ['AND']]; }
  | IFWBUZZER
    { $$ = [['WORLDBUZZERS'], ['LOAD', 0], ['EQ'], ['NOT']]; }
  | IFNWBUZZER
    { $$ = [['WORLDBUZZERS'], ['NOT']]; }
  | IFBBUZZER
    { $$ = [['BAGBUZZERS'], ['LOAD', 0], ['EQ'], ['NOT']]; }
  | IFNBBUZZER
    { $$ = [['BAGBUZZERS'], ['NOT']]; }
  | IFW
    { $$ = [['ORIENTATION'], ['LOAD', 0], ['EQ']]; }
  | IFN
    { $$ = [['ORIENTATION'], ['LOAD', 1], ['EQ']]; }
  | IFE
    { $$ = [['ORIENTATION'], ['LOAD', 2], ['EQ']]; }
  | IFS
    { $$ = [['ORIENTATION'], ['LOAD', 3], ['EQ']]; }
  | IFNW
    { $$ = [['ORIENTATION'], ['LOAD', 0], ['EQ'], ['NOT']]; }
  | IFNN
    { $$ = [['ORIENTATION'], ['LOAD', 1], ['EQ'], ['NOT']]; }
  | IFNE
    { $$ = [['ORIENTATION'], ['LOAD', 2], ['EQ'], ['NOT']]; }
  | IFNS
    { $$ = [['ORIENTATION'], ['LOAD', 3], ['EQ'], ['NOT']]; }
  ;

integer
  : var
    { $$ = [['PARAM', $var.toLowerCase()]]; }
  | NUM
    { $$ = [['LOAD', parseInt(yytext)]]; }
  | INC '(' integer ')'
    { $$ = $integer.concat([['INC']]); }
  | DEC	 '(' integer ')'
    { $$ = $integer.concat([['DEC']]); }
  ;

var
  : VAR
    { $$ = yytext; }
  ;

line
  :
    { $$ = [['LINE', yylineno]]; }
  ;
