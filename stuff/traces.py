#!/usr/bin/python

import argparse
import json
import re

parser = argparse.ArgumentParser('Process the perf log into something Chrome can display.')
parser.add_argument('file', nargs='?', default='/var/log/omegaup/perf.log',
                    help='The location of the omegaUp perf.log')
parser.add_argument('--tscaling', default='as-is', choices=['as-is','shift','trim', 'cat'],
                    help='The layout of the events in the Chrome tracing screen')
parser.add_argument('--sscaling', default='as-is', choices=['run-id','single','best'],
                    help='The layout of the events in the Chrome tracing screen')
args = parser.parse_args()

PATTERN = re.compile('^.*omegaup.grader.RunContext - ')

runs = []
with open(args.file, 'r') as perflog:
	for line in perflog:
		runs.append(sorted(
			json.loads(PATTERN.sub('', line.strip())),
			lambda x, y: x['ts'] - y['ts']))

runs.sort(lambda x,y: x[0]['ts'] - y[1]['ts'])

# Temporal (horizontal) scaling
if args.tscaling == 'shift':
	for run in runs:
		t0 = run[0]['ts']

		for event in run:
			event['ts'] -= t0
elif args.tscaling == 'cat':
	t1 = 0
	for run in runs:
		t0 = run[0]['ts'] - t1
		t1 += run[-1]['ts'] - run[0]['ts'] + 1000

		for event in run:
			event['ts'] -= t0
elif args.tscaling == 'trim':
	ends = [(runs[0][0]['ts'], 0)]
	for run in runs:
		tidx = -1
		while ends[tidx][0] > run[0]['ts']:
			tidx -= 1
		t0, t1 = ends[tidx]
		delta = run[0]['ts'] - t1
		if run[-1]['ts'] > ends[-1][0]:
			ends.append((run[-1]['ts'], run[-1]['ts'] - delta))

		for event in run:
			event['ts'] -= delta

# Spatial (vertical) scaling
if args.sscaling == 'single':
	for run in runs:
		for event in run:
			event['tid'] = 0
if args.sscaling == 'best':
	threads = []
	for run in runs:
		idx = -1
		for i in xrange(len(threads)):
			if threads[i] <= run[0]['ts']:
				idx = i
				break
		if idx == -1:
			idx = len(threads)
			threads.append(0)
		threads[idx] = run[-1]['ts']
		for event in run:
			event['tid'] = idx

# Flatten runs
data = []
for run in runs:
	data += run

print json.dumps(data, separators=(',', ':'))
