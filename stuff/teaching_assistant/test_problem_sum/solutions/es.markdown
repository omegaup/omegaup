Para resolver este problema, sólo necesitabas usar el operador `+`.

```python
print(sum(int(x) for x in raw_input().strip().split()))
```

```cpp
#include <iostream>

int main() {
    std::cin.tie(nullptr);
    std::ios_base::sync_with_stdio(false);

    int A, B;
    std::cin >> A >> B;
    std::cout << A + B << '\n';
}
```
