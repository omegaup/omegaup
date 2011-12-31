#!/bin/bash

gcc -x c -o .sample - <<EOF
#include <sys/types.h>
#include <sys/socket.h>

int main() {
        socket(AF_INET, SOCK_STREAM, 0);
        perror("socket");

        return 0;
}
EOF

echo -n "Opening a socket                            "
if [ "`./.sample 2>&1`" == "socket: Success" ]; then
	echo "[OK]"
else
	echo "[FAIL]"
fi

echo -n "Opening a socket (sandboxed)                "
if [ "`./box -q -S profiles/java -- ./.sample 2>&1`" == "socket: Permission denied" ]; then
	echo "[OK]"
else
	echo "[FAIL]"
fi

gcc -x c -o .sample - <<EOF
#include <unistd.h>
#include <stdio.h>

int main() {
	sleep(1);

        return 0;
}
EOF

echo -n "Time Limit Exceeded                         "

if [ "`./box -S profiles/c -t 1 -- ./.sample 2>&1 | grep 'Forbidden syscall' | wc -l`" -eq 1 ]; then
	echo "[OK]"
else
	echo "[FAIL]"
fi

rm .sample
