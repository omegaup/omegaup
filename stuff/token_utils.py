#!/usr/bin/env python3
"""
Utilidad para manejo de tokens de omegaUp
"""

import sys
from pathlib import Path

def get_token(provided_token=None):
    """
    Obtener token de API de omegaUp con la siguiente prioridad:
    1. Token proporcionado como parámetro
    2. Token desde archivo .token
    3. Solicitar token via input
    
    Args:
        provided_token (str, optional): Token proporcionado directamente
    
    Returns:
        str: Token de API válido
    """
    
    token_file = Path('.token')
    
    # 1. Si se proporciona token, guardarlo y usarlo
    if provided_token:
        print(f"🔑 Usando token proporcionado")
        with open(token_file, 'w') as f:
            f.write(provided_token.strip())
        return provided_token.strip()
    
    # 2. Intentar cargar desde archivo .token
    if token_file.exists():
        try:
            with open(token_file, 'r') as f:
                token = f.read().strip()
            if token:
                print(f"🔑 Usando token desde archivo .token")
                return token
        except Exception as e:
            print(f"⚠️  Error leyendo .token: {e}")
    
    # 3. Solicitar token via input
    print("🔑 No se encontró token. Por favor proporciona tu token de API de omegaUp:")
    print("   (Puedes obtenerlo en: https://omegaup.com/profile/edit/#api-tokens)")
    
    while True:
        token = input("Token: ").strip()
        if token:
            # Guardar para uso futuro
            with open(token_file, 'w') as f:
                f.write(token)
            print(f"✅ Token guardado en .token para uso futuro")
            return token
        else:
            print("❌ Token vacío. Intenta de nuevo.")

def parse_token_from_args():
    """
    Parsear token desde argumentos de línea de comandos
    
    Usage:
        script.py --token abc123
        script.py -t abc123
        
    Returns:
        str or None: Token si se proporcionó en argumentos
    """
    
    if len(sys.argv) >= 3:
        for i, arg in enumerate(sys.argv[1:], 1):
            if arg in ['--token', '-t'] and i + 1 < len(sys.argv):
                return sys.argv[i + 1]
    
    return None

def parse_url_from_args():
    """
    Parsear URL base desde argumentos de línea de comandos
    
    Usage:
        script.py --url https://omegaup.com
        script.py -u https://omegaup.com
        
    Returns:
        str or None: URL si se proporcionó en argumentos
    """
    
    if len(sys.argv) >= 3:
        for i, arg in enumerate(sys.argv[1:], 1):
            if arg in ['--url', '-u'] and i + 1 < len(sys.argv):
                return sys.argv[i + 1]
    
    return None

if __name__ == "__main__":
    # Test de la utilidad
    token = get_token(parse_token_from_args())
    print(f"Token obtenido: {token[:10]}...")