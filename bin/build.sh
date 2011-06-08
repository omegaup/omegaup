#!/bin/bash

if [ "bin" == "$(basename `pwd`)" ]; then
	cd ..
fi

cd sandbox
make
