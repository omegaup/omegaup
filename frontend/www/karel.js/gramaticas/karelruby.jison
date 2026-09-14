/* Karel-ruby */

%lex
%%

[\n]				{ return 'NEWLINE'; }
\s+                             {/* ignore */}
\#[^\n]*			{/* ignore */}
"def"				{ return 'DEF'; }
"apagate"                       { return 'HALT'; }
"sal-de-instruccion"		{ return 'RET'; }
"gira-izquierda"                { return 'LEFT'; }
"avanza"                        { return 'FORWARD'; }
"coge-zumbador"	                { return 'PICKBUZZER'; }
"deja-zumbador"	                { return 'LEAVEBUZZER'; }
"fin"                           { return 'END'; }
"veces"                         { return 'TIMES'; }
"mientras"                      { return 'WHILE'; }
"precede"                       { return 'DEC'; }
"sucede"                        { return 'INC'; }
"si-es-cero"                    { return 'IFZ'; }
"frente-libre"                  { return 'IFNFWALL'; }
"frente-bloqueado"              { return 'IFFWALL'; }
"izquierda-libre"               { return 'IFNLWALL'; }
"izquierda-bloqueada"           { return 'IFLWALL'; }
"derecha-libre"                 { return 'IFNRWALL'; }
"derecha-bloqueada"             { return 'IFRWALL'; }
"junto-a-zumbador"              { return 'IFWBUZZER'; }
"no-junto-a-zumbador"           { return 'IFNWBUZZER'; }
"algun-zumbador-en-la-mochila"  { return 'IFBBUZZER'; }
"ningun-zumbador-en-la-mochila" { return 'IFNBBUZZER'; }
"orientado-al-norte"            { return 'IFN'; }
"orientado-al-sur"              { return 'IFS'; }
"orientado-al-este"             { return 'IFE'; }
"orientado-al-oeste"            { return 'IFW'; }
"no-orientado-al-norte"         { return 'IFNN'; }
"no-orientado-al-sur"           { return 'IFNS'; }
"no-orientado-al-este"          { return 'IFNE'; }
"no-orientado-al-oeste"         { return 'IFNW'; }
"sino"                          { return 'ELSE'; }
"si"                            { return 'IF'; }
"no"                            { return 'NOT'; }
"o"                             { return 'OR'; }
"u"                             { return 'OR'; }
"y"                             { return 'AND'; }
"("                             { return '('; }
")"                             { return ')'; }
[0-9]+                          { return 'NUM'; }
[a-zA-Z][a-zA-Z0-9-]*           { return 'VAR'; }
<<EOF>>                         { return 'EOF'; }
/lex

%nonassoc XIF
%nonassoc ELSE

%{
function validate(function_list, program, yy) {
	var functions = {};
	var prototypes = {};
	
	for (var i = 0; i < function_list.length; i++) {
		if (functions[function_list[i][0]]) {
			yy.parser.parseError("Function redefinition: " + function_list[i][0], {
				text: function_list[i][0],
				line: function_list[i][3]
			});
		}
		
		functions[function_list[i][0]] = program.length;
		prototypes[function_list[i][0]] = function_list[i][2];
		program = program.concat(function_list[i][1]);
	}

	var current_line = 0;
	for (var i = 0; i < program.length; i++) {
		if (program[i][0] == 'LINE') {
			current_line = program[i][1];
		} else if (program[i][0] == 'CALL') {
			if (!functions[program[i][1]] || !prototypes[program[i][1]]) {
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
				line: current_line + 1
			});
		}
	}
	
	return program;
}
%}

%%

program
  : opt_newlines def_expr_list opt_newlines EOF
    { return validate($def_expr_list[0], $def_expr_list[1].concat([['LINE', yylineno], ['HALT']]), yy); }
  ;
  
def_expr_list
  : def_expr_list newlines def
    { $$ = [$def_expr_list[0].concat($def), $def_expr_list[1]]; }
  | def_expr_list newlines expr
    { $$ = [$def_expr_list[0], $def_expr_list[1].concat($expr)]; }
  | def
    { $$ = [$def, []]; }
  | expr
    { $$ = [[], $expr]; }
  ;

expr_list
  : expr_list expr newlines
    { $$ = $expr_list.concat($expr); }
  | expr newlines
    { $$ = $expr; }
  ;
 
opt_newlines
  : newlines
  |
  ;

newlines
  : newlines NEWLINE
  | NEWLINE
  ;

def
  : DEF line var newlines expr_list END
    { $$ = [[$var, $line.concat($expr_list).concat([['RET']])], 1]; }
  | DEF line var '(' var ')' newlines expr_list END
    %{
    	var result = $line.concat($expr_list).concat([['RET']]);	
    	for (var i = 0; i < result.length; i++) {
    		if (result[i][0] == 'PARAM') {
    			if (result[i][1] == $5) {
    				result[i][1] = 0;
    			} else {
						yy.parser.parseError("Unknown variable: " + $5, {
							text: $5,
							line: yylineno
						});
    			}
    		}
    	}
    	$$ = [[$var, result, 2]];
    %}
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
  | repeat
    { $$ = $repeat; }
  | cond
    { $$ = $cond; }
  | loop
    { $$ = $loop; }
  ;

cond
  : IF line term newlines expr_list END
    { $$ = $term.concat($line).concat([['JZ', $expr_list.length]]).concat($expr_list); }
  | IF line term newlines expr_list ELSE newlines expr_list END
    { $$ = $term.concat($line).concat([['JZ', 1 + $5.length]]).concat($5).concat([['JMP', $8.length]]).concat($8); }
  ;

loop
  : WHILE line term newlines expr_list END
    { $$ = $term.concat($line).concat([['JZ', 1 + $expr_list.length]]).concat($expr_list).concat([['JMP', -1 -($term.length + 1 + $expr_list.length + 1)]]); }
  ;
  
call
  : var
    { $$ = [['LINE', yylineno], ['LOAD', 0], ['CALL', $var, 1], ['LINE', yylineno]]; }
  | var '(' integer ')'
    { $$ = [['LINE', yylineno]].concat($integer).concat([['CALL', $var, 2], ['LINE', yylineno]]); }
  ;

repeat
  : var TIMES line newlines expr_list END
    { $$ = [['PARAM', $var]].concat($line).concat([['DUP'], ['LOAD', 0], ['EQ'], ['NOT'], ['JZ', $expr_list.length + 2]]).concat($expr_list).concat([['DEC'], ['JMP', -1 -($expr_list.length + 7)], ['POP']]); }
  | non_var_integer TIMES line newlines expr_list END
    { $$ = $non_var_integer.concat($line).concat([['DUP'], ['LOAD', 0], ['EQ'], ['NOT'], ['JZ', $expr_list.length + 2]]).concat($expr_list).concat([['DEC'], ['JMP', -1 -($expr_list.length + 7)], ['POP']]); }
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
    { $$ = [['PARAM', $var]]; }
  | non_var_integer
    { $$ = $non_var_integer; }
  ;

non_var_integer
  : NUM
    { $$ = [['LOAD', parseInt(yytext)]]; }
  | INC '(' integer ')'
    { $$ = $integer.concat([['INC']]); }
  | DEC	'(' integer ')'
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
