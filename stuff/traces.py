#!/usr/bin/python3

import argparse
import json
import re

parser = argparse.ArgumentParser('Process the perf log into something Chrome can display.')
parser.add_argument('file', nargs='?', default='/var/log/omegaup/perf.log',
                    help='The location of the omegaUp perf.log')
parser.add_argument('--nopid', default=True, dest='pid', action='store_false',
                    help='The horizontal layout of the events in the Chrome tracing screen')
parser.add_argument('--tscaling', default='as-is', choices=['as-is','shift','trim', 'cat'],
                    help='The horizontal layout of the events in the Chrome tracing screen')
parser.add_argument('--sscaling', default='best', choices=['single','run','best'],
                    help='The vertical layout of the events in the Chrome tracing screen')
args = parser.parse_args()

PATTERN = re.compile('^.*omegaup.grader.RunContext - ')

runs = []
with open(args.file, 'r') as perflog:
	for line in perflog:
		runs.append(sorted(
			json.loads(PATTERN.sub('', line.strip())),
			lambda x, y: x['ts'] - y['ts']))

runs.sort(lambda x,y: x[0]['ts'] - y[1]['ts'])

def extent(run):
	t0, t1 = run[0]['ts'], 0
	for event in run:
		if event['ph'] == 'X':
			t1 = max(t1, event['ts'] + event['dur'])
		else:
			t1 = max(t1, event['ts'])
	return t0, t1

# Segment into runners
runners = {}
if args.pid:
	for run in runs:
		# Find runner
		runner = ""
		for event in run:
			if 'args' in event and 'runner' in event['args']:
				runner = event['args']['runner']
				break
		if runner not in runners:
			runners[runner] = len(runners) + 1
		pid = runners[runner]
		for event in run:
			event['pid'] = pid

# Temporal (horizontal) scaling
if args.tscaling == 'shift':
	for run in runs:
		t0, t1 = extent(run)
		for event in run:
			event['ts'] -= t0
elif args.tscaling == 'cat':
	start = 0
	for run in runs:
		t0, t1 = extent(run)
		delta = t0 - start
		start += t1 - t0
		for event in run:
			event['ts'] -= delta
elif args.tscaling == 'trim':
	curStart = runs[0][0]['ts']
	curEnd = runs[0][0]['ts']
	curOffset = 0
	for run in runs:
		t0, t1 = extent(run)
		if t0 >= curEnd:
			curOffset +=  curEnd - curStart
			curStart = t0
			curEnd = t1
		else:
			curEnd = max(curEnd, t1)
		delta = curStart - curOffset
		for event in run:
			event['ts'] -= delta

# Spatial (vertical) scaling
if args.sscaling == 'run':
	tid = 0
	for run in runs:
		tid += 1
		for event in run:
			event['tid'] = tid
if args.sscaling == 'best':
	pidThreads = {}
	for run in runs:
		t0, t1 = extent(run)
		idx = -1
		if run[0]['pid'] not in pidThreads:
			pidThreads[run[0]['pid']] = []
		threads = pidThreads[run[0]['pid']]
		for i in range(len(threads)):
			if threads[i] <= t0:
				idx = i
				break
		if idx == -1:
			idx = len(threads)
			threads.append(0)
		threads[idx] = t1
		for event in run:
			event['tid'] = idx

# Add metadata
data = [{"name": "process_name", "ph": "M", "pid": runners[runner], "tid": 0, "args": {"name": runner}} for runner in runners]

# Flatten
for run in runs:
	data += run

print(json.dumps(data, separators=(',', ':')))

# vim: set noexpandtab:
