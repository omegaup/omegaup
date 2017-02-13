#!/usr/bin/python3

import sys

with open(sys.argv[1], 'r') as f:
	data = []
	for line in f:
		line = line.split()
		data.append([int(line[1]) / 1e6, int(line[3]) / 1e6, int(line[0]), line[2]])

data.sort()
t0 = data[0][0]

for row in data:
	row[0] -= t0

t1 = int(data[-1][0])

window = 10000

i0 = 0
i1 = 0
for t in range(0, t1, 1000):
	while i0 < len(data) and data[i0][0] < t:
		i0 += 1
	i1 = i0
	s = 0
	threads = {}
	while i1 < len(data) and data[i1][0] < t + window:
		s += data[i1][1]
		threads[data[i1][2]] = True
		i1 += 1
	print(t / 1000, (i1 - i0) / (window / 1000.0), s / max(1, i1 - i0), len(threads))
