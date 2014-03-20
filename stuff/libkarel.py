# -*- coding: utf-8 -*-
"""Librería para parsear entradas y salidas de Karel en XML."""

import xml.etree.ElementTree as ET
import sys

def load():
	"""Regresa (input, output, nombre de caso) para la ejecución actual"""
	with open('data.in', 'r') as data_in:
		return KarelInput(data_in.read()), KarelOutput(sys.stdin.read()), sys.argv[1]

class KarelInput:
	"""Representa un archivo .in. Los siguientes miembros están definidos:
		* w: el ancho del mundo
		* h: el alto del mundo
		* x: la posición x inicial de Karel
		* y: la posición y inicial de Karel
		* direccion: La orientación inicial de Karel. Puede ser uno de ['NORTE', 'ESTE', 'SUR', 'OESTE']
		* mochila: El número de zumbadores en la mochila de Karel. Puede ser un entero o la cadena 'INFINITO'
		* despliega: Lista de elementos que se van a guardar en la salida. Puede ser uno de ['MUNDO', 'ORIENTACION', 'POSICION']
		* despliega_posicion: True si se va a desplegar la posición final de Karel en la salida
		* despliega_orientacion: True si se va a desplegar la orientación final de Karel en la salida
		* despliega_mundo: True si se van a desplegar los zumbadores finales elegidor en la salida
		* _lista_dump: La lista original de posiciones (x, y) de casillas que se van a desplegar en la salida
		* _dump: Un diccionario donde cada llave (x, y) que esté definida significa que se va a desplegar la casilla
		* _lista_zumbadores: La lista original de montones (x, y, zumbadores) en el mundo
		* _zumbadores: Un diccionario donde cada llave (x, y) tiene como valor el número de zumbadores en esa casilla"""

	def __init__(self, string):
		self.root = ET.fromstring(string)
		mundo = self.root.find('mundos/mundo').attrib
		self.w = int(mundo['ancho'])
		self.h = int(mundo['alto'])
		programa = self.root.find('programas/programa').attrib
		self.x = int(programa['xKarel'])
		self.y = int(programa['yKarel'])
		self.direccion = programa['direccionKarel']
		self.mochila = programa['mochilaKarel']
		if self.mochila != 'INFINITO':
			self.mochila = int(self.mochila)

		self.despliega = map(
			lambda x: x.attrib['tipo'].upper(),
			self.root.findall('programas/programa/despliega')
		)
		self.despliega_orientacion = 'ORIENTACION' in self.despliega
		self.despliega_mundo = 'MUNDO' in self.despliega
		self.despliega_posicion = 'POSICION' in self.despliega
		self.despliega_instrucciones = 'INSTRUCCIONES' in self.despliega

		self._lista_zumbadores = map(
			lambda x: {
				'x': int(x.attrib['x']),
				'y': int(x.attrib['y']),
				'zumbadores': x.attrib['zumbadores']
			},
			self.root.findall('mundos/mundo/monton')
		)
		self._zumbadores = {(x['x'], x['y']): x['zumbadores'] for x in self._lista_zumbadores}
		self._lista_dump = map(
			lambda x: {k: int(x.attrib[k]) for k in x.attrib},
			self.root.findall('mundos/mundo/posicionDump')
		)
		self._dump = {(x['x'], x['y']): True for x in self._lista_dump}

	def __repr__(self):
		"""Imprime una versión bonita del objeto"""
		return '<libkarel.KarelInput %s>' % ', '.join(map(lambda x: '%s=%r' % x, {
			'x': self.x,
			'y': self.y,
			'mochila': self.mochila,
			'direccion': self.direccion,
			'despliega': self.despliega,
		}.iteritems()))
	
	def zumbadores(self, x, y):
		"""Regresa el número de zumbadores (o la cadena 'INFINITO') para la casilla en (x, y)"""
		if (x, y) not in self._zumbadores:
			return 0
		z = self._zumbadores[(x, y)]
		if z == 'INFINITO':
			return z
		return int(z)

	def dump(self, x, y):
		"""Regresa True si la casilla está marcada para generar una salida"""
		return (x, y) in self._dump

class KarelOutput:
	"""Representa un archivo .out. Los siguientes miembros están definidos:
		* resultado: una cadena con el resultado de la ejecución. 'FIN PROGRAMA' significa ejecución exitosa.
		* error: True si no fue una ejeción exitosa.
		* x: la posición x final de Karel. None si no se encuentra en la salida.
		* y: la posición y final de Karel. None si no se encuentra en la salida.
		* direccion: La orientación inicial de Karel. Puede ser uno de ['NORTE', 'ESTE', 'SUR', 'OESTE'], o None si no se encuentra
		* _zumbadores: Un diccionario donde cada llave (x, y) tiene como valor el número de zumbadores en esa casilla al final de la ejecución"""

	def __init__(self, string):
		self.root = ET.fromstring(string)
		self._zumbadores = {}
		for linea in self.root.findall('mundos/mundo/linea'):
			y = int(linea.attrib['fila'])
			x = 0
			for token in linea.text.strip().split():
				if token[0] == '(':
					x = int(token[1:-1])
				else:
					self._zumbadores[(x, y)] = token
					x += 1

		self.resultado = self.root.find('programas/programa').attrib['resultadoEjecucion']
		self.error = self.resultado != 'FIN PROGRAMA'
		karel = self.root.find('programas/programa/karel')
		self.x = None
		self.y = None
		self.direccion = None
		if karel:
			if 'x' in karel.attrib:
				self.x = int(karel.attrib['x'])
				self.y = int(karel.attrib['y'])
			if 'direccion' in karel.attrib:
				self.direccion = karel.attrib['direccion']

	def __repr__(self):
		"""Imprime una versión bonita del objeto"""
		return '<libkarel.KarelOutput %s>' % ', '.join(map(lambda x: '%s=%r' % x, {
			'x': self.x,
			'y': self.y,
			'direccion': self.direccion,
			'resultado': self.resultado,
			'error': self.error,
		}.iteritems()))
	
	def zumbadores(self, x, y):
		"""Regresa el número de zumbadores (o la cadena 'INFINITO') para la casilla en (x, y)"""
		if (x, y) not in self._zumbadores:
			return 0
		z = self._zumbadores[(x, y)]
		if z == 'INFINITO':
			return z
		return int(z)
