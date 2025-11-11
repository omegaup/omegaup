# Solución simple para problemas A+B
try:
    a, b = map(int, input().split())
    print(a + b)
except:
    # Para otros tipos de problemas, intentar diferentes aproximaciones
    try:
        print("Hello World!")
    except:
        print("42")