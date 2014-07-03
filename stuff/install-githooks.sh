#!/bin/bash

OMEGAUP_ROOT=`/usr/bin/git rev-parse --show-toplevel`

# Install git hooks
for hook in $OMEGAUP_ROOT/stuff/git-hooks/*; do
	if [ ! -e $OMEGAUP_ROOT/.git/hooks/`basename $hook` ]; then
		ln -s $hook $OMEGAUP_ROOT/.git/hooks
	fi
done
