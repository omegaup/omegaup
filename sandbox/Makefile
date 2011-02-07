# Makefile for MO-Eval sandbox
# (c) 2008--2010 Martin Mares <mj@ucw.cz>

DIRS+=box
PROGS+=box

BOX_CFLAGS=
ifdef CONFIG_BOX_KERNEL_AMD64
BOX_CFLAGS += -m64
endif

box: box.o
box.o: syscall-table.h

box: LDFLAGS+=$(BOX_CFLAGS)
box.o: CFLAGS+=$(BOX_CFLAGS) -O2

syscall-table.h: mk-syscall-table
	sh $^ >$@ $(CFLAGS) $(BOX_CFLAGS)

clean:
	rm -f *.o
	rm -f box
	rm -f syscall-table.h
	rm -f test-sys32*
	rm -f test-sys64*

box-tests: $(addprefix test-sys,32-int80 64-int80 32-syscall 64-syscall 32-sysenter 64-sysenter)

.PHONY: box-tests

test-sys32-int80: test-syscalls.c
	$(CC) -m32 $^ -o $@ -DTEST_INT80

test-sys64-int80: test-syscalls.c
	$(CC) -m64 $^ -o $@ -DTEST_INT80

test-sys32-syscall: test-syscalls.c
	$(CC) -m32 $^ -o $@ -DTEST_SYSCALL_32

test-sys64-syscall: test-syscalls.c
	$(CC) -m64 $^ -o $@ -DTEST_SYSCALL_64

test-sys32-sysenter: test-syscalls.c
	$(CC) -m32 $^ -o $@ -DTEST_SYSENTER_32

test-sys64-sysenter: test-syscalls.c
	$(CC) -m64 $^ -o $@ -DTEST_SYSENTER_32 #sic
