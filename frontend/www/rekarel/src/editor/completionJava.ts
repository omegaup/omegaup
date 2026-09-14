import {syntaxTree} from "@codemirror/language"
import {CompletionContext} from "@codemirror/autocomplete"
import { SyntaxNode } from "@lezer/common";

const conditions = [
  "frontIsClear", "leftIsClear", "rightIsClear", 
  "frontIsBlocked", "leftIsBlocked", "rightIsBlocked",
  "nextToABeeper", "notNextToABeeper", 
  "facingNorth", "facingSouth", "facingEast", "facingWest",
  "notFacingNorth", "notFacingSouth", "notFacingEast", "notFacingWest",
].map(tag => ({label: tag, type: "function"}))

const builtins = [
  "move()", "turnleft()", 
  "pickbeeper()", "putbeeper()",
  "return()", "turnoff()"
].map(tag => ({label: tag, type: "function"}))

const keywords = [
  "if", "iterate", "while"
].map(tag => ({label: tag, type: "keyword"}))

const statements = builtins.concat(keywords);

function safeStringify(obj) {
  const seen = new WeakSet();
  return JSON.stringify(obj, (key, value) => {
      if (typeof value === "object" && value !== null) {
          if (seen.has(value)) {
              return "[Circular]";
          }
          seen.add(value);
      }
      return value;
  });
}

function searchFirst(node: SyntaxNode, depth:number):string {
  
  if (depth <=0) return "none";
  console.log((node.name), depth);
  if (node.name ==="BooleanHeader") return "Boolean";
  if (node.name ==="InnerBlock") return "Block";
  if (node.name ==="Block") return "Block";

  if (!node.parent)
    return "none";
  return searchFirst(node.parent, depth-1);
}

export function completeKarelJava(context: CompletionContext) {
  let nodeBefore = syntaxTree(context.state).resolveInner(context.pos, -1)
  let word = context.matchBefore(/\w*/)
  if (word.from === word.to && !context.explicit) 
      return null;
  console.log(word);
  const target = searchFirst(nodeBefore, 2);
  if (target === "Boolean") {    
      return {
      from: word.from,
      options: conditions,
    }
  }
  if (target === "Block") {    
      return {
      from: word.from,
      options: statements,
    }
  }
  return null
}