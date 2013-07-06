#!/bin/bash

if [ "bin" == "$(basename `pwd`)" ]; then
	cd ..
fi

cd bin
make
cd ../sandbox
make
