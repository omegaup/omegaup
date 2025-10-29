Para resolver este problema, solo necesitabas usar el operador `-`.

```python
print(int(input().strip().split()[0]) - int(input().strip().split()[1]))
```

```cpp
#include <iostream>

int main() {
    std::cin.tie(nullptr);
    std::ios_base::sync_with_stdio(false);

    int A, B;
    std::cin >> A >> B;
    std::cout << A - B << '\n';
}
```
