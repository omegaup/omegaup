import { KarelNumbers } from "@rekarel/core";

export const ERRORCODES = {
    WALL: 'Karel ha chocado con un muro!',
    WORLDUNDERFLOW: 'Karel intentó tomar zumbadores en una posición donde no había!',
    BAGUNDERFLOW: 'Karel intentó dejar un zumbador pero su mochila estaba vacía!',
    INSTRUCTION: 'Karel ha superado el límite de instrucciones!',
    STACK: 'La pila de karel se ha desbordado!',
};

interface executionLimits {
    maxInstructions: {
        general: number,
        left: number
        forward: number,
        pick: number,
        leave: number
    }
    stackSize: number
    callMaxParam: number
    stackMemory: number
}

function formatNumber(n:number):string {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

export function decodeRuntimeError(error: string, limits:executionLimits):string {
    if (error === "INSTRUCTION") {
        return `Karel ha superado el límite de ${formatNumber(limits.maxInstructions.general)} instrucciones!`;
    }
    if (error === "INSTRUCTION_LEFT") {
        return `Karel ha superado el límite de ${formatNumber(limits.maxInstructions.left)} gira izquierda (turnleft)!`;
    }
    if (error === "INSTRUCTION_FORWARD") {
        return `Karel ha superado el límite de ${formatNumber(limits.maxInstructions.forward)} avanza (move)!`;
    }
    if (error === "INSTRUCTION_PICKBUZZER") {
        return `Karel ha superado el límite de ${formatNumber(limits.maxInstructions.pick)} coge-zumbador (pickbeeper)!`;    
    }
    if (error === "INSTRUCTION_LEAVEBUZZER") {
        return `Karel ha superado el límite de ${formatNumber(limits.maxInstructions.leave)} deja-zumbador (putbeeper)!`;
    }
    if (error === "STACK") {
        return `La pila de karel se ha desbordado! El tamaño de la pila es de ${formatNumber(limits.stackSize)}`
    }
    if (error === "CALLMEMORY") {
        return `Límite de parámetros superados.`
        +`<br>Solo puedes llamar con a lo más ${formatNumber(limits.callMaxParam)}`;
    }
    if (error === "STACKMEMORY") {
        return `El límite de memoria del stack a sido superado.`
        +`<br>Limite de memoria: ${formatNumber(limits.stackMemory)}`
        +`<br>El costo de una función es igual al mayor entre uno y la cantidad de parámetros que usa.`;
    }
    if (error === "INTEGEROVERFLOW") {
        return `Se superó el límite superior numérico de ${formatNumber(KarelNumbers.maximum)}.`;
    }
    if (error === "INTEGERUNDERFLOW") {
        return `Se superó el límite inferior numérico de ${formatNumber(KarelNumbers.minimum)}.`;
    }
    if (error === "WORLDOVERFLOW") {
        return `Se superó el límite de zumbadores en una casilla, no debe haber más de ${formatNumber(KarelNumbers.maximum)} zumbadores.`;
    }
    if (error in ERRORCODES) {
        return ERRORCODES[error];
    } else {
        return `Karel tubo un error de ejecución desconocido: ${error}`
    }
}