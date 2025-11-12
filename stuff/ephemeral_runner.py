#!/usr/bin/env python3
"""
Ephemeral Runner actualizado - funcionando correctamente
"""

import hashlib
import time
from pathlib import Path
from typing import Any, Dict, List, Optional, cast

import requests

from token_utils import get_token, parse_token_from_args, parse_url_from_args


class EphemeralRunner:
    """Runner para ejecutar código en modo ephemeral sin rastros en DB"""
    def __init__(self, token: Optional[str] = None,
                 base_url: Optional[str] = None) -> None:
        self.base_url = base_url or 'http://localhost:8001'
        self.token = get_token(token)
        print(f"🌐 Usando servidor: {self.base_url}")

    def get_aliases_file(self) -> str:
        """Determinar qué archivo de aliases usar según la URL"""
        if 'omegaup.com' in self.base_url:
            return 'stuff/prod_test_aliases.txt'
        return 'stuff/aliases.txt'

    def load_solution(self, problem_alias: str) -> Optional[str]:
        """Cargar solución desde archivo"""
        solution_path = Path(f'solutions/{problem_alias}.py')

        if not solution_path.exists():
            print(f"❌ No se encuentra solución: {solution_path}")
            return None

        solution_code = solution_path.read_text(encoding='utf-8')
        print(f"✅ Solución cargada: {len(solution_code)} caracteres")
        return solution_code

    def get_problem_details(
        self,
        problem_alias: str) -> Optional[Dict[str, Any]]:
        """Obtener detalles del problema desde la API"""
        try:
            session = requests.Session()
            # No enviar token/cookies para consultas públicas
            url = f"{self.base_url}/api/problem/details/"
            response = session.get(url,
                                   params={'problem_alias': problem_alias},
                                   timeout=10)

            if response.status_code == 200:
                # Mostrar hash corto y conteo de casos para comprobar cambios
                try:
                    text = response.text
                    hash_short = hashlib.md5(
                        text.encode('utf-8')).hexdigest()[:8]
                    data = cast(Dict[str, Any], response.json())
                    cases = data.get('settings', {}).get('cases', {})
                    cases_count = len(cases) if isinstance(cases, dict) else 0
                except (ValueError, KeyError, TypeError):
                    hash_short = '????????'
                    cases_count = 0
                    data = None

                print(f"   📡 details: alias={problem_alias}, "
                      f"md5={hash_short}, cases={cases_count}")
                return data
            print(f"   ⚠️  Error obteniendo detalles del problema: "
                  f"{response.status_code}")
            return None
        except (requests.RequestException, ConnectionError) as e:
            print(f"   ⚠️  Error conectando con API: {e}")
            return None

    def _build_request_data(self, problem_settings: Optional[Dict[str, Any]],
                            source_code: str, language: str) -> Dict[str, Any]:
        """Construir datos del request usando detalles del problema"""
        # Construir casos
        if problem_settings and 'cases' in problem_settings:
            cases = problem_settings['cases']
            sample_case = list(cases.values())[0] if cases else {}
            sample_input = sample_case.get('in', "1 2\n")
            sample_output = sample_case.get('out', "3\n")
            sample_weight = sample_case.get('weight', 1)
        else:
            sample_input = "1 2\n"
            sample_output = "3\n"
            sample_weight = 1

        # Obtener límites
        if problem_settings and 'limits' in problem_settings:
            limits = problem_settings['limits']
            time_limit = limits.get('TimeLimit', '1s')
            memory_limit = limits.get('MemoryLimit', 33554432)
            output_limit = limits.get('OutputLimit', 10240)
            overall_wall_time = limits.get('OverallWallTimeLimit', '1s')
            extra_wall_time = limits.get('ExtraWallTime', '0s')
        else:
            time_limit = '1s'
            memory_limit = 33554432
            output_limit = 10240
            overall_wall_time = '1s'
            extra_wall_time = '0s'

        # Obtener validador
        if problem_settings and 'validator' in problem_settings:
            validator = problem_settings['validator']
        else:
            validator = {'name': 'token-caseless'}

        return {
            "input": {
                "cases": {
                    "sample": {
                        "in": sample_input,
                        "out": sample_output,
                        "weight": sample_weight
                    }
                },
                "limits": {
                    "ExtraWallTime": extra_wall_time,
                    "MemoryLimit": memory_limit,
                    "OutputLimit": output_limit,
                    "OverallWallTimeLimit": overall_wall_time,
                    "TimeLimit": time_limit
                },
                "validator": validator
            },
            "language": language,
            "source": source_code
        }

    def run_ephemeral(self, problem_alias: str,
                      source_code: Optional[str] = None,
                      language: str = 'py3') -> Dict[str, Any]:
        """Ejecutar código usando el endpoint ephemeral (sin rastros en DB)"""

        if source_code is None:
            source_code = self.load_solution(problem_alias)
            if source_code is None:
                return {'success': False,
                        'error': 'No se pudo cargar la solución'}

        print(f"\n🚀 Ejecutando ephemeral: {problem_alias} ({language})")
        print("   🎯 Sin rastros en base de datos")

        # Obtener detalles del problema desde la API
        print("   📡 Obteniendo detalles del problema...")
        problem_details = self.get_problem_details(problem_alias)

        if not problem_details:
            print("   ⚠️  Usando valores por defecto")
            problem_settings = None
        else:
            problem_settings = problem_details.get('settings', {})
            print("   ✅ Detalles del problema obtenidos")

        try:
            # Configurar sesión (no enviar token/cookies)
            session = requests.Session()
            # Headers básicos
            session.headers.update({
                'Content-Type': 'application/json',
                'User-Agent': 'omegaup-ephemeral-runner/1.0',
                'Origin': self.base_url,
                'Referer': f'{self.base_url}/grader/ephemeral/'
            })

            # Usar método auxiliar para construir datos del request
            request_data = self._build_request_data(
                problem_settings, source_code, language)

            url = f"{self.base_url}/grader/ephemeral/run/new/"

            # Enviar como JSON
            response = session.post(url, json=request_data, timeout=30)

            if response.status_code == 200:
                response_text = response.text
                ephemeral_token = response.headers.get(
                    'X-Omegaup-Ephemeraltoken', '')
                success = bool(ephemeral_token)
                return {
                    'success': success,
                    'response': response_text,
                    'status_code': response.status_code,
                    'ephemeral_token': ephemeral_token,
                    'problem_alias': problem_alias
                }

            return {
                'success': False,
                'error': f"HTTP {response.status_code}",
                'response': response.text
            }

        except (requests.RequestException, ConnectionError) as e:
            print(f"   ❌ Error de conexión: {e}")
            return {
                'success': False,
                'error': str(e)
            }

    def run_batch_ephemeral(self,
                            problems: List[Dict[str, Any]]
                            ) -> List[Dict[str, Any]]:
        """Ejecutar múltiples problemas en modo ephemeral"""

        print(f"🎯 Ejecutando batch ephemeral: {len(problems)} problemas")
        print("   💡 Ideal para problemsetters - sin rastros en DB")

        results = []

        for problem in problems:
            result = self.run_ephemeral(
                problem_alias=problem['alias'],
                source_code=problem.get('source'),
                language=problem.get('language', 'py3')
            )

            results.append({
                'problem': problem['alias'],
                'success': result['success'],
                'result': result
            })

            # Pausa pequeña entre ejecuciones
            time.sleep(0.5)

        # Resumen
        successful = sum(1 for r in results if r['success'])
        print("\n📊 Resumen batch ephemeral:")
        print(f"   ✅ Exitosas: {successful}/{len(problems)}")
        print(f"   ❌ Fallidas: {len(problems) - successful}/{len(problems)}")
        print("   🎯 Todas las ejecuciones sin rastros en DB")

        return results


def main() -> None:
    """Función principal - ejemplos de uso"""

    # Obtener token y URL desde argumentos o archivo
    provided_token = parse_token_from_args()
    provided_url = parse_url_from_args()
    runner = EphemeralRunner(provided_token, provided_url)

    # Determinar qué archivo de aliases usar
    aliases_file = runner.get_aliases_file()
    print(f"📁 Usando archivo de aliases: {aliases_file}")

    # Cargar algunos aliases del archivo correspondiente
    try:
        with open(aliases_file, 'r', encoding='utf-8') as f:
            # Tomar los primeros 3
            aliases = [line.strip() for line in f.readlines()
                       if line.strip()][:3]

        if not aliases:
            print(f"⚠️  No se encontraron aliases en {aliases_file}, "
                  "usando valores por defecto")
            aliases = ['sumas']  # Fallback

        print(f"🎯 Aliases a probar: {aliases}")

    except FileNotFoundError:
        print(f"⚠️  Archivo {aliases_file} no encontrado, "
              "usando alias por defecto")
        aliases = ['sumas']  # Fallback

    # Test individual con el primer alias
    first_alias = aliases[0]
    print(f"\n🧪 Test ephemeral individual con: {first_alias}")
    result = runner.run_ephemeral(
        problem_alias=first_alias,
        source_code='a, b = map(int, input().split())\nprint(a + b)',
        language='py3'
    )

    if result['success']:
        print(f"✅ Ephemeral exitoso para '{first_alias}'")
    else:
        print(f"❌ Error en ephemeral: {result.get('error')}")

    # Test batch con los aliases disponibles
    print("\n🧪 Test batch ephemeral con aliases reales:")
    problems = []

    for alias in aliases[:2]:  # Solo los primeros 2 para no sobrecargar
        problems.append({
            'alias': alias,
            'source': 'a, b = map(int, input().split())\nprint(a + b)',
            'language': 'py3'
        })

    runner.run_batch_ephemeral(problems)

    print("\n🎯 Ephemeral runner listo para problemsetters!")
    print("   💡 Ejecuta código sin dejar rastros en la base de datos")
    print("   🚀 Perfecto para probar soluciones durante "
          "desarrollo de problemas")
    print(f"   📁 Usando aliases de: {aliases_file}")


if __name__ == "__main__":
    main()
