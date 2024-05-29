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

export const sourceTemplates = {
  c: `#include <stdio.h>
#include <stdint.h>

int main() {
  // TODO: fixme.

  return 0;
}`,
  cpp: `#include <iostream>

int main() {
  std::cin.tie(nullptr);
  std::ios_base::sync_with_stdio(false);

  // TODO: fixme.

  return 0;
}`,
  cs: `using System.Collections.Generic;
using System.Linq;
using System;

class Program
{
  static void Main(string[] args)
  {
    // TODO: fixme.
  }
}`,
  java: `import java.io.BufferedReader;
import java.io.IOException;
import java.io.InputStreamReader;

public class Main {
  public static void main(String[] args) throws IOException {
    BufferedReader br = new BufferedReader(
                          new InputStreamReader(System.in));
    // TODO: fixme.
  }
}`,
  lua: `-- TODO: fixme.`,
  py: `#!/usr/bin/python3

def _main() -> None:
  # TODO: fixme.
  pass

if __name__ == '__main__':
  _main()`,
  rb: `# TODO: fixme.`,
};

export const originalInteractiveTemplates = {
  c: `#include "sumas.h"

long long sumas(long long a, long long b) {
  // FIXME
  return 0;
}`,
  cpp: `#include "sumas.h"

long long sumas(long long a, long long b) {
  // FIXME
  return 0;
}`,
  cs: '// not supported',
  java: `public class sumas {
  public static long sumas(long a, long b) {
    // FIXME
    return 0;
  }
}`,
  lua: '-- not supported',
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
  { FIXME }
  sumas := 0;
end;

end.`,
  py: `#!/usr/bin/python3

import Main

def sumas(a: int, b: int) -> int:
    """ sumas """
    // FIXME
    return 0`,
  rb: '# not supported',
};