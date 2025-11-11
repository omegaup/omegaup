#!/usr/bin/env python3
"""
Verificador de token de producción
"""

import requests
import sys

def test_production_token(token):
    """Probar token en producción de omegaUp"""
    
    base_url = 'https://omegaup.com'
    
    print(f"🧪 Probando token de producción...")
    print(f"🌐 Servidor: {base_url}")
    print(f"🔑 Token: {token[:10]}...")
    
    # Test 1: Profile API (requiere autenticación)
    try:
        response = requests.get(
            f"{base_url}/api/user/profile/",
            headers={'Authorization': f'token {token}'},
            timeout=10
        )
        
        if response.status_code == 200:
            profile = response.json()
            username = profile.get('userinfo', {}).get('username', 'Unknown')
            print(f"✅ Token válido - Usuario: {username}")
            return True
        elif response.status_code == 401:
            print(f"❌ Token inválido o expirado")
            return False
        else:
            print(f"⚠️  Respuesta inesperada: {response.status_code}")
            print(f"   Response: {response.text[:200]}...")
            return False
            
    except Exception as e:
        print(f"❌ Error de conectividad: {e}")
        return False

if __name__ == "__main__":
    if len(sys.argv) > 1:
        token = sys.argv[1]
    else:
        token = input("Token de producción: ").strip()
    
    if test_production_token(token):
        print(f"\n🎯 ¡Token listo para usar en producción!")
    else:
        print(f"\n💥 Token no válido - verifica y vuelve a intentar")