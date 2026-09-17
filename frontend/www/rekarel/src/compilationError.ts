import { CompilationError } from "@rekarel/core";


function jumpable(line:number, column:number | null) {
    let c = column;
    if (column == null) {
        c=0;
    }
    const onclick = `karel.MoveEditorCursorToLine(${line}, ${c})`
    return `<a class="text-decoration-underline" href="#" title="Haz clic para ir al error" onclick="${onclick}">línea ${line}</a>`;
}

const ERROR_TOKENS = {
    pascal: {
      BEGINPROG: '"iniciar-programa"',
      BEGINEXEC: '"inicia-ejecución"',
      ENDEXEC: '"termina-ejecución"',
      ENDPROG: '"finalizar-programa"',
      DEF: '"define-nueva-instrucción"',
      PROTO: '"define-prototipo-instrucción"',
      RET: '"sal-de-instrucción"',
      AS: '"como"',
      HALT: '"apágate"',
      LEFT: '"gira-izquierda"',
      FORWARD: '"avanza"',
      PICKBUZZER: '"coge-zumbador"',
      LEAVEBUZZER: '"deja-zumbador"',
      BEGIN: '"inicio"',
      END: '"fin"',
      THEN: '"entonces"',
      WHILE: '"mientras"',
      DO: '"hacer"',
      REPEAT: '"repetir"',
      TIMES: '"veces"',
      DEC: '"precede"',
      INC: '"sucede"',
      IFZ: '"si-es-cero"',
      IFNFWALL: '"frente-libre"',
      IFFWALL: '"frente-bloqueado"',
      IFNLWALL: '"izquierda-libre"',
      IFLWALL: '"izquierda-bloqueada"',
      IFNRWALL: '"derecha-libre"',
      IFRWALL: '"derecha-bloqueada"',
      IFWBUZZER: '"junto-a-zumbador"',
      IFNWBUZZER: '"no-junto-a-zumbador"',
      IFBBUZZER: '"algún-zumbador-en-la-mochila"',
      IFNBBUZZER: '"ningún-zumbador-en-la-mochila"',
      IFN: '"orientado-al-norte"',
      IFS: '"orientado-al-sur"',
      IFE: '"orientado-al-este"',
      IFW: '"orientado-al-oeste"',
      IFNN: '"no-orientado-al-norte"',
      IFNS: '"no-orientado-al-sur"',
      IFNE: '"no-orientado-al-este"',
      IFNW: '"no-orientado-al-oeste"',
      ELSE: '"si-no"',
      IF: '"si"',
      NOT: '"no"',
      OR: '"o"',
      AND: '"y"',
      '(': '"("',
      ')': '")"',
      ';': '";"',
      NUM: 'un número',
      VAR: 'un nombre',
      EOF: 'el final del programa',
    },
    java: {
      CLASS: '"class"',
      PROG: '"program"',
      DEF: '"define"',
      RET: '"return"',
      HALT: '"turnoff"',
      LEFT: '"turnleft"',
      FORWARD: '"move"',
      PICKBUZZER: '"pickbeeper"',
      LEAVEBUZZER: '"putbeeper"',
      WHILE: '"while"',
      REPEAT: '"iterate"',
      DEC: '"pred"',
      INC: '"succ"',
      IFZ: '"iszero"',
      IFNFWALL: '"frontIsClear"',
      IFFWALL: '"frontIsBlocked"',
      IFNLWALL: '"leftIsClear"',
      IFLWALL: '"leftIsBlocked"',
      IFNRWALL: '"rightIsClear"',
      IFRWALL: '"rightIsBlocked"',
      IFWBUZZER: '"nextToABeeper"',
      IFNWBUZZER: '"notNextToABeeper"',
      IFBBUZZER: '"anyBeepersInBeeperBag"',
      IFNBBUZZER: '"noBeepersInBeeperBag"',
      IFN: '"facingNorth"',
      IFS: '"facingSouth"',
      IFE: '"facingEast"',
      IFW: '"facingWest"',
      IFNN: '"notFacingNorth"',
      IFNS: '"notFacingSouth"',
      IFNE: '"notFacingEast"',
      IFNW: '"notFacingWest"',
      ELSE: '"else"',
      IF: '"if"',
      NOT: '"!"',
      OR: '"||"',
      AND: '"&&"',
      '(': '"("',
      ')': '")"',
      BEGIN: '"{"',
      END: '"}"',
      ';': '";"',
      NUM: 'un número',
      VAR: 'un nombre',
      EOF: 'el final del programa',
    },
  };

  const operatorsJava : Record<string, string> = {
    "AND" : "&#38;&#38;",
    "OR" : "||",
    "LT" : "<",
    "LTE" : "<=",
    "NOT" : "!",
  }

  const operatorsPascal : Record<string, string>  = {
    "AND" : "y",
    "OR" : "o",
    "LT" : "<",
    "LTE" : "<=",
    "NOT" : "no",
  }

function decodeKnownError(status:CompilationError.ErrorStatus, lan : "java"|"pascal"): string {
    if (status.error === CompilationError.Errors.BINARY_OPERATOR_TYPE_ERROR) {
        const direction = status.direction === "LEFT" ? "izquierdo":"derecho";
        const operator = lan === "java" ? operatorsJava[status.operator] : operatorsPascal[status.operator];
        return `Para el operador <b>${operator}</b> no puede utilizar un termino ${direction} de tipo ${status.actualType}, necesita ${status.expectedType}.`;        
    }
    if (status.error === CompilationError.Errors.CALL_TYPE) {
        return `Se llamó a <b>${status.funcName}</b> en un lugar donde se esperaba un tipo ${status.expectedCallType}, pero la función es de tipo ${status.functionType}.`;
    }
    if (status.error === CompilationError.Errors.COMPARISON_TYPE) {
        return `Se intento comparar un tipo <b>${status.leftType}</b> contra un tipo <b>${status.rightType}</b> . Solo se pueden comparar los mismos tipos`;
    }
    if (status.error === CompilationError.Errors.FUNCTION_ILLEGAL_NAME) {
        return `La función no se puede llamar como una variable global: ${status.functionName}`;
    }
    if (status.error === CompilationError.Errors.FUNCTION_REDEFINITION) {
        return `La función <b>${status.functionName}</b> ya fue definida previamente`;
    }
    if (status.error === CompilationError.Errors.ILLEGAL_BREAK) {
        const breakName = lan === "java"? "break": "rompe";
        return `No se puede usar la instrucción <b>${breakName}</b> en este lugar`;
    }
    if (status.error === CompilationError.Errors.ILLEGAL_CONTINUE) {
        const continueName = lan === "java"? "continue": "continua";
        return `No se puede usar la instrucción <b>${continueName}</b> en este lugar`;
    }
    if (status.error === CompilationError.Errors.NO_EXPLICIT_RETURN) {
        return `La función ${status.functionName} es de tipo ${status.returnType}, pero no siempre retorna un valor`;
    }
    if (status.error === CompilationError.Errors.NUMBER_TOO_LARGE) {
        return `El número excede el límite de 999,999,999`;
    }
    if (status.error === CompilationError.Errors.PARAMETER_ILLEGAL_NAME) {
        return `No se puede llamar a un parámetro <b>${status.parameterName}</b> porque ya esta siendo usado por otra función o variable global`;
    }
    if (status.error === CompilationError.Errors.PARAMETER_REDEFINITION) {
        return `El parámetro <b>${status.parameterName}</b> ya fue definido`;      
    }    
    if (status.error === CompilationError.Errors.PROTOTYPE_PARAMETERS_MISS_MATCH) {
        return `El prototipo de <b>${status.functionName}</b> no concuerda con su definición.`
        + `<br> El prototipo tiene ${status.prototypeParamCount} parámetros, pero su definición tiene ${status.functionParamCount}`;
    }
    if (status.error === CompilationError.Errors.PROTOTYPE_REDEFINITION) {
        return `El prototipo <b>${status.prototypeName}</b> ya fue definido previamente`;
    }
    if (status.error === CompilationError.Errors.PROTOTYPE_TYPE_MISS_MATCH) {
        return `El prototipo de <b>${status.functionName}</b> no concuerda con su definición.`
        + `<br> El prototipo es de tipo ${status.prototypeType}, pero su definición es ${status.functionType}`;
    }
    if (status.error === CompilationError.Errors.RETURN_TYPE) {
        return `No se puede retornar un tipo  <b>${status.actualType}</b>, porque la función es de tipo ${status.expectedType}`;
    }
    if (status.error === CompilationError.Errors.TOO_FEW_PARAMS_IN_CALL) {
        return `Se llamó a <b>${status.funcName}</b> con menos parámetros de los necesarios.`
        + `<br> La llamada tiene ${status.actualParams}, pero su definición necesita ${status.expectedParams}`;
    }
    if (status.error === CompilationError.Errors.TOO_MANY_PARAMS_IN_CALL) {
        return `Se llamó a <b>${status.functionName}</b> con menos parámetros de los necesarios.`
        + `<br> La llamada tiene ${status.actualParams}, pero su definición necesita ${status.expectedParams}`;
    }
    if (status.error === CompilationError.Errors.TYPE_ERROR) {
        return `No se puede utilizar el tipo ${status.actualType} donde se requiere un tipo ${status.expectedType}`;
    } 
    if (status.error === CompilationError.Errors.UNARY_OPERATOR_TYPE_ERROR) {
        const operator = lan === "java" ? operatorsJava[status.operator] : operatorsPascal[status.operator];
        return `Para el operador <b>${operator}</b> no puede utilizar un termino de tipo ${status.actualType}, necesita ${status.expectedType}.`;
    }
    if (status.error === CompilationError.Errors.UNDEFINED_FUNCTION) {
        return `La función <b>${status.functionName}</b> no está definida`;
    }    
    if (status.error === CompilationError.Errors.UNKNOWN_MODULE) {
        return `No se reconoce el módulo <b>${status.module}</b> en la importación de ${status.full}`;
    }    
    if (status.error === CompilationError.Errors.UNKNOWN_PACKAGE) {
        return `No se reconoce el paquete <b>${status.package}</b> en la importación de ${status.full}`;
    }
    if (status.error === CompilationError.Errors.UNKNOWN_VARIABLE) {
        return `El parámetro o variable <b>${status.variable}</b> no está definido`;
    }    
    if (status.error === CompilationError.Errors.VOID_COMPARISON) {
        return `No se pueden comparar dos expresiones de tipo VOID.`;
    }
    if (status.error === CompilationError.Errors.UNKNOWN_SYNTAX) {
        return "Error de compilación, no se puede reconocer el lenguaje";
    }
    return `Error de compilación desconocido:  ${JSON.stringify(status)}`;

}

export function decodeError(e, lan : "java"|"pascal"|"ruby"|"none") : string {
    if (lan === "ruby" || lan === "none") {
        return "Error de compilación, no se puede reconocer el lenguaje";
    }
    let status = e.hash;
    if (status == null) {
        console.log(JSON.stringify(e))
        console.log(e)
        return "Error de compilación";
    }
    const errorString = `${e}`;
    let message = `Error de compilación en  la ${jumpable(status.line+1,status.loc?.first_column )}\n<br>\n<div class="card"><div class="card-body">`
    if (status.expected) {        
        let expectations = status.expected.map((x=>ERROR_TOKENS[lan][x.replace(/^'+/,"").replace(/'+$/,"") ]))        
        message += `Se encontró "${status.text}" cuando se esperaba ${ expectations.join(", ")}`
    } else if (status.error != null) {
        message += decodeKnownError(status, lan);
    } else if (errorString.includes("Unrecognized text")) {
        message += "Se encontró un token ilegal";
    } else {        
        message += "Error desconocido"
        
        console.log(JSON.stringify(e))
        console.log(e)
    }
    message+="</div></div>"
    return message;
}