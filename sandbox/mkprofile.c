/*
 *	Make the profile scripts
 *
 *	(c) 2001--2010 Martin Mares <mj@ucw.cz>
 *	(c) 2010--2011 Luis Hector Chavez <lhchavez@lhchavez.com>
 */

#define _LARGEFILE64_SOURCE
#define _GNU_SOURCE

#include <errno.h>
#include <stdio.h>
#include <fcntl.h>
#include <stdlib.h>
#include <string.h>
#include <stdarg.h>
#include <stdint.h>
#include <unistd.h>
#include <getopt.h>
#include <time.h>
#include <sched.h>
#include <sys/wait.h>
#include <sys/user.h>
#include <sys/time.h>
#include <sys/ptrace.h>
#include <sys/signal.h>
#include <sys/sysinfo.h>
#include <sys/resource.h>
#include <sys/utsname.h>
#include <linux/net.h>
#include <linux/ptrace.h>

#ifdef __amd64
#define CONFIG_BOX_KERNEL_AMD64
#define CONFIG_BOX_USER_AMD64
#endif

#define NONRET __attribute__((noreturn))
#define ARRAY_SIZE(a) (int)(sizeof(a)/sizeof(a[0]))

#if defined(CONFIG_BOX_KERNEL_AMD64) && !defined(CONFIG_BOX_USER_AMD64)
#include <asm/unistd_32.h>
#define NATIVE_NR_execve 59		/* 64-bit execve */
#else
#include <asm/unistd.h>
#define NATIVE_NR_execve __NR_execve
#endif

static const char * const syscall_names[] = {
#include "syscall-table.h"
};
#define NUM_SYSCALLS ARRAY_SIZE(syscall_names)
#define NUM_ACTIONS (NUM_SYSCALLS+64)

#ifdef CONFIG_BOX_KERNEL_AMD64
typedef uint64_t arg_t;
#else
typedef uint32_t arg_t;
#endif

int mem_fd = 0;
int box_pid = 0;
int sys_tick = 0;
int last_sys = 0;
int first_execve = 1;

char java_filename[10240];
int java_envc = 0;
char java_envv[100][10240];

struct syscall_args {
  arg_t sys;
  arg_t arg1, arg2, arg3;
  arg_t result;
  struct user user;
};

void make_config() {
	FILE *f;
	FILE *conf;
	int i;
	int found = 0;
	char buffer[1024];
	
	f = fopen("profiles/java", "w");
	fprintf(f, 
"-f\n\
-C\n");
	
	for(i = 0; i < java_envc; i++) {
		fprintf(f, "-E %s\n", java_envv[i]);
	}
	
	fprintf(f,
"-a 2\n\
\n\
-s mprotect\n\
-s get_robust_list\n\
-s set_robust_list\n\
-s futex\n\
-s rt_sigaction\n\
-s rt_sigprocmask\n\
-s rt_sigreturn=noret\n\
-s ugetrlimit\n\
-s getdents64\n\
-s clock_getres\n\
-s clock_gettime\n\
-s gettimeofday\n\
-s setrlimit\n\
-s socketcall\n\
-s getdents\n\
-s mkdir\n\
-s sched_getaffinity\n\
-s sched_yield\n\
-s getcwd\n\
-s madvise\n\
-s kill\n\
\n\
-p /dev/random\n\
-p /dev/urandom\n\
-p /proc/self/exe\n\
-p /proc/self/maps\n\
-p /proc/stat\n\
-p /proc/meminfo\n\
-p /sys/devices/system/cpu\n\
-p /usr/lib/\n\
-p /usr/java/\n\
-p /usr/share/\n\
-p /etc/nsswitch.conf\n\
-p /etc/passwd\n\
-p /etc/localtime\n\
-p /tmp/\n\
-p /home/\n\
-p ./\n");

	fclose(f);
	
	f = fopen("profiles/javac", "w");
	fprintf(f,
"-f\n\
-C\n");
	
	for(i = 0; i < java_envc; i++) {
		fprintf(f, "-E %s\n", java_envv[i]);
	}
	
	fprintf(f,
"-a 2\n\
-F\n\
\n\
-s mprotect\n\
-s get_robust_list\n\
-s set_robust_list\n\
-s futex\n\
-s rt_sigaction\n\
-s rt_sigprocmask\n\
-s rt_sigreturn=noret\n\
-s ugetrlimit\n\
-s getrlimit\n\
-s getdents64\n\
-s clock_getres\n\
-s clock_gettime\n\
-s gettimeofday\n\
-s setrlimit\n\
-s socketcall\n\
-s getdents\n\
-s mkdir\n\
-s sched_getaffinity\n\
-s sched_yield\n\
-s getcwd\n\
-s madvise\n\
-s kill\n\
\n\
-p /dev/random\n\
-p /dev/urandom\n\
-p /proc/self/exe\n\
-p /proc/self/maps\n\
-p /proc/stat\n\
-p /proc/meminfo\n\
-p /sys/devices/system/cpu\n\
-p /usr/lib/\n\
-p /usr/java/\n\
-p /usr/share/\n\
-p /etc/nsswitch.conf\n\
-p /etc/passwd\n\
-p /etc/localtime\n\
-p /tmp/\n\
-p /home/\n\
-p ./=rw\n");

	fclose(f);

	conf = fopen("../runner/omegaup.conf", "r");
	f = fopen(".omegaup.conf", "w");
	
	if(conf) {
		while(fgets(buffer, sizeof(buffer), conf)) {
			if(strncmp(buffer, "java.compile.path", 17) == 0) {
				found = 1;
				fprintf(f, "java.compile.path = %s\n", java_filename);
			} else {
				fwrite(buffer, sizeof(char), sizeof(buffer), f);
			}
		}
	
		fclose(conf);
	}
	
	if(!found) {
		fprintf(f, "java.compile.path = %s\n", java_filename);
	}
	
	fclose(f);
	
	rename(".omegaup.conf", "../runner/omegaup.conf");
}

static void NONRET __attribute__((format(printf,1,2))) err(char *msg, ...) {
	va_list args;
	va_start(args, msg);
	char buf[1024];
	vsnprintf(buf, sizeof(buf), msg, args);
	printf("status:XX\nmessage:%s\n", buf);
	//meta_printf("status:XX\nmessage:%s\n", buf);
	exit(2);
}

static void NONRET __attribute__((format(printf,1,2))) die(char *msg, ...) {
	va_list args;
	va_start(args, msg);
	char buf[1024];
	vsnprintf(buf, sizeof(buf), msg, args);
	printf("status:XX\nmessage:%s\n", buf);
	//meta_printf("status:XX\nmessage:%s\n", buf);
	exit(2);
}

static int
read_user_mem(pid_t pid, arg_t addr, char *buf, int len) {
	if (!mem_fd) {
		char memname[64];
		sprintf(memname, "/proc/%d/mem", (int) pid);
		mem_fd = open(memname, O_RDONLY);
		if (mem_fd < 0)
			die("open(%s): %m", memname);
	}
	if (lseek64(mem_fd, addr, SEEK_SET) < 0)
		die("lseek64(mem): %m");
	return read(mem_fd, buf, len);
}


static void
read_string(pid_t pid, arg_t addr, char *namebuf, int bufsize) {
	char *p, *end, *pr, *pw;
	int dirs = 0;

	p = end = namebuf;
	do {
		if (p >= end) {
			int remains = PAGE_SIZE - (addr & (PAGE_SIZE-1));
			int l = namebuf + bufsize - end;
			if (l > remains)
				l = remains;
			if (!l)
				err("FA: Access to file with name too long");
			remains = read_user_mem(pid, addr, end, l);
			if (remains < 0) {
				perror("read");
				die("read(mem[%d]): %m", pid);
			}
			if (!remains)
				err("FA: Access to file with name out of memory");
			end += remains;
			addr += remains;
		}
	} while (*p++);
}

#ifdef CONFIG_BOX_KERNEL_AMD64

static void
get_syscall_args(pid_t pid, struct syscall_args *a, int is_exit) {
	if (ptrace(PTRACE_GETREGS, pid, NULL, &a->user) < 0)
		die("ptrace(PTRACE_GETREGS, %d): %m", pid);
	a->sys = a->user.regs.orig_rax;
	a->result = a->user.regs.rax;

	/*
	 *  CAVEAT: We have to check carefully that this is a real 64-bit syscall.
	 *  We test whether the process runs in 64-bit mode, but surprisingly this
	 *  is not enough: a 64-bit process can still issue the INT 0x80 instruction
	 *  which performs a 32-bit syscall. Currently, the only known way how to
	 *  detect this situation is to inspect the instruction code (the kernel
	 *  keeps a syscall type flag internally, but it is not accessible from
	 *  user space). Hopefully, there is no instruction whose suffix is the
	 *  code of the SYSCALL instruction. Sometimes, one would wish the
	 *  instruction codes to be unique even when read backwards :)
	 */

	if (is_exit)
		return;

	int sys_type;
	uint16_t instr;

	switch (a->user.regs.cs) {
		case 0x23:
			// 32-bit CPU mode => only 32-bit syscalls can be issued
			sys_type = 32;
			break;
		case 0x33:
			// 64-bit CPU mode
			if (read_user_mem(pid, a->user.regs.rip-2, (char *) &instr, 2) != 2)
				err("FO: Cannot read syscall instruction");
			switch (instr) {
				case 0x050f:
					break;
				case 0x80cd:
					err("FO: Forbidden 32-bit syscall in 64-bit mode");
				default:
					err("XX: Unknown syscall instruction %04x", instr);
			}
			sys_type = 64;
			break;
		default:
			err("XX: Unknown code segment %04jx", (intmax_t) a->user.regs.cs);
	}

	#ifdef CONFIG_BOX_USER_AMD64
	if (sys_type != 64)
		err("FO: Forbidden %d-bit mode syscall", sys_type);
	#else
	if (sys_type != (exec_seen ? 32 : 64))
		err("FO: Forbidden %d-bit mode syscall", sys_type);
	#endif

	if (sys_type == 32) {
		a->arg1 = a->user.regs.rbx;
		a->arg2 = a->user.regs.rcx;
		a->arg3 = a->user.regs.rdx;
	} else {
		a->arg1 = a->user.regs.rdi;
		a->arg2 = a->user.regs.rsi;
		a->arg3 = a->user.regs.rdx;
	}
}

static void
set_syscall_args(pid_t pid, struct syscall_args *a) {
  a->user.regs.orig_rax = a->sys;
  a->user.regs.rax = a->result;

  int sys_type;

  switch (a->user.regs.cs)
    {
    case 0x23:
      // 32-bit CPU mode => only 32-bit syscalls can be issued
      sys_type = 32;
      break;
    case 0x33:
      sys_type = 64;
      break;
    }

	if (sys_type == 32) {
		a->user.regs.rbx = a->arg1;
		a->user.regs.rcx = a->arg2;
		a->user.regs.rdx = a->arg3;
	} else  {
		a->user.regs.rdi = a->arg1;
		a->user.regs.rsi = a->arg2;
		a->user.regs.rdx = a->arg3;
	}

	if (ptrace(PTRACE_SETREGS, pid, NULL, &a->user) < 0)
		die("ptrace(PTRACE_SETREGS): %m");
}

static void
sanity_check(void) {
}

#else

static void
get_syscall_args(pid_t pid, struct syscall_args *a, int is_exit UNUSED) {
	if (ptrace(PTRACE_GETREGS, pid, NULL, &a->user) < 0) {
		if( !(sys_tick & 1) && last_sys == __NR_futex ) {
			// for some weird reason, futex sometimes does not return as we'd
			// expect. apparently the kernel decides to give control back to the
			// process ignoring ptrace, leaving the sandbox in a weird state.
			// sooooooo let's just pretend nothing ever happened and be happy.
			a->result = 0;
			return;
		}
		die("ptrace(PTRACE_GETREGS, %d): %m", pid);
	}
	a->sys = a->user.regs.orig_eax;
	a->arg1 = a->user.regs.ebx;
	a->arg2 = a->user.regs.ecx;
	a->arg3 = a->user.regs.edx;
	a->result = a->user.regs.eax;
}

static void
set_syscall_args(pid_t pid, struct syscall_args *a)
{
  a->user.regs.orig_eax = a->sys;
  a->user.regs.ebx = a->arg1;
  a->user.regs.ecx = a->arg2;
  a->user.regs.edx = a->arg3;
  a->user.regs.eax = a->result;
  if (ptrace(PTRACE_SETREGS, pid, NULL, &a->user) < 0)
    die("ptrace(PTRACE_SETREGS): %m");
}

static void
sanity_check(void)
{
#if !defined(CONFIG_BOX_ALLOW_INSECURE)
  struct utsname uts;
  if (uname(&uts) < 0)
    die("uname() failed: %m");

  if (!strcmp(uts.machine, "x86_64"))
    die("Running 32-bit sandbox on 64-bit kernels is inherently unsafe. Please get a 64-bit version.");
#endif
}

#endif

int outside() {
	struct rusage rus;
	int stat;
	pid_t p;
	struct syscall_args a;

	for(;;) {
		p = wait4(-1, &stat, WUNTRACED | __WALL, &rus);
	
		if(WIFEXITED(stat)) {
			kill(p, SIGKILL);
			break;
		} else if(WIFSIGNALED(stat)) {
		} else if(WIFSTOPPED(stat)) {
			int sig = WSTOPSIG(stat);
			
			if(sig == SIGTRAP) {			
			} else if(sig == (SIGTRAP | 0x80)) {
				
				if( !(sys_tick++ & 1) ) {
					get_syscall_args(p, &a, 0);
					arg_t sys = a.sys;
					
					if(sys == NATIVE_NR_execve) {
						if(first_execve) {
							first_execve = 0;
						} else {
							read_string(p, a.arg1, java_filename, sizeof(java_filename));
						
							arg_t envp;
							arg_t tmp;
						
							for(envp = a.arg3; read_user_mem(p, envp, (char *)&tmp, sizeof(tmp)) && tmp; envp += sizeof(char *)) {
								read_string(p, tmp, java_envv[java_envc++], sizeof(java_envv[0]));
							}
							
							make_config();
						}
					}
				} else {
					//get_syscall_args(p, &a, 1);
					//arg_t sys = a.sys;
					//printf("%d exit syscall %d %s\n", p, sys, syscall_names[sys]);
				}
			} else if(sig == SIGSTOP) {
				if (ptrace(PTRACE_SETOPTIONS, p, NULL, (void *) (PTRACE_O_TRACESYSGOOD | PTRACE_O_TRACEFORK | PTRACE_O_TRACEVFORK)) < 0)
					die("ptrace(PTRACE_SETOPTIONS): %m");
			} else {
			}
			
			if (ptrace(PTRACE_SYSCALL, p, 0, 0) < 0)
				die("ptrace: %m");
		} else {
			kill(p, SIGKILL);
			break;
		}
	}
}

int inside(char **args) {
	char *env[] = { NULL };
	
	ptrace(PTRACE_TRACEME);
	raise(SIGSTOP);
	
	freopen("/dev/null", "w", stdout);
	freopen("/dev/null", "w", stderr);
	
	execve(args[0], args, env);
	perror("execve died");
	exit(0);
}

int main(int argc, char *argv[]) {
	int pid = fork();
	char *java[] = { "/usr/bin/java" };
	
	if(pid == -1)
		perror("fork");
	else if(pid == 0)
		inside(java);
	else {
		outside();
		exit(0);
	}
}
