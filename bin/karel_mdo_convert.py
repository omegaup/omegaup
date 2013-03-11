#!/usr/bin/python

import struct
import sys

if len(sys.argv) == 1:
	print "python karel_mdo_convert.py mundo.mdo"
	sys.exit(1)

f = open(sys.argv[1], "rb")
data = f.read()
f.close()

worldname = sys.argv[1]

if '/' in worldname:
	worldname = worldname[worldname.rfind('/')+1:]
if '.' in worldname:
	worldname = worldname[:worldname.rfind('.')]

kec = False
for extension in ("kec", "KEC"):
	try:
		f = open(sys.argv[1][:sys.argv[1].rfind(".")] + "." + extension, "rb")
		kec = f.read()
		f.close()
		break
	except Exception:
		pass

if not kec:
	print "%s.kec not found" % worldname
	sys.exit(1)

(x1, width, height, buzzers, karelx, karely, karelorient, wallcount, heapcount, x10) = struct.unpack("HHHHHHHHHH", data[10:30])

tuples = [struct.unpack("HHH", data[i:i+6]) for i in xrange(30, len(data), 6)]
kec = [struct.unpack("HHH", kec[i:i+6]) for i in xrange(0, len(kec), 6)]

maxlines = kec[0][1] if kec[0][0] else 10000000
maxmove = kec[1][1] if kec[1][0] else False
maxturnleft = kec[2][1] if kec[2][0] else False
maxpickbeeper = kec[3][1] if kec[3][0] else False
maxputbeeper = kec[4][1] if kec[4][0] else False
maxkarelbeepers = kec[5][1] if kec[5][0] else False
maxbeepers = kec[6][1] if kec[6][0] else False
endposition = kec[7][1:] if kec[7][0] else False
endorientation = ["NORTE", "ESTE", "SUR", "OESTE"][kec[8][1]] if kec[8][0] else False
dumpcount = kec[9][1] if kec[9][0] else 0 
	
def formatbuzzers(b):
	if b == 65535:
		return "INFINITO"
	else:
		return "%d" % b

def isborder(wall, w, h):
	if wall[0] == wall[2]:
		return wall[0] in (0, w)
	if wall[1] == wall[3]:
		return wall[1] in (0, h)

def decodewalls(t, w, h):
	dx = ((-1, 0, -1, -1), (0, 0, 0, -1))
	dy = ((0, -1, -1, -1), (0, 0, -1, 0))
	for i in xrange(4):
		if (t[2] & (1 << i)):
			wall = (t[0] + dx[0][i], t[1] + dy[0][i], t[0] + dx[1][i], t[1] + dy[1][i])
			if not isborder(wall, w, h):
				yield wall

def encodewall(w):
	if w[0] == w[2]:
		return 'x1="%d" y1="%d" y2="%d"' % (w[0], min(w[1], w[3]), max(w[1], w[3]))
	elif w[1] == w[3]:
		return 'x1="%d" x2="%d" y1="%d"' % (min(w[0], w[2]), max(w[0], w[2]), w[1])
	else:
		sys.exit(1)

def generateIn():
	print "<ejecucion>"
	if maxmove != False or maxturnleft != False or maxpickbeeper != False or maxputbeeper != False:
		print "  <condiciones instruccionesMaximasAEjecutar=\"%d\" longitudStack=\"65000\">" % maxlines
		if maxmove != False:
			print '    <comando nombre="AVANZA" maximoNumeroDeEjecuciones="%d" />' % maxmove
		if maxturnleft != False:
			print '    <comando nombre="GIRA_IZQUIERDA" maximoNumeroDeEjecuciones="%d" />' % maxturnleft
		if maxpickbeeper != False:
			print '    <comando nombre="COGE_ZUMBADOR" maximoNumeroDeEjecuciones="%d" />' % maxpickbeeper
		if maxputbeeper != False:
			print '    <comando nombre="DEJA_ZUMBADOR" maximoNumeroDeEjecuciones="%d" />' % maxputbeeper
		print "  </condiciones>"
	else:
		print "  <condiciones instruccionesMaximasAEjecutar=\"%d\" longitudStack=\"65000\" />" % maxlines
	print "  <mundos>"
	print "    <mundo nombre=\"mundo_0\" ancho=\"%d\" alto=\"%d\">" % (width, height)

	for i in xrange(wallcount):
		for wall in decodewalls(tuples[i], width, height):
			print "      <pared %s/>" % encodewall(wall)

	for i in xrange(wallcount, wallcount + heapcount):
		print "      <monton x=\"%d\" y=\"%d\" zumbadores=\"%d\"/>" % tuples[i]

	for i in xrange(10, 10 + dumpcount):
		print "      <posicionDump x=\"%d\" y=\"%d\" />" % kec[i][:2]

	print "    </mundo>"

	print "  </mundos>"
	print "  <programas tipoEjecucion=\"CONTINUA\" intruccionesCambioContexto=\"1\" milisegundosParaPasoAutomatico=\"0\">"
	print "    <programa nombre=\"p1\" ruta=\"{$2$}\" mundoDeEjecucion=\"mundo_0\" xKarel=\"%d\" yKarel=\"%s\" direccionKarel=\"%s\" mochilaKarel=\"%s\" >" \
		% (karelx, karely, ["", "NORTE", "ESTE", "SUR", "OESTE"][karelorient], formatbuzzers(buzzers))
	if dumpcount:
		print "      <despliega tipo=\"MUNDO\" />"
	if endorientation:
		print "      <despliega tipo=\"ORIENTACION\" />"
	if endposition:
		print "      <despliega tipo=\"POSICION\" />"
	print "    </programa>"
	print "  </programas>"
	print "</ejecucion>"

generateIn()
