# 🚀 omegaUp API Tools

Este directorio contiene herramientas para interactuar con endpoints alternativos de omegaUp.

## 📁 Archivos Principales

### ✅ `bulk_submit.py`
**Sistema de Submissions Estándar**
- **Endpoint**: `/api/run/create/`
- **Purpose**: Submissions that **ARE** saved to database
- **Autenticación**: Authorization header con token
- **Características**:
  - Submissions reales con veredictos AC/WA/TLE
  - Aparecen en el perfil del usuario
  - Batch submissions automáticas
  - Espera de resultados con polling

**Uso:**
```python
python3 bulk_submit.py
```

### ✅ `ephemeral_runner.py`
**Ephemeral System (No Traces)**
- **Endpoint**: `/grader/ephemeral/run/new/`
- **Purpose**: Executions **WITHOUT traces** in database
- **Autenticación**: Cookie `ouat` con token
- **Características**:
  - Ideal para problemsetters
  - No deja rastros en DB
  - Testing privado de soluciones
  - Batch testing ephemeral

**Uso:**
```python
python3 ephemeral_runner.py
```

### 📄 `aliases.txt`
Lista de aliases de problemas para testing automático.

## 🔑 Configuración

Los scripts requieren un token de API válido de omegaUp. El token se maneja automáticamente con la siguiente prioridad:

1. **Token como parámetro**: `--token abc123` o `-t abc123`
2. **Archivo `.token`**: Se lee automáticamente si existe
3. **Input manual**: Se solicita por teclado si no se encuentra

### Métodos de configuración:

#### Opción 1: Parámetro de línea de comandos
```bash
python3 bulk_submit.py aliases.txt solution.py --token tu_token_aqui
python3 ephemeral_runner.py -t your_token_here
```

#### Opción 2: Archivo .token (recomendado)
```bash
echo "tu_token_aqui" > .token
python3 bulk_submit.py aliases.txt solution.py
```

#### Opción 3: Input interactivo
```bash
python3 ephemeral_runner.py
# Token will be prompted via keyboard and saved to .token
```

### Get your token:
You can generate a token at: https://omegaup.com/profile/edit/#api-tokens

## 🎯 Use Cases

| Case | Tool | DB Traces | Ideal For |
|------|------|-----------|-----------|
| **Real Submissions** | `bulk_submit.py` | ✅ YES | Normal users, testing real submissions |
| **Private Testing** | `ephemeral_runner.py` | ❌ NO | Testing without affecting statistics |

## 🚀 Technical Differences

### Standard Submissions
```json
{
  "problem_alias": "sumas",
  "language": "py3", 
  "source": "código..."
}
```

### Ephemeral
```json
{
  "input": {
    "cases": {"sample": {"in": "1 2\n", "out": "3\n", "weight": 1}},
    "limits": {"TimeLimit": "1s", "MemoryLimit": 33554432, ...},
    "validator": {"name": "token-caseless"}
  },
  "language": "py3",
  "source": "código..."
}
```

## 📊 Resultados de Testing

- **Bulk Submissions**: ✅ 4/4 exitosas (100% AC)
- **Ephemeral Runner**: ✅ 2/2 successful (100% success)
- **Nginx**: ✅ Rutas corregidas y funcionales
- **Autenticación**: ✅ Ambos métodos funcionando

---
*Herramientas desarrolladas para omegaUp - endpoints alternativos funcionales*