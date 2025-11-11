#!/usr/bin/env python3
"""
Script para probar conectividad con omegaUp en producción
"""

import requests
import sys

def test_production_connectivity():
    """Probar conectividad básica con omegaUp producción"""
    
    print("🌐 Probando conectividad con omegaUp producción...")
    
    # URLs de prueba
    test_urls = [
        "https://omegaup.com",
        "https://omegaup.com/api/user/profile/",
        "https://omegaup.com/api/problem/list/"
    ]
    
    for url in test_urls:
        try:
            print(f"   📡 Probando: {url}")
            response = requests.get(url, timeout=10)
            print(f"   ✅ Status: {response.status_code}")
            
            if response.status_code == 200:
                content_length = len(response.content)
                print(f"   📄 Content length: {content_length} bytes")
            else:
                print(f"   ⚠️  Response: {response.text[:100]}...")
                
        except Exception as e:
            print(f"   ❌ Error: {e}")
    
    print("\n🔑 Para usar las herramientas en producción necesitas:")
    print("   1. Un token válido de https://omegaup.com/profile/edit/#api-tokens")
    print("   2. Ejecutar con: --url https://omegaup.com")
    print()
    print("Ejemplo:")
    print("   python3 bulk_submit.py aliases.txt solution.py --url https://omegaup.com --token TU_TOKEN")

if __name__ == "__main__":
    test_production_connectivity()