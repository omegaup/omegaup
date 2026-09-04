# Prueba de Mermaid en omegaUp

Este es un ejemplo de diagrama de flujo:

```mermaid
flowchart TD
    A[Inicio] --> B{¿Es correcto?}
    B -->|Sí| C[Continuar]
    B -->|No| D[Corregir]
    D --> A
    C --> E[Fin]
```

## Diagrama de secuencia

```mermaid
sequenceDiagram
    participant Usuario
    participant Sistema
    participant Juez
    
    Usuario->>Sistema: Enviar solución
    Sistema->>Juez: Evaluar código
    Juez->>Juez: Ejecutar casos de prueba
    Juez-->>Sistema: Resultado
    Sistema-->>Usuario: Mostrar veredicto
```

## Gráfica de Gantt

```mermaid
gantt
    title Calendario del concurso
    dateFormat  YYYY-MM-DD
    section Preparación
    Diseño de problemas    :a1, 2024-01-01, 30d
    Pruebas                :a2, after a1, 20d
    section Concurso
    Ronda clasificatoria   :b1, after a2, 7d
    Final                  :b2, after b1, 1d
```

## Diagrama de clases

```mermaid
classDiagram
    class Problem {
        +String title
        +String description
        +int timeLimit
        +int memoryLimit
        +solve()
    }
    class TestCase {
        +String input
        +String output
        +validate()
    }
    Problem "1" --> "*" TestCase : has
```

## Gráfica de estados

```mermaid
stateDiagram-v2
    [*] --> Pending
    Pending --> Running: Start evaluation
    Running --> Accepted: All tests pass
    Running --> WrongAnswer: Test fails
    Running --> RuntimeError: Program crashes
    Running --> TimeLimit: Timeout
    Accepted --> [*]
    WrongAnswer --> [*]
    RuntimeError --> [*]
    TimeLimit --> [*]
```

## Texto normal con LaTeX

Aquí puedes mezclar Mermaid con LaTeX: $f(x) = x^2 + 2x + 1$

Y también con código:

```python
def fibonacci(n):
    if n <= 1:
        return n
    return fibonacci(n-1) + fibonacci(n-2)
```
