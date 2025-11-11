#!/usr/bin/env python3
"""
Ephemeral Runner actualizado - funcionando correctamente
"""

import requests
import json
from pathlib import Path
from token_utils import get_token, parse_token_from_args, parse_url_from_args

class EphemeralRunner:
    def __init__(self, token=None, base_url=None):
        self.base_url = base_url or 'http://localhost:8001'
        self.token = get_token(token)
        print(f"🌐 Usando servidor: {self.base_url}")
    
    def load_solution(self, problem_alias):
        """Cargar solución desde archivo"""
        solution_path = Path(f'solutions/{problem_alias}.py')
        
        if not solution_path.exists():
            print(f"❌ No se encuentra solución: {solution_path}")
            return None
            
        solution_code = solution_path.read_text()
        print(f"✅ Solución cargada: {len(solution_code)} caracteres")
        return solution_code
    
    def run_ephemeral(self, problem_alias, source_code=None, language='py3', custom_input=None, custom_output=None):
        """Ejecutar código usando el endpoint ephemeral (sin rastros en DB)"""
        
        if source_code is None:
            source_code = self.load_solution(problem_alias)
            if source_code is None:
                return {'success': False, 'error': 'No se pudo cargar la solución'}
        
        print(f"\n🚀 Ejecutando ephemeral: {problem_alias} ({language})")
        print(f"   🎯 Sin rastros en base de datos")
        
        try:
            # Configurar sesión
            session = requests.Session()
            session.cookies.set('ouat', self.token)
            
            # Headers JSON como en el código Vue
            session.headers.update({
                'Content-Type': 'application/json',
                'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept': '*/*',
                'Accept-Language': 'es-419,es;q=0.9',
                'Origin': self.base_url,
                'Referer': f'{self.base_url}/grader/ephemeral/',
                'Sec-Fetch-Dest': 'empty',
                'Sec-Fetch-Mode': 'cors',
                'Sec-Fetch-Site': 'same-origin'
            })
            
            # Estructura completa como en GraderStore
            request_data = {
                "input": {
                    "cases": {
                        "sample": {
                            "in": custom_input or "1 2\n",
                            "out": custom_output or "3\n",
                            "weight": 1
                        }
                    },
                    "limits": {
                        "ExtraWallTime": "0s",
                        "MemoryLimit": 33554432,
                        "OutputLimit": 10240,
                        "OverallWallTimeLimit": "1s",
                        "TimeLimit": "1s"
                    },
                    "validator": {
                        "name": "token-caseless"
                    }
                },
                "language": language,
                "source": source_code
            }
            
            url = f"{self.base_url}/grader/ephemeral/run/new/"
            
            print(f"   🌐 URL: {url}")
            print(f"   💻 Language: {language}")
            print(f"   📝 Source: {source_code[:50]}...")
            
            # Enviar como JSON
            response = session.post(url, json=request_data, timeout=30)
            
            print(f"   📤 Status: {response.status_code}")
            
            if response.status_code == 200:
                print(f"   ✅ ¡Éxito! Ejecución ephemeral completada")
                print(f"   🎯 No se guardaron rastros en la base de datos")
                
                # La respuesta es multipart/form-data con actualizaciones streaming
                response_text = response.text
                ephemeral_token = response.headers.get('X-Omegaup-Ephemeraltoken', '')
                
                if ephemeral_token:
                    print(f"   🔑 Ephemeral Token: {ephemeral_token}")
                
                # Mostrar preview de la respuesta
                print(f"   📄 Response preview: {response_text[:200]}...")
                
                return {
                    'success': True,
                    'response': response_text,
                    'status_code': response.status_code,
                    'ephemeral_token': ephemeral_token,
                    'problem_alias': problem_alias
                }
            else:
                print(f"   ❌ Error {response.status_code}")
                print(f"   📄 Response: {response.text[:200]}...")
                return {
                    'success': False,
                    'error': f"HTTP {response.status_code}",
                    'response': response.text
                }
                
        except Exception as e:
            print(f"   ❌ Error de conexión: {e}")
            return {
                'success': False,
                'error': str(e)
            }
    
    def run_batch_ephemeral(self, problems):
        """Ejecutar múltiples problemas en modo ephemeral"""
        
        print(f"🎯 Ejecutando batch ephemeral: {len(problems)} problemas")
        print(f"   💡 Ideal para problemsetters - sin rastros en DB")
        
        results = []
        
        for problem in problems:
            result = self.run_ephemeral(
                problem_alias=problem['alias'],
                source_code=problem.get('source'),
                language=problem.get('language', 'py3'),
                custom_input=problem.get('input'),
                custom_output=problem.get('output')
            )
            
            results.append({
                'problem': problem['alias'],
                'success': result['success'],
                'result': result
            })
            
            # Pausa pequeña entre ejecuciones
            import time
            time.sleep(0.5)
        
        # Resumen
        successful = sum(1 for r in results if r['success'])
        print(f"\n📊 Resumen batch ephemeral:")
        print(f"   ✅ Exitosas: {successful}/{len(problems)}")
        print(f"   ❌ Fallidas: {len(problems) - successful}/{len(problems)}")
        print(f"   🎯 Todas las ejecuciones sin rastros en DB")
        
        return results

def main():
    """Función principal - ejemplos de uso"""
    
    # Obtener token y URL desde argumentos o archivo
    provided_token = parse_token_from_args()
    provided_url = parse_url_from_args()
    runner = EphemeralRunner(provided_token, provided_url)
    
    # Test individual
    print("🧪 Test ephemeral individual:")
    result = runner.run_ephemeral(
        problem_alias='sumas',
        source_code='a, b = map(int, input().split())\nprint(a + b)',
        language='py3'
    )
    
    if result['success']:
        print(f"✅ Ephemeral exitoso para 'sumas'")
    else:
        print(f"❌ Error en ephemeral: {result.get('error')}")
    
    # Test batch 
    print(f"\n🧪 Test batch ephemeral:")
    problems = [
        {
            'alias': 'sumas',
            'source': 'a, b = map(int, input().split())\nprint(a + b)',
            'language': 'py3',
            'input': '1 2\n',
            'output': '3\n'
        },
        {
            'alias': 'colas',
            'source': 'n = int(input())\nnums = list(map(int, input().split()))\nprint(*nums)',
            'language': 'py3',
            'input': '5\n1 2 3 4 5\n',
            'output': '1 2 3 4 5\n'
        }
    ]
    
    batch_results = runner.run_batch_ephemeral(problems)
    
    print(f"\n🎯 Ephemeral runner listo para problemsetters!")
    print(f"   💡 Ejecuta código sin dejar rastros en la base de datos")
    print(f"   🚀 Perfecto para probar soluciones durante desarrollo de problemas")

if __name__ == "__main__":
    main()