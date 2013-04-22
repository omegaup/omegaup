/* Karel-ruby */

%lex
%%

\n\s*				{ return 'NEWLINE'; }
[ \t]+                          {/* ignore */}
"#"[^\n]*\n\s*			{/* ignore */}
"def"				{ return 'DEF'; }
"apagate"                       { return 'HALT'; }
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
"."				{ return 'DOT'; }
[0-9]+                          { return 'NUM'; }
[a-zA-Z][a-zA-Z0-9-]*           { return 'VAR'; }
<<EOF>>                         { return 'EOF'; }
/lex

%nonassoc XIF
%nonassoc ELSE

%%

program
  : def_list expr_list EOF
    %{
    	var program = $expr_list.concat([['LINE', yylineno], ['HALT']]);
    	var functions = {};
    	
    	for (var i = 0; i < $def_list.length; i++) {
    		if (functions[$def_list[i][0]]) {
    			throw "Function redefinition: " + $def_list[i][0];
    		}
    		
    		functions[$def_list[i][0]] = program.length;
    		program = program.concat($def_list[i][1]);
    	}
    	
    	for (var i = 0; i < program.length; i++) {
    		if (program[i][0] == 'CALL') {
    			if (!functions[program[i][1]]) {
    				throw "Unknown function: " + program[i][1];
    			}
    			
    			program[i].push(program[i][1]);
    			program[i][1] = functions[program[i][2]];
    		} else if (program[i][0] == 'PARAM' && program[i][1] != 0) {
			throw "Unknown variable: " + program[i][1];
    		}
    	}
    	
    	return program;
    %}
  | expr_list EOF
    { return $expr_list.concat([['HALT']]); }
  ;
  
def_list
  : def_list def NEWLINE
    { $$ = $def_list.concat($def); }
  | def NEWLINE
    { $$ = $def; }
  ;

def
  : DEF line var NEWLINE expr_list END
    { $$ = [[$var, $line.concat($expr_list).concat([['RET']])]]; }
  | DEF line var '(' var ')' NEWLINE expr_list END
    %{
    	var result = $line.concat($expr_list).concat([['RET']]);
    	for (var i = 0; i < result.length; i++) {
    		if (result[i][0] == 'PARAM') {
    			if (result[i][1] == $5) {
    				result[i][1] = 0;
    			} else {
				throw "Unknown variable: " + $5;
    			}
    		}
    	}
    	$$ = [[$var, result]];
    %}
  ;
  
expr_list
  : expr_list expr NEWLINE
    { $$ = $expr_list.concat($expr); }
  | expr NEWLINE
    { $$ = $expr; }
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
  : IF line term NEWLINE expr_list END
    { $$ = $term.concat($line).concat([['JZ', $expr_list.length]]).concat($expr_list); }
  | IF line term NEWLINE expr_list ELSE NEWLINE expr_list END
    { $$ = $term.concat($line).concat([['JZ', 1 + $5.length]]).concat($r).concat([['JMP', $8.length]]).concat($8); }
  ;

loop
  : WHILE line term NEWLINE expr_list END
    { $$ = $term.concat($line).concat([['JZ', 1 + $expr_list.length]]).concat($expr_list).concat([['JMP', -1 -($term.length + 1 + $expr_list.length + 1)]]); }
  ;
  
call
  : var
    { $$ = [['LINE', yylineno], ['LOAD', 0], ['CALL', $var], ['LINE', yylineno]]; }
  | var '(' integer ')'
    { $$ = [['LINE', yylineno]].concat($integer).concat([['CALL', $var], ['LINE', yylineno]]); }
  ;

repeat
  : var TIMES line NEWLINE expr_list END
    { $$ = $var.concat($line).concat([['DUP'], ['JLEZ', $expr_list.length + 2]]).concat($expr_list).concat([['DEC'], ['JMP', -1 -($expr_list.length + 4)], ['POP']]); }
  | non_var_integer TIMES line NEWLINE expr_list END
    { $$ = $non_var_integer.concat($line).concat([['DUP'], ['JLEZ', $expr_list.length + 2]]).concat($expr_list).concat([['DEC'], ['JMP', -1 -($expr_list.length + 4)], ['POP']]); }
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
    { $$ = [['BAGBUFFERS'], ['NOT']]; }
  | IFW
    { $$ = [['ORIENTATION'], ['LOAD', 0], ['EQ']]; }
  | IFN
    { $$ = [['ORIENTATION'], ['LOAD', 1], ['EQ']]; }
  | IFS
    { $$ = [['ORIENTATION'], ['LOAD', 2], ['EQ']]; }
  | IFE
    { $$ = [['ORIENTATION'], ['LOAD', 3], ['EQ']]; }
  | IFNW
    { $$ = [['ORIENTATION'], ['LOAD', 0], ['EQ'], ['NOT']]; }
  | IFNN
    { $$ = [['ORIENTATION'], ['LOAD', 1], ['EQ'], ['NOT']]; }
  | IFNS
    { $$ = [['ORIENTATION'], ['LOAD', 2], ['EQ'], ['NOT']]; }
  | IFNE
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
