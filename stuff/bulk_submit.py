#!/usr/bin/env python3
"""
Script mejorado para enviar submissions usando el sistema existente
Reemplaza el script de Selenium con una versión más eficiente
"""

import sys
import time
from pathlib import Path

# Importar nuestros clientes existentes
sys.path.append('/home/jpgomez/Development/omegaup/stuff')
from omegaup_api_client import OmegaUpAPIClient
from token_utils import get_token, parse_token_from_args, parse_url_from_args

class BulkSubmissionRunner:
    def __init__(self, token=None, base_url=None):
        self.client = None
        self.solution_code = None
        self.token = get_token(token)
        self.base_url = base_url or 'http://localhost:8001'
        print(f"✅ Token configurado: {self.token[:20]}...")
        print(f"🌐 Usando servidor: {self.base_url}")
    
    def load_solution(self, solution_file):
        """Cargar código de solución"""
        solution_path = Path(solution_file)
        if not solution_path.exists():
            print(f"❌ Archivo de solución no encontrado: {solution_file}")
            return False
            
        self.solution_code = solution_path.read_text()
        print(f"✅ Solución cargada: {len(self.solution_code)} caracteres")
        return True
    
    def load_aliases_from_file(self, aliases_file):
        """Cargar aliases desde archivo de texto"""
        aliases_path = Path(aliases_file)
        if not aliases_path.exists():
            print(f"❌ Archivo de aliases no encontrado: {aliases_file}")
            return []
            
        aliases = []
        with open(aliases_path, 'r', encoding='utf-8') as f:
            for line in f:
                alias = line.strip()
                if alias and not alias.startswith('#'):  # Ignorar comentarios
                    aliases.append(alias)
                    
        print(f"✅ Cargados {len(aliases)} aliases")
        return aliases
    
    def submit_to_problem(self, problem_alias):
        """Enviar solución a un problema específico"""
        try:
            print(f"\n🚀 Enviando a: {problem_alias}")
            
            # Hacer submission usando requests directamente con Authorization header
            submission_url = f"{self.base_url}/api/run/create/"
            
            headers = {
                'Authorization': f'token {self.token}',
                'Content-Type': 'application/x-www-form-urlencoded'
            }
            
            data = {
                'problem_alias': problem_alias,
                'source': self.solution_code,
                'language': 'py3'
            }
            
            import requests
            response = requests.post(submission_url, headers=headers, data=data, timeout=30)
            
            if response.status_code == 200:
                submission = response.json()
            else:
                print(f"   ❌ HTTP {response.status_code}: {response.text[:200]}...")
                return False
            
            if submission:
                print(f"   ✅ Submission creada: {submission.get('guid', 'N/A')}")
                
                # Opcional: Esperar resultado
                if 'guid' in submission:
                    print(f"   ⏳ Esperando veredicto...")
                    verdict = self.wait_for_verdict(submission['guid'], max_wait_time=30)
                    
                    if verdict:
                        status = verdict.get('verdict', 'Unknown')
                        score = verdict.get('score', 0)
                        print(f"   📊 Resultado: {status} (Score: {score})")
                    else:
                        print(f"   ⏰ Timeout esperando veredicto")
                        
                return True
            else:
                print(f"   ❌ Error creando submission")
                return False
                
        except Exception as e:
            print(f"   ❌ Error: {e}")
            return False
    
    def wait_for_verdict(self, guid, max_wait_time=30):
        """Esperar veredicto de una submission"""
        import requests
        import time
        
        start_time = time.time()
        
        while time.time() - start_time < max_wait_time:
            try:
                details_url = f"{self.base_url}/api/run/status/"
                headers = {'Authorization': f'token {self.token}'}
                params = {'run_alias': guid}
                
                response = requests.get(details_url, headers=headers, params=params, timeout=10)
                
                if response.status_code == 200:
                    result = response.json()
                    
                    # Verificar si ya terminó
                    if result.get('status') == 'ready':
                        return result
                
                time.sleep(2)  # Esperar 2 segundos antes del siguiente intento
                
            except Exception as e:
                print(f"      Error consultando veredicto: {e}")
                break
        
        return None
    
    def run_bulk_submissions(self, aliases_file, solution_file, wait_between=3):
        """Ejecutar submissions en lote"""
        print("🎯 Iniciando submissions en lote")
        print("=" * 50)
        
        # El token ya fue configurado en __init__
        if not self.token:
            print("❌ No se pudo obtener token válido")
            return False
            
        if not self.load_solution(solution_file):
            return False
            
        aliases = self.load_aliases_from_file(aliases_file)
        if not aliases:
            return False
        
        print(f"\n📋 Procesando {len(aliases)} problemas con {wait_between}s de espera entre cada uno")
        
        # Confirmar antes de proceder
        if len(aliases) > 10:
            response = input(f"\n⚠️  ¿Enviar a {len(aliases)} problemas? (y/n): ")
            if response.lower() != 'y':
                print("❌ Cancelado por el usuario")
                return False
        
        # Procesar submissions
        success_count = 0
        failed_count = 0
        
        for i, alias in enumerate(aliases, 1):
            print(f"\n[{i}/{len(aliases)}]", end="")
            
            if self.submit_to_problem(alias):
                success_count += 1
            else:
                failed_count += 1
            
            # Esperar entre submissions para no saturar el servidor
            if i < len(aliases):
                print(f"   ⏳ Esperando {wait_between}s...")
                time.sleep(wait_between)
        
        # Resumen final
        print(f"\n📊 Resumen:")
        print(f"   ✅ Exitosas: {success_count}")
        print(f"   ❌ Fallidas: {failed_count}")
        print(f"   📈 Tasa de éxito: {success_count/(success_count+failed_count)*100:.1f}%")
        
        return success_count > 0

def main():
    # Parsear argumentos especiales para token y URL
    if '--help' in sys.argv or '-h' in sys.argv:
        print("Uso:")
        print(f"  {sys.argv[0]} <archivo_aliases> <archivo_solucion> [tiempo_espera] [--token TOKEN] [--url URL]")
        print()
        print("Ejemplos:")
        print(f"  {sys.argv[0]} aliases.txt solution.py")
        print(f"  {sys.argv[0]} aliases.txt solution.py 5")
        print(f"  {sys.argv[0]} aliases.txt solution.py --token abc123def456")
        print(f"  {sys.argv[0]} aliases.txt solution.py --url https://omegaup.com")
        print(f"  {sys.argv[0]} aliases.txt solution.py 5 -t abc123def456 --url https://omegaup.com")
        print()
        print("Formato aliases.txt:")
        print("  SumasJP")
        print("  A-PLUS-B") 
        print("  HELLO-WORLD")
        print("  # Comentarios empiezan con #")
        print()
        print("Opciones:")
        print("  --token, -t TOKEN    Token de API de omegaUp")
        print("  --url, -u URL        Base URL (default: http://localhost:8001)")
        print("                       Usar https://omegaup.com para producción")
        print()
        print("Token:")
        print("  • Se puede proporcionar con --token o -t")
        print("  • Si no se proporciona, se busca en .token")
        print("  • Si no existe .token, se solicita por input")
        sys.exit(0)
    
    # Filtrar argumentos de token y URL para obtener los argumentos normales
    filtered_args = []
    skip_next = False
    
    for i, arg in enumerate(sys.argv[1:], 1):
        if skip_next:
            skip_next = False
            continue
        if arg in ['--token', '-t', '--url', '-u']:
            skip_next = True
            continue
        filtered_args.append(arg)
    
    if len(filtered_args) < 2:
        print("❌ Argumentos insuficientes. Usa --help para ver el uso.")
        sys.exit(1)
    
    aliases_file = filtered_args[0]
    solution_file = filtered_args[1]
    wait_time = int(filtered_args[2]) if len(filtered_args) > 2 else 3
    
    # Obtener token y URL desde argumentos
    provided_token = parse_token_from_args()
    provided_url = parse_url_from_args()
    
    runner = BulkSubmissionRunner(provided_token, provided_url)
    success = runner.run_bulk_submissions(aliases_file, solution_file, wait_time)
    
    if success:
        print("\n🎉 ¡Proceso completado!")
    else:
        print("\n💥 Proceso terminado con errores")
        sys.exit(1)

if __name__ == "__main__":
    main()