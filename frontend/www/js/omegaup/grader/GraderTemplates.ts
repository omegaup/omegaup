export const defaultValidatorSource = `#!/usr/bin/python3
# -*- coding: utf-8 -*-

import logging
import sys

def _main() -> None:
  # lee "data.in" para obtener la entrada original.
  with open('data.in', 'r') as f:
    a, b = [int(x) for x in f.read().strip().split()]
  # lee "data.out" para obtener la salida esperada.
  with open('data.out', 'r') as f:
    suma = int(f.read().strip())

  score = 0
  try:
    # Lee la salida del concursante
    suma_concursante = int(input().strip())

    # Determina si la salida es correcta
    if suma_concursante != suma:
      # Cualquier cosa que imprimas a sys.stderr se ignora, pero es útil
      # para depurar con debug-rejudge.
      logging.error('Salida incorrecta')
      return
    score = 1
  except:
    log.exception('Error leyendo la salida del concursante')
  finally:
    print(score)

if __name__ == '__main__':
  _main()`;

export const defaultInteractiveIdlSource = `interface Main {
};

interface sumas {
    long sumas(long a, long b);
};`;

export const defaultInteractiveMainSource = `#include <iostream>

#include "sumas.h"

int main(int argc, char* argv[]) {
    long long a, b;
    std::cin >> a >> b;
    std::cout << sumas(a, b) << '\\n';
}`;

export const sourceTemplates: Record<string, string> = {
  c: `#include <stdio.h>
#include <stdint.h>

int main() {
  int t;
  scanf("%d", &t);
  while (t--) {

  }

  return 0;
}`,
  cpp: `#include <iostream>

using namespace std;

int main() {
  cin.tie(nullptr);
  ios_base::sync_with_stdio(false);

  int t;
  cin >> t;
  while (t--) {

  }

  return 0;
}`,
  java: `import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;
import java.util.StringTokenizer;

public class Main {
  public static void main(String[] args) throws IOException {
    BufferedReader br = new BufferedReader(
                          new InputStreamReader(System.in));
    StringTokenizer st = new StringTokenizer(br.readLine());
    int t = Integer.parseInt(st.nextToken());
    while (t-- > 0) {

    }
  }
}`,
  kt: `import java.util.*

fun main() {
  val scanner = Scanner(System.in)
  val t = scanner.nextInt()
  repeat(t) {

  }
}`,
  py: `#!/usr/bin/python3

def _main() -> None:
  t = int(input())
  for _ in range(t):

  pass

if __name__ == '__main__':
  _main()`,
  rb: `t = gets.to_i
t.times do

end`,
  cs: `using System;
using System.Collections.Generic;

class Program {
  static void Main(string[] args) {
    int t = int.Parse(Console.ReadLine());
    for (int i = 0; i < t; i++) {

    }
  }
}`,
  pas: `program CompetitiveProgramming;
var
  t, i: integer;
begin
  readln(t);
  for i := 1 to t do
  begin

  end;
end.`,
  hs: `main :: IO ()
main = do
  t <- readLn
  mapM_ (\\_ -> do

    return ()) [1..t]`,
  lua: `local t = io.read("*n")
for i = 1, t do

end`,
  go: `package main

import "fmt"

func main() {
  var t int
  fmt.Scan(&t)
  for i := 0; i < t; i++ {

  }
}`,
  rs: `use std::io::{self, BufRead};

fn main() {
  let stdin = io::stdin();
  let mut lines = stdin.lock().lines();
  let t: i32 = lines.next().unwrap().unwrap().trim().parse().unwrap();
  for _ in 0..t {

  }
}`,
  js: `const readline = require('readline');

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

rl.question('', (t) => {
  t = parseInt(t);
  for (let i = 0; i < t; i++) {

  }
  rl.close();
});`,
  cat: '',
};

export const originalInteractiveTemplates: Record<string, string> = {
  c: `#include "sumas.h"

long long sumas(long long a, long long b) {

  return 0;
}`,
  cpp: `#include "sumas.h"

long long sumas(long long a, long long b) {

  return 0;
}`,
  java: `public class sumas {
  public static long sumas(long a, long b) {

    return 0;
  }
}`,
  kt: '// not supported',
  py: `#!/usr/bin/python3

import Main

def sumas(a: int, b: int) -> int:

    return 0`,
  rb: '# not supported',
  cs: '// not supported',
  pas: `unit sumas;
{
 unit Main;
}

interface
  function sumas(a: LongInt; b: LongInt): LongInt;

implementation

uses Main;

function sumas(a: LongInt; b: LongInt): LongInt;
begin

  sumas := 0;
end;

end.`,
  hs: '-- not supported',
  lua: '-- not supported',
  go: '// not supported',
  rs: '// not supported',
  js: '// not supported',
  cat: '// not supported',
};
