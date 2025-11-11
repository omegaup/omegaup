# 🚀 omegaUp API Tools

Este directorio contiene herramientas para interactuar con endpoints alternativos de omegaUp.

## 📁 Archivos Principales

### ✅ `bulk_submit.py`
**Sistema de Submissions Estándar**
- **Endpoint**: `/api/run/create/`
- **Propósito**: Submissions que **SÍ** se guardan en base de datos
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
**Sistema Ephemeral (Sin Rastros)**
- **Endpoint**: `/grader/ephemeral/run/new/`
- **Propósito**: Ejecuciones **SIN rastros** en base de datos
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
python3 ephemeral_runner.py -t tu_token_aqui
```

#### Opción 2: Archivo .token (recomendado)
```bash
echo "tu_token_aqui" > .token
python3 bulk_submit.py aliases.txt solution.py
```

#### Opción 3: Input interactivo
```bash
python3 ephemeral_runner.py
# Se solicitará el token por teclado y se guardará en .token
```

### Obtener tu token:
Puedes generar un token en: https://omegaup.com/profile/edit/#api-tokens

## 🎯 Casos de Uso

| Caso | Herramienta | Rastros DB | Ideal Para |
|------|-------------|------------|-------------|
| **Submissions Reales** | `bulk_submit.py` | ✅ SÍ | Usuarios normales, testing de verdaderos submissions |
| **Testing Privado** | `ephemeral_runner.py` | ❌ NO | Testing sin afectar estadísticas |

## 🚀 Diferencias Técnicas

### Submissions Estándar
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
- **Ephemeral Runner**: ✅ 2/2 exitosas (100% success)
- **Nginx**: ✅ Rutas corregidas y funcionales
- **Autenticación**: ✅ Ambos métodos funcionando

---
*Herramientas desarrolladas para omegaUp - endpoints alternativos funcionales*