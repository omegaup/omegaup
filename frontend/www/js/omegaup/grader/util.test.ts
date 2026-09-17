import { detectLanguageFromCode } from './util';

describe('detectLanguageFromCode', () => {
  describe('Python detection', () => {
    it('should detect Python for competitive programming code', () => {
      const pythonCode = `n, q = map(int, input().split())
v = list(map(int, input().split()))
for _ in range(q):
  a, b = map(int, input().split())
  s = 0
  for i in range(a, b + 1):
    s += v[i]
  print(s)`;
      const result = detectLanguageFromCode(pythonCode);
      expect(result).not.toBeNull();
      expect(result?.language).not.toBe('lua');
      expect(result?.language).toBe('py3');
    });

    it('should detect Python for complete Python code with imports', () => {
      const completePython = `import sys
def solve():
    n = int(input())
    for i in range(n):
        print(i * 2)
if __name__ == "__main__":
    solve()`;
      const result = detectLanguageFromCode(completePython);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('py3');
    });
  });
  describe('Lua detection', () => {
    it('should not falsely detect Lua for simple code', () => {
      const simpleCodeExample = `n = int(input())
print(n * 2)`;
      const result = detectLanguageFromCode(simpleCodeExample);
      expect(result?.language).not.toBe('lua');
    });

    it('should detect Lua for simple Lua code with io.read', () => {
      const luaCode = `local a, b = io.read("*n", "*n")
print(a + b)`;
      const result = detectLanguageFromCode(luaCode);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('lua');
    });

    it('should detect Lua for complete Lua code', () => {
      const completeLua = `local function solve()
    local n = io.read("*n")
    for i = 1, n do
        io.write(i .. "\\n")
    end
end
solve()`;
      const result = detectLanguageFromCode(completeLua);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('lua');
    });

    it('should detect Lua for simple code with low confidence', () => {
      const simpleCode = `local x = 5
print(x)`;
      const result = detectLanguageFromCode(simpleCode);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('lua');
    });
  });

  describe('C detection', () => {
    it('should detect C for competitive programming code', () => {
      const cCode = `#include <stdio.h>
int main() {
    int n;
    scanf("%d", &n);
    printf("%d\\n", n * 2);
    return 0;
}`;
      const result = detectLanguageFromCode(cCode);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('c11-gcc');
    });
  });

  describe('single-pattern detection', () => {
    it('should detect C from printf without an include', () => {
      const code = `int main() {
    printf("%d", x);
    return 0;
}`;
      const result = detectLanguageFromCode(code);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('c11-gcc');
    });

    it('should detect Java from a bare public class', () => {
      const code = `public class Solution {
}`;
      const result = detectLanguageFromCode(code);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('java');
    });

    it('should detect Lua from local declarations only', () => {
      const code = `local x = 5
local y = 10`;
      const result = detectLanguageFromCode(code);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('lua');
    });
  });

  describe('C++ detection', () => {
    it('should detect C++ for competitive programming code', () => {
      const cppCode = `#include "bits/stdc++.h"
using namespace std;

int main() {
    int n;
    cin >> n;
    vector<int> v(n);
    for (int i = 0; i < n; i++) {
        cout << v[i] << endl;
    }
    return 0;
}`;

      const result = detectLanguageFromCode(cppCode);
      expect(result).not.toBeNull();
      expect(result?.language).toBe('cpp20-gcc');
    });
  });
});
