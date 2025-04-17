Sandbox is a *very* modified version of Moeval (the sandbox used in the IOI), written by Martin Mares. In essence, Sandbox is a debugger that uses the Linux ptrace system call to stop a process every time it attempts to make a system call. Sandbox examines this system call to see if it's harmless or dangerous, and it can:

1. Allow the syscall to proceed normally.
2. Replace the called syscall (for example, replace setrlimit with getuid, which is very harmless) and then make the process believe there was an error when calling the syscall. This is used to pretend there's no network (all socket calls return -1).
3. Kill the process if the syscall is VERY evil.

Some of the modifications made to Sandbox that aren't in Moeval are:

1. Support for syscall mangling
2. Support for multiple threads.
3. A verbose mode to examine which syscalls were made.
4. Path normalization (to be able to say, for example, that ./ is also writable).
5. Reading parameters from a file (to create profiles for different compilers/interpreters).
6. Many, many, many, many small improvements.
7. A version of Moeval that doesn't use ptrace (this one is cross-platform, but doesn't offer as much security)

The only component that needs to execute Sandbox at any point is Runner.