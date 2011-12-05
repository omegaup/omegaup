#!/bin/bash

gcc -x c -o sample - <<EOF
#include <sys/types.h>
#include <sys/socket.h>

int main() {
        socket(AF_INET, SOCK_STREAM, 0);
        perror("socket");

        return 0;
}
EOF
./sample
../box -q -S ../profiles/java -- ./sample
rm sample
