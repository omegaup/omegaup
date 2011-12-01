/*
 *	A Simple Sandbox for Moe
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

#if defined(CONFIG_BOX_KERNEL_AMD64) && !defined(CONFIG_BOX_USER_AMD64)
#include <asm/unistd_32.h>
#define NATIVE_NR_execve 59		/* 64-bit execve */
#else
#include <asm/unistd.h>
#define NATIVE_NR_execve __NR_execve
#endif

#define NONRET __attribute__((noreturn))
#define UNUSED __attribute__((unused))
#define ARRAY_SIZE(a) (int)(sizeof(a)/sizeof(a[0]))

static int filter_syscalls;		/* 0=off, 1=on */
static int timeout;			/* milliseconds */
static int wall_timeout;
static int extra_timeout;
static int pass_environ;
static int file_access;
static int verbose;
static int allow_threads;
static int allow_fork;
static int memory_limit;
static int output_limit;
static int stack_limit;
static char *redir_stdin, *redir_stdout, *redir_stderr, *redir_probin;
static char *set_cwd;
static int probin_fd;
static char cwd[4096];

static pid_t box_pid;
static int is_ptraced;
static volatile int timer_tick;
static struct timeval start_time;
static int ticks_per_sec;
static int exec_seen;
static int exec_remaining = 1;
static int partial_line;

static int mem_peak_kb;
static int total_ms, wall_ms;

static void die(char *msg, ...) NONRET;
static void sample_mem_peak(void);

#ifdef CONFIG_BOX_KERNEL_AMD64
typedef uint64_t arg_t;
#else
typedef uint32_t arg_t;
#endif

struct syscall_args {
  arg_t sys;
  arg_t arg1, arg2, arg3;
  arg_t result;
  struct user user;
};

/*** Thread support ***/

struct thread {
  pid_t pid;
  char last_filename[4096];
  struct syscall_args a;
  unsigned int sys_tick, last_act;
  arg_t last_sys;
  int active, waiting, sys_intercepted;
  int mem_fd;
};
static struct thread threads[100];
static int nthreads = 1;

/*** Meta-files ***/

static FILE *metafile;

static void
meta_open(const char *name)
{
  if (!strcmp(name, "-"))
    {
      metafile = stdout;
      return;
    }
  metafile = fopen(name, "w");
  if (!metafile)
    die("Failed to open metafile '%s'",name);
}

static void
meta_close(void)
{
  if (metafile && metafile != stdout)
    fclose(metafile);
}

static void __attribute__((format(printf,1,2)))
meta_printf(const char *fmt, ...)
{
  if (!metafile)
    return;

  va_list args;
  va_start(args, fmt);
  vfprintf(metafile, fmt, args);
  va_end(args);
}

static void
final_stats(struct rusage *rus)
{
  struct timeval total, now, wall;
  timeradd(&rus->ru_utime, &rus->ru_stime, &total);
  total_ms = total.tv_sec*1000 + total.tv_usec/1000;
  gettimeofday(&now, NULL);
  timersub(&now, &start_time, &wall);
  wall_ms = wall.tv_sec*1000 + wall.tv_usec/1000;

  meta_printf("time:%d.%03d\n", total_ms/1000, total_ms%1000);
  meta_printf("time-wall:%d.%03d\n", wall_ms/1000, wall_ms%1000);
  meta_printf("mem:%llu\n", (unsigned long long) mem_peak_kb * 1024);
}

/*** Messages and exits ***/

static void NONRET
box_exit(int rc)
{
  if (box_pid > 0)
    {
    
      if (threads[0].pid != -1)
        sample_mem_peak();
      
      int t;
      for (t = 0; t < nthreads; t++)
        {
          if (threads[t].pid == -1) continue;
          
          // aparently this is causing a bug in the server.
	  //if (is_ptraced)
	  //  ptrace(PTRACE_KILL, threads[t].pid);
	  kill(-threads[t].pid, SIGKILL);
	  kill(threads[t].pid, SIGKILL);
	}
	if (rc)
      meta_printf("killed:1\n");
    else
      meta_printf("status:OK\n");

      struct rusage rus;
      int p, stat;
      for (t = 0; t < nthreads; t++)
        {
          if (threads[t].pid == -1) continue;
          
          do
	    p = wait4(-1, &stat, __WALL, &rus);
          while (p < 0 && errno == EINTR);
          if (verbose >= 0 && p < 0)
	    fprintf(stderr, "UGH: Lost track of the process (%m)\n");
	  else if (p == box_pid)
	    final_stats(&rus);
        }
    }
  meta_close();
  exit(rc);
}

static void
flush_line(void)
{
  if (partial_line && verbose >= 0)
    fputc('\n', stderr);
  partial_line = 0;
}

/* Report an error of the sandbox itself */
static void NONRET __attribute__((format(printf,1,2)))
die(char *msg, ...)
{
  va_list args;
  va_start(args, msg);
  flush_line();
  char buf[1024];
  vsnprintf(buf, sizeof(buf), msg, args);
  meta_printf("status:XX\nmessage:%s\n", buf);
  if (verbose >= 0)
    {
      fputs(buf, stderr);
      fputc('\n', stderr);
    }
  box_exit(2);
}

/* Report an error of the program inside the sandbox */
static void NONRET __attribute__((format(printf,1,2)))
err(char *msg, ...)
{
  va_list args;
  va_start(args, msg);
  flush_line();
  if (msg[0] && msg[1] && msg[2] == ':' && msg[3] == ' ')
    {
      meta_printf("status:%c%c\n", msg[0], msg[1]);
      msg += 4;
    }
  char buf[1024];
  vsnprintf(buf, sizeof(buf), msg, args);
  meta_printf("message:%s\n", buf);
  if (verbose >= 0)
    {
      fputs(buf, stderr);
      fputc('\n', stderr);
    }
  box_exit(1);
}

/* Write a message, but only if in verbose mode */
static void __attribute__((format(printf,1,2)))
msg(char *msg, ...)
{
  va_list args;
  va_start(args, msg);
  if (verbose > 0)
    {
      int len = strlen(msg);
      if (len > 0)
        partial_line = (msg[len-1] != '\n');
      vfprintf(stderr, msg, args);
      fflush(stderr);
    }
  va_end(args);
}

static void *
xmalloc(size_t size)
{
  void *p = malloc(size);
  if (!p)
    die("Out of memory");
  return p;
}

/*** Syscall rules ***/

static const char * const syscall_names[] = {
#include "syscall-table.h"
};
#define NUM_SYSCALLS ARRAY_SIZE(syscall_names)
#define NUM_ACTIONS (NUM_SYSCALLS+64)

enum action {
  A_DEFAULT,		// Use the default action
  A_NO,			// Always forbid
  A_YES,		// Always permit
  A_FILENAME,		// Permit if arg1 is a known filename
  A_ACTION_MASK = 7,
  A_FORK = 8,		// Allow multiple processes, and monitor them all
  A_THREADS = 16,	// Allow multiple threads of execution
  A_NO_RETVAL = 32,	// Does not return a value
  A_SAMPLE_MEM = 64,	// Sample memory usage before the syscall
  A_READ_WRITE = 128,	// Can access the file also for writing
  // Must fit in a unsigned char
};

static unsigned char syscall_action[NUM_ACTIONS] = {
#define S(x) [__NR_##x]

    // Syscalls permitted for specific file names
    S(open) = A_FILENAME,
    S(access) = A_FILENAME,	
    S(stat) = A_FILENAME,
    S(lstat) = A_FILENAME,
    S(readlink) = A_FILENAME,
#ifndef CONFIG_BOX_USER_AMD64
    S(oldstat) = A_FILENAME,
    S(oldlstat) = A_FILENAME,
    S(stat64) = A_FILENAME,
    S(lstat64) = A_FILENAME,
#endif

    // Syscalls permitted always
    S(exit) = A_YES | A_SAMPLE_MEM,
    S(read) = A_YES,
    S(write) = A_YES,
    S(close) = A_YES,
    S(lseek) = A_YES,
    S(getpid) = A_YES,
    S(dup) = A_YES,
    S(brk) = A_YES | A_SAMPLE_MEM,
    S(getuid) = A_YES,
#ifdef __NR_getuid32
    S(getuid32) = A_YES,
#endif
    S(getgid) = A_YES,
#ifdef __NR_getgid32
    S(getgid32) = A_YES,
#endif
    S(geteuid) = A_YES,
#ifdef __NR_geteuid32
    S(geteuid32) = A_YES,
#endif
    S(getegid) = A_YES,
#ifdef __NR_getegid32
    S(getegid32) = A_YES,
#endif
    S(dup2) = A_YES,
    S(fstat) = A_YES,
    S(personality) = A_YES,
    S(readv) = A_YES,
    S(writev) = A_YES,
    S(getresuid) = A_YES,
#ifdef __NR_pread64
    S(pread64) = A_YES,
    S(pwrite64) = A_YES,
#else
    S(pread) = A_YES,
    S(pwrite) = A_YES,
#endif
    S(fcntl) = A_YES,
    S(mmap) = A_YES,
    S(munmap) = A_YES,
    S(ioctl) = A_YES,
    S(uname) = A_YES,
#ifdef __NR_olduname
    S(olduname) = A_YES,
#endif
#ifdef __NR_oldolduname
    S(oldolduname) = A_YES,
#endif
    S(gettid) = A_YES,
    S(set_thread_area) = A_YES,
    S(get_thread_area) = A_YES,
    S(set_tid_address) = A_YES,
    S(exit_group) = A_YES | A_SAMPLE_MEM,
#ifdef CONFIG_BOX_USER_AMD64
    S(arch_prctl) = A_YES,
#else
    S(oldfstat) = A_YES,
    S(ftruncate64) = A_YES,
    S(_llseek) = A_YES,
    S(fstat64) = A_YES,
    S(fcntl64) = A_YES,
    S(mmap2) = A_YES,
#endif

    // Multithreading support
    S(clone) = A_YES | A_THREADS,
    
    // Multiple process support
    S(fork) = A_YES | A_FORK,
    S(vfork) = A_YES | A_FORK,
    S(execve) = A_FILENAME | A_FORK | A_NO_RETVAL,
#ifdef __NR_waitpid
    S(waitpid) = A_YES | A_FORK,
#else
    S(wait4) = A_YES | A_FORK,
#endif
    
#undef S
};

static const char *
syscall_name(unsigned int id, char *buf)
{
  if (id < NUM_SYSCALLS && syscall_names[id])
    return syscall_names[id];
  else
    {
      sprintf(buf, "#%d", id);
      return buf;
    }
}

static int
syscall_by_name(char *name)
{
  unsigned int i;
  for (i=0; i<NUM_SYSCALLS; i++)
    if (syscall_names[i] && !strcmp(syscall_names[i], name))
      return i;
  if (name[0] == '#')
    name++;
  if (!*name)
    return -1;
  char *ep;
  unsigned long l = strtoul(name, &ep, 0);
  if (*ep)
    return -1;
  if (l >= NUM_ACTIONS)
    return NUM_ACTIONS;
  return l;
}

static int
set_syscall_action(char *a)
{
  char *sep = strchr(a, '=');
  enum action act = A_YES;
  if (sep)
    {
      *sep++ = 0;
      if (!strcmp(sep, "yes"))
	act = A_YES;
      else if (!strcmp(sep, "no"))
	act = A_NO;
      else if (!strcmp(sep, "file"))
	act = A_FILENAME;
      else if (!strcmp(sep, "noret"))
	act = A_YES | A_NO_RETVAL;
      else
	return 0;
    }

  int sys = syscall_by_name(a);
  if (sys < 0) {
    //die("Unknown syscall `%s'", a);
    return 1;
  }
  if (sys == NATIVE_NR_execve) {
    exec_remaining = -1;
  }
  if (sys >= NUM_ACTIONS)
    die("Syscall `%s' out of range", a);
  syscall_action[sys] = act;
  return 1;
}

/*** Path rules ***/

struct path_rule {
  char *path;
  enum action action;
  struct path_rule *next;
};

static struct path_rule default_path_rules[] = {
  { "/etc/ld.so.nohwcap", A_YES },
  { "/etc/ld.so.preload", A_YES },
  { "/etc/ld.so.cache", A_YES },
  { "/lib/", A_YES },
};

static struct path_rule *user_path_rules;
static struct path_rule **last_path_rule = &user_path_rules;

static int
set_path_action(char *a)
{
  char *sep = strchr(a, '=');
  enum action act = A_YES;
  if (sep)
    {
      *sep++ = 0;
      if (!strcmp(sep, "yes"))
	act = A_YES;
      else if (!strcmp(sep, "no"))
	act = A_NO;
      else if (!strcmp(sep, "rw"))
	act = A_YES | A_READ_WRITE;
      else
	return 0;
    }

  struct path_rule *r = xmalloc(sizeof(*r) + strlen(a) + 1);
  r->path = (char *)(r+1);
  strcpy(r->path, a);
  r->action = act;
  r->next = NULL;
  *last_path_rule = r;
  last_path_rule = &r->next;
  return 1;
}

static enum action
match_path_rule(struct path_rule *r, char *path)
{
  char *rr = r->path;
  while (*rr)
    if (*rr++ != *path++)
      {
	if (rr[-1] == '/' && !path[-1])
	  break;
	return A_DEFAULT;
      }
  if (rr > r->path && rr[-1] != '/' && *path)
    return A_DEFAULT;
  return r->action;
}

/*** Environment rules ***/

struct env_rule {
  char *var;			// Variable to match
  char *val;			// ""=clear, NULL=inherit
  int var_len;
  struct env_rule *next;
};

static struct env_rule *first_env_rule;
static struct env_rule **last_env_rule = &first_env_rule;

static struct env_rule default_env_rules[] = {
  { "LIBC_FATAL_STDERR_", "1" }
};

static int
set_env_action(char *a0)
{
  struct env_rule *r = xmalloc(sizeof(*r) + strlen(a0) + 1);
  char *a = (char *)(r+1);
  strcpy(a, a0);

  char *sep = strchr(a, '=');
  if (sep == a)
    return 0;
  r->var = a;
  if (sep)
    {
      *sep++ = 0;
      r->val = sep;
    }
  else
    r->val = NULL;
  *last_env_rule = r;
  last_env_rule = &r->next;
  r->next = NULL;
  return 1;
}

static int
match_env_var(char *env_entry, struct env_rule *r)
{
  if (strncmp(env_entry, r->var, r->var_len))
    return 0;
  return (env_entry[r->var_len] == '=');
}

static void
apply_env_rule(char **env, int *env_sizep, struct env_rule *r)
{
  // First remove the variable if already set
  int pos = 0;
  while (pos < *env_sizep && !match_env_var(env[pos], r))
    pos++;
  if (pos < *env_sizep)
    {
      (*env_sizep)--;
      env[pos] = env[*env_sizep];
      env[*env_sizep] = NULL;
    }

  // What is the new value?
  char *new;
  if (r->val)
    {
      if (!r->val[0])
	return;
      new = xmalloc(r->var_len + 1 + strlen(r->val) + 1);
      sprintf(new, "%s=%s", r->var, r->val);
    }
  else
    {
      pos = 0;
      while (environ[pos] && !match_env_var(environ[pos], r))
	pos++;
      if (!(new = environ[pos]))
	return;
    }

  // Add it at the end of the array
  env[(*env_sizep)++] = new;
  env[*env_sizep] = NULL;
}

static char **
setup_environment(void)
{
  int i;
  struct env_rule *r;

  // Link built-in rules with user rules
  for (i=ARRAY_SIZE(default_env_rules)-1; i >= 0; i--)
    {
      default_env_rules[i].next = first_env_rule;
      first_env_rule = &default_env_rules[i];
    }

  // Scan the original environment
  char **orig_env = environ;
  int orig_size = 0;
  while (orig_env[orig_size])
    orig_size++;

  // For each rule, reserve one more slot and calculate length
  int num_rules = 0;
  for (r = first_env_rule; r; r=r->next)
    {
      num_rules++;
      r->var_len = strlen(r->var);
    }

  // Create a new environment
  char **env = xmalloc((orig_size + num_rules + 1) * sizeof(char *));
  int size;
  if (pass_environ)
    {
      memcpy(env, environ, orig_size * sizeof(char *));
      size = orig_size;
    }
  else
    size = 0;
  env[size] = NULL;

  // Apply the rules one by one
  for (r = first_env_rule; r; r=r->next)
    apply_env_rule(env, &size, r);

  // Return the new env and pass some gossip
  if (verbose > 1)
    {
      fprintf(stderr, "Passing environment:\n");
      for (i=0; env[i]; i++)
	fprintf(stderr, "\t%s\n", env[i]);
    }
  return env;
}

/*** Low-level parsing of syscalls ***/

static int read_user_mem(pid_t pid, arg_t addr, char *buf, int len)
{
  int t;
  
  for (t = 0; t < nthreads; t++)
    if (threads[t].pid == pid) break;

  if (!threads[t].mem_fd)
    {
      char memname[64];
      sprintf(memname, "/proc/%d/mem", (int) pid);
      threads[t].mem_fd = open(memname, O_RDONLY);
      if (threads[t].mem_fd < 0)
	die("open(%s): %m", memname);
    }
  if (lseek64(threads[t].mem_fd, addr, SEEK_SET) < 0)
    die("lseek64(mem): %m");
  return read(threads[t].mem_fd, buf, len);
}

#ifdef CONFIG_BOX_KERNEL_AMD64

static void
get_syscall_args(pid_t pid, struct syscall_args *a, int is_exit)
{
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

  switch (a->user.regs.cs)
    {
    case 0x23:
      // 32-bit CPU mode => only 32-bit syscalls can be issued
      sys_type = 32;
      break;
    case 0x33:
      // 64-bit CPU mode
      if (read_user_mem(pid, a->user.regs.rip-2, (char *) &instr, 2) != 2)
	err("FO: Cannot read syscall instruction");
      switch (instr)
	{
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

  if (sys_type == 32)
    {
      a->arg1 = a->user.regs.rbx;
      a->arg2 = a->user.regs.rcx;
      a->arg3 = a->user.regs.rdx;
    }
  else
    {
      a->arg1 = a->user.regs.rdi;
      a->arg2 = a->user.regs.rsi;
      a->arg3 = a->user.regs.rdx;
    }
}

static void
set_syscall_args(pid_t pid, struct syscall_args *a)
{
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

  if (sys_type == 32)
    {
      a->user.regs.rbx = a->arg1;
      a->user.regs.rcx = a->arg2;
      a->user.regs.rdx = a->arg3;
    }
  else
    {
      a->user.regs.rdi = a->arg1;
      a->user.regs.rsi = a->arg2;
      a->user.regs.rdx = a->arg3;
    }


  if (ptrace(PTRACE_SETREGS, pid, NULL, &a->user) < 0)
    die("ptrace(PTRACE_SETREGS): %m");
}

static void
sanity_check(void)
{
}

#else

static void
get_syscall_args(pid_t pid, struct syscall_args *a, int is_exit UNUSED)
{
  if (ptrace(PTRACE_GETREGS, pid, NULL, &a->user) < 0)
    {
      int t;
      for(t = 0; t < nthreads; t++)
        {
    	  if(threads[t].pid == pid) break;
        }
        
      if( !(threads[t].sys_tick & 1) && threads[t].last_sys == __NR_futex )
        {
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

/*** Syscall checks ***/

static void
get_filename(pid_t pid, arg_t addr, char *namebuf, int bufsize)
{
  char *p, *end, *pr, *pw;
  int dirs = 0;
  
  p = end = namebuf;
  do
    {
      if (p >= end)
	{
	  int remains = PAGE_SIZE - (addr & (PAGE_SIZE-1));
	  int l = namebuf + bufsize - end;
	  if (l > remains)
	    l = remains;
	  if (!l)
	    err("FA: Access to file with name too long");
	  remains = read_user_mem(pid, addr, end, l);
	  if (remains < 0)
	    {
	      perror("read");
	      die("read(mem[%d]): %m", pid);
	    }
	  if (!remains)
	    err("FA: Access to file with name out of memory");
	  end += remains;
	  addr += remains;
	}
    }
  while (*p++);
  
  if (namebuf[0] != '/')
    {
      pr = namebuf + strlen(namebuf);
      pw = pr + 2;
      
      while(pr >= namebuf)
        *pw-- = *pr--;
        
      namebuf[0] = '.';
      namebuf[1] = '/';
    }
  
  pr = pw = namebuf;
  
  while(*pr)
    {
      if (*pr == '/') dirs++;
      if ((!strcmp(pr, "/..") || !strncmp(pr, "/../", 4)) && dirs >= 2)
        {
          dirs -= 2;
          pw--;
          while(*pw != '/') pw--;
          pr += 3;
          continue;
        }
      else if (!strncmp(pr, "/./", 3))
        {
          pr += 2;
          continue;
        }
      else if (!strncmp(pr, "/.", 3))
        {
          *pw++ = '/';
          pr += 2;
          continue;
        }
      *(pw++) = *(pr++);
    }
  *pw = '\0';
}

static void
valid_filename(pid_t pid, arg_t addr, arg_t flags)
{
  char namebuf[4096], *nameptr;
  int i;
  struct path_rule *r;

  if (!file_access)
    err("FA: File access forbidden");
  if (file_access >= 9)
    return;
  
  enum action act = A_DEFAULT;
    
  get_filename(pid, addr, namebuf, sizeof(namebuf));
  
  // change the current directory into ./ for write rules
  if(!strncmp(cwd, namebuf, strlen(cwd)))
    {
	  nameptr = namebuf + strlen(cwd) - 1;
	  *nameptr = '.';
    }
  else
    {
	  nameptr = namebuf;
    }

  msg("<%s> ", nameptr);
  if (file_access >= 3)
    return;
    
  // ".." anywhere in the path is forbidden
  if (strstr(nameptr, ".."))
    err("FA: Forbidden access to file `%s'", nameptr);

  // Scan user rules
  for (r = user_path_rules; r && !act; r=r->next)
    act = match_path_rule(r, nameptr);

  // Scan built-in rules
  if (file_access >= 2)
    for (i=0; i<ARRAY_SIZE(default_path_rules) && !act; i++)
      act = match_path_rule(&default_path_rules[i], nameptr);
  
  // Only allow write / read-write access if explicitly permitted
  if ((flags & O_ACCMODE) != O_RDONLY && (act & A_READ_WRITE) == 0)
    err("FA: Forbidden write access to file `%s'", nameptr);
  else
    act &= ~A_READ_WRITE;
  
  // Everything in current directory is permitted
  if (nameptr[0] != '/')
    return;
    
  if (act != A_YES)
    err("FA: Forbidden access to file `%s'", nameptr);
}

// Check syscall. If invalid, return -1, otherwise return the action mask.
static int
valid_syscall(pid_t pid, struct syscall_args *a)
{
  unsigned int sys = a->sys;
  unsigned int act = (sys < NUM_ACTIONS) ? syscall_action[sys] : A_DEFAULT;
    
  if (act & A_THREADS)
    {
      if (!allow_threads)
        act = A_NO;
    }
    
  if (act & A_FORK)
    {
      if (!allow_fork)
        act = A_NO;
    }

  switch (act & A_ACTION_MASK)
    {
    case A_YES:
      return act;
    case A_NO:
      return -1;
    case A_FILENAME:
      if (sys == __NR_open)
        {
          valid_filename(pid, a->arg1, a->arg2);
        }
      else
        {
          valid_filename(pid, a->arg1, 0);
        }
      return act;
    default: ;
    }

  switch (sys)
    {
    case __NR_kill:
      if (a->arg1 == (arg_t) box_pid)
	{
	  meta_printf("exitsig:%d\n", (int) a->arg2);
	  err("SG: Committed suicide by signal %d", (int) a->arg2);
	}
      return -1;
    case __NR_tgkill:
      if (a->arg1 == (arg_t) box_pid && a->arg2 == (arg_t) box_pid)
	{
	  meta_printf("exitsig:%d\n", (int) a->arg3);
	  err("SG: Committed suicide by signal %d", (int) a->arg3);
	}
      return -1;
    default:
      return -1;
    }
}

// special syscall interception and handling.
static int
syscall_intercept(int pid, int sys, struct syscall_args *a, int entry)
{
  static char namebuf[4096];

  switch(sys)
  {
    case NATIVE_NR_execve:
      if (entry == 0)
        {
          if (exec_remaining == 0)
            {
              return -1;
            }
          exec_remaining--;
          return 1;
        }
      break;
    case __NR_setrlimit:
      if (entry == 0)
        {
	  a->sys = __NR_getuid;
	  set_syscall_args(pid, a);
	  return 1;
        }
      else
        {
          a->sys = __NR_setrlimit;
          a->result = -1;
          errno = EPERM;
          set_syscall_args(pid, a);
        }
      break;
    case __NR_mkdir:
      if (entry == 0)
        {
	  a->sys = __NR_getuid;
	  set_syscall_args(pid, a);
	  return 1;
        }
      else
        {
          a->sys = __NR_mkdir;
          a->result = -1;
          errno = EACCES;
          set_syscall_args(pid, a);
        }
      break;
#ifdef __NR_socketcall
    case __NR_socketcall:
      if (entry == 0)
        {
          if (a->arg1 = SYS_SOCKET)
            {
              a->sys = __NR_getuid;
              set_syscall_args(pid, a);
              return 1;
            }
          else
            {
	      return -1;
            }
        }
      else
        {
          a->sys = __NR_socketcall;
          a->result = -1;
          errno = EACCES;
          set_syscall_args(pid, a);
        }
      break;
#else
    case __NR_socket:	
      if (entry == 0)
        {
          a->sys = __NR_getuid;
          set_syscall_args(pid, a);
          return 1;
        }
      else
        {
          a->sys = __NR_socket;
          a->result = -1;
          errno = EACCES;
          set_syscall_args(pid, a);
        }
      break;
#endif
    case __NR_open:
      if (entry == 0)
        {
          get_filename(pid, a->arg1, namebuf, sizeof(namebuf));
          
          if (redir_probin && !strcmp(namebuf, "./data.in"))
            {
              a->sys = __NR_getuid;
              set_syscall_args(pid, a);
              return 1;
            }
        }
      else
        {
          a->result = probin_fd;
          if (a->result == -1)
            {
              errno = EACCES;
            }
          a->sys = __NR_open;
          set_syscall_args(pid, a);
          probin_fd = -1;
        }
      break;
    case __NR_clone:
      if (entry == 0)
        {
          if ((a->arg1 & CLONE_THREAD) == 0)
            {
              return -1;
            }
          
          a->arg1 |= CLONE_PTRACE;
          
          set_syscall_args(pid, a);
          
          return 1;
        }
      else
        {
        }
      break;
    default: ;
  }
  
  return 0;
}

static void
signal_alarm(int unused UNUSED)
{
  /* Time limit checks are synchronous, so we only schedule them there. */
  timer_tick = 1;
  alarm(1);
}

static void
signal_int(int unused UNUSED)
{
  /* Interrupts are fatal, so no synchronization requirements. */
  meta_printf("exitsig:%d\n", SIGINT);
  err("SG: Interrupted");
}

#define PROC_BUF_SIZE 4096
static void
read_proc_file(char *buf, char *name, int *fdp)
{
  int c;

  if (!*fdp)
    {
      sprintf(buf, "/proc/%d/%s", (int) box_pid, name);
      *fdp = open(buf, O_RDONLY);
      if (*fdp < 0)
	die("open(%s): %m", buf);
    }
  lseek(*fdp, 0, SEEK_SET);
  if ((c = read(*fdp, buf, PROC_BUF_SIZE-1)) < 0)
    die("read on /proc/%d/%s: %m", box_pid, name);
  if (c >= PROC_BUF_SIZE-1)
    die("/proc/%d/%s too long", box_pid, name);
  buf[c] = 0;
}

static void
check_timeout(void)
{
  if (wall_timeout)
    {
      struct timeval now, wall;
      int wall_ms;
      gettimeofday(&now, NULL);
      timersub(&now, &start_time, &wall);
      wall_ms = wall.tv_sec*1000 + wall.tv_usec/1000;
      if (wall_ms > wall_timeout)
        err("TO: Time limit exceeded (wall clock)");
      if (verbose > 1)
        fprintf(stderr, "[wall time check: %d msec]\n", wall_ms);
    }
  if (timeout)
    {
      char buf[PROC_BUF_SIZE], *x;
      int utime, stime, ms;
      static int proc_stat_fd;
      read_proc_file(buf, "stat", &proc_stat_fd);
      x = buf;
      while (*x && *x != ' ')
	x++;
      while (*x == ' ')
	x++;
      if (*x++ != '(')
	die("proc stat syntax error 1");
      while (*x && (*x != ')' || x[1] != ' '))
	x++;
      while (*x == ')' || *x == ' ')
	x++;
      if (sscanf(x, "%*c %*d %*d %*d %*d %*d %*d %*d %*d %*d %*d %d %d", &utime, &stime) != 2)
	die("proc stat syntax error 2");
      ms = (utime + stime) * 1000 / ticks_per_sec;
      if (verbose > 1)
	fprintf(stderr, "[time check: %d msec]\n", ms);
      if (ms > timeout && ms > extra_timeout)
	err("TO: Time limit exceeded");
    }
}

static void
sample_mem_peak(void)
{
  /*
   *  We want to find out the peak memory usage of the process, which is
   *  maintained by the kernel, but unforunately it gets lost when the
   *  process exits (it is not reported in struct rusage). Therefore we
   *  have to sample it whenever we suspect that the process is about
   *  to exit.
   */
  char buf[PROC_BUF_SIZE], *x;
  static int proc_status_fd;
  read_proc_file(buf, "status", &proc_status_fd);

  x = buf;
  while (*x)
    {
      char *key = x;
      while (*x && *x != ':' && *x != '\n')
	x++;
      if (!*x || *x == '\n')
	break;
      *x++ = 0;
      while (*x == ' ' || *x == '\t')
	x++;

      char *val = x;
      while (*x && *x != '\n')
	x++;
      if (!*x)
	break;
      *x++ = 0;

      if (!strcmp(key, "VmPeak"))
	{
	  int peak = atoi(val);
	  if (peak > mem_peak_kb)
	    mem_peak_kb = peak;
	}
    }

  if (verbose > 1)
    msg("[mem-peak: %u KB]\n", mem_peak_kb);
}

static void
trace_thread(int pid, int t)
{
  if (t != 0)
    {
      int ins;
      
      for (ins = 0; ins < nthreads; ins++)
        {
          if (threads[ins].pid == -1)
            {
              break;
            }
          else if(threads[ins].pid == pid)
            {
              if (ptrace(PTRACE_SYSCALL, threads[ins].pid, 0, 0) < 0)
		  die("ptrace: %m");
              return;
            }
        }
      
      if (ins == ARRAY_SIZE(threads))
        die("too many open threads.");
	    
      threads[ins].pid = pid;
      threads[ins].mem_fd = 0;
      threads[ins].active = threads[ins].waiting = threads[ins].sys_tick = threads[ins].last_sys = 0;

      t = ins;
      
      if(ins == nthreads)
        nthreads++;
    }
  
  if (ptrace(PTRACE_SETOPTIONS, threads[t].pid, NULL, (void *) (PTRACE_O_TRACESYSGOOD | PTRACE_O_TRACEFORK | PTRACE_O_TRACEVFORK)) < 0)
    die("ptrace(PTRACE_SETOPTIONS): %m");
  if (ptrace(PTRACE_SYSCALL, threads[t].pid, 0, 0) < 0)
    die("ptrace: %m");
}

static void
boxkeeper(void)
{
  int syscall_count = (filter_syscalls ? 0 : 1);
  char current_filename[4096];
  struct sigaction sa;
  int t, entered = 0;

  is_ptraced = 1;
  
  threads[0].pid = box_pid;

  bzero(&sa, sizeof(sa));
  sa.sa_handler = signal_int;
  sigaction(SIGINT, &sa, NULL);

  gettimeofday(&start_time, NULL);
  ticks_per_sec = sysconf(_SC_CLK_TCK);
  if (ticks_per_sec <= 0)
    die("Invalid ticks_per_sec!");

  if (timeout || wall_timeout)
    {
      sa.sa_handler = signal_alarm;
      sigaction(SIGALRM, &sa, NULL);
      alarm(1);
    }

  for(;;)
    {
      struct rusage rus;
      int stat;
      pid_t p;
      if (timer_tick)
	{
	  check_timeout();
	  timer_tick = 0;
	}
      p = wait4(-1, &stat, WUNTRACED | __WALL, &rus);
      if (p < 0)
	{
	  if (errno == EINTR)
	    continue;
	  die("wait4: %m");
	}
      msg("[%d] ", p);
      for (t = 0; t < nthreads; t++)
        if (threads[t].pid == p) break;
      if (t >= nthreads && !(WIFSTOPPED(stat) && WSTOPSIG(stat) == SIGSTOP))
	die("wait4: unknown pid %d exited!", p);
      if (WIFEXITED(stat))
	{
	  msg("= [exit]\n");
	  threads[t].pid = -1;
	  if (threads[t].mem_fd)
	    close(threads[t].mem_fd);
	  
	  if (p != box_pid)
	    {
	      continue;
	    }
	  
	  final_stats(&rus);
	  meta_printf("syscall-count:%d\n", syscall_count);
	  if (WEXITSTATUS(stat))
	    {
	      if (syscall_count)
		{
		  meta_printf("exitcode:%d\n", WEXITSTATUS(stat));
		  err("RE: Exited with error status %d", WEXITSTATUS(stat));
		}
	      else
		{
		  // Internal error happened inside the child process and it has been already reported.
		  box_exit(2);
		}
	    }
	  if (timeout && total_ms > timeout)
	    err("TO: Time limit exceeded");
	  if (wall_timeout && wall_ms > wall_timeout)
	    err("TO: Time limit exceeded (wall clock)");
	  flush_line();
	  if (verbose >= 0)
	    {
		  fprintf(stderr, "OK (%d.%03d sec real, %d.%03d sec wall, %d MB, %d syscalls)\n",
	         total_ms/1000, total_ms%1000,
	         wall_ms/1000, wall_ms%1000,
	         (mem_peak_kb + 1023) / 1024,
	         syscall_count);
	     }
	  box_exit(0);
	}
      if (WIFSIGNALED(stat))
	{
	  threads[t].pid = -1;
	  meta_printf("exitsig:%d\n", WTERMSIG(stat));
	  final_stats(&rus);
	  err("SG: Caught fatal signal %d%s", WTERMSIG(stat), (syscall_count ? "" : " during startup"));
	}
      if (WIFSTOPPED(stat))
	{
	  int sig = WSTOPSIG(stat);
	  if (sig == SIGTRAP)
	    {
	      if (verbose > 2)
		msg("[ptrace status %08x] ", stat);
	      static int stop_count;
	      if (!stop_count++)		/* Traceme request */
		msg(">> Traceme request caught\n");
	      else if(!allow_fork)
		err("SG: Breakpoint");
	      if (p == box_pid)
	        ptrace(PTRACE_SYSCALL, box_pid, 0, 0);
	      else
	        trace_thread(p, t);
	    }
	  else if (sig == (SIGTRAP | 0x80))
	    {
	      if (verbose > 2)
		msg("[ptrace status %08x] ", stat);

	      if (++threads[t].sys_tick & 1)		/* Syscall entry */
		{
		  char namebuf[32];
		  int act, forbidden = 0;

		  threads[t].sys_intercepted = 0;
		  get_syscall_args(threads[t].pid, &threads[t].a, 0);
		  arg_t sys = threads[t].a.sys;
		  
		  if (entered)
		    {
		      entered = 0;
		      msg("= pending...\n");
		    }
		  entered++;
		  
		  msg(">> Syscall %-12s (%08jx,%08jx,%08jx) ", syscall_name(sys, namebuf), (intmax_t) threads[t].a.arg1, (intmax_t) threads[t].a.arg2, (intmax_t) threads[t].a.arg3);
		  if (!exec_seen)
		    {
		      msg("[master] ");
		      if (sys == NATIVE_NR_execve)
			exec_seen = 1;
		    }
		  else if ((act = valid_syscall(threads[t].pid, &threads[t].a)) >= 0)
		    {
		      if ((act & A_ACTION_MASK) == A_FILENAME)
		        {
		          get_filename(threads[t].pid, threads[t].a.arg1, threads[t].last_filename, sizeof(threads[t].last_filename));
		        }
		        
		      threads[t].sys_intercepted = syscall_intercept(threads[t].pid, sys, &threads[t].a, 0);
		      
		      if (threads[t].sys_intercepted == -1)
		        {
		          forbidden = 1;
		        }

		      threads[t].last_act = act;
		      syscall_count++;
		      if (act & A_SAMPLE_MEM)
		        {
				  sample_mem_peak();
				  if (memory_limit && mem_peak_kb > memory_limit)
				    err("ML: Memory Limit Exceeded");
				}
		    }
		  else
		    {
		      forbidden = 1;
		    }

		  if (forbidden)
		    {
		      /*
		       * Unfortunately, PTRACE_KILL kills _after_ the syscall completes,
		       * so we have to change it to something harmless (e.g., an undefined
		       * syscall) and make the program continue.
		       */
		      threads[t].a.sys = ~(arg_t)0;
		      set_syscall_args(threads[t].pid, &threads[t].a);
		      err("FO: Forbidden syscall %s", syscall_name(sys, namebuf));
		    }
		  else if (sys == NATIVE_NR_execve)
		    {
		      threads[t].last_act &= ~A_FILENAME;
		    }
		  threads[t].last_sys = sys;
		}
	      else					/* Syscall return */
		{
		  entered--;
		  
		  get_syscall_args(threads[t].pid, &threads[t].a, 1);
		  
		  if(threads[t].sys_intercepted)
		    {
		      syscall_intercept(threads[t].pid, threads[t].last_sys, &threads[t].a, 1);
		    }
		  
		  if (threads[t].a.sys == ~(arg_t)0)
		    {
		      /* Some syscalls (sigreturn et al.) do not return a value */
		      if (!(threads[t].last_act & A_NO_RETVAL))
			err("XX: Syscall does not return, but it should");
		    }
		  else
		    {
		      if (threads[t].a.sys != threads[t].last_sys)
			err("XX: Mismatched syscall entry/exit");
		    }
		    
		  if ((threads[t].last_act & A_ACTION_MASK) == A_FILENAME)
		    {
		      get_filename(threads[t].pid, threads[t].a.arg1, current_filename, sizeof(current_filename));
		      
		      if (strcmp(threads[t].last_filename, current_filename))
		        {
			  err("XX: File access race condition attack detected");
		        }
		    }
		    
		  if (errno == EBADF)
		    {
		      err("XX: File Descriptor guessing attack detected");
		    }
		    
		  if (threads[t].last_act & A_NO_RETVAL)
		    msg("= ?\n");
		  else
		    msg("= %jd\n", (intmax_t) threads[t].a.result);
		}
	      ptrace(PTRACE_SYSCALL, threads[t].pid, 0, 0);
	    }
	  else if (sig == SIGSTOP)
	    {   
	      msg(">> SIGSTOP\n");
	      
	      trace_thread(p, t);
	    }
	  else if (sig != SIGXCPU && sig != SIGXFSZ)
	    {
	      msg(">> Signal %d\n", sig);
	      sample_mem_peak();			/* Signal might be fatal, so update mem-peak */
	      ptrace(PTRACE_SYSCALL, threads[t].pid, 0, sig);
	    }
	  else if (sig == SIGXFSZ)
	    {
	      meta_printf("exitsig:%d\n", sig);
	      err("OL: Output Limit Exceeded");
	    }
	  else
	    {
	      meta_printf("exitsig:%d\n", sig);
	      err("SG: Received signal %d", sig);
	    }
	}
      else
	die("wait4: unknown status %x, giving up!", stat);
    }
}

static void
box_inside(int argc, char **argv)
{
  struct rlimit rl;
  char *args[argc+1];

  memcpy(args, argv, argc * sizeof(char *));
  args[argc] = NULL;
  if (set_cwd && chdir(set_cwd))
    die("chdir: %m");
  if (redir_stdin)
    {
      close(0);
      if (open(redir_stdin, O_RDONLY) != 0)
	die("open(\"%s\"): %m", redir_stdin);
    }
  if (redir_stdout)
    {
      close(1);
      if (open(redir_stdout, O_WRONLY | O_CREAT | O_TRUNC, 0666) != 1)
	die("open(\"%s\"): %m", redir_stdout);
    }
  if (redir_stderr)
    {
      close(2);
      if (open(redir_stderr, O_WRONLY | O_CREAT | O_TRUNC, 0666) != 2)
	die("open(\"%s\"): %m", redir_stderr);
    }
  else
    dup2(1, 2);
  
  if (redir_probin)
    {
      int fd;
      if ((fd = open(redir_probin, O_RDONLY)) == -1)
        die("open(\"%s\"): %m", redir_probin);
      
      if (dup2(fd, probin_fd) == -1)
        die("dup2: %m");
      
      close(fd);
    }
  setpgrp();

  if (memory_limit)
    {
      rl.rlim_cur = rl.rlim_max = (memory_limit + 32*1024) * 1024;
      if (setrlimit(RLIMIT_AS, &rl) < 0)
	die("setrlimit(RLIMIT_AS): %m");
    }
  
  if (output_limit)
    {
      rl.rlim_cur = rl.rlim_max = output_limit * 1024;
      if (setrlimit(RLIMIT_FSIZE, &rl) < 0)
    die("setrlimit(RLIMIT_FSIZE): %m");
    }

  rl.rlim_cur = rl.rlim_max = (stack_limit ? (rlim_t)stack_limit * 1024 : RLIM_INFINITY);
  if (setrlimit(RLIMIT_STACK, &rl) < 0)
    die("setrlimit(RLIMIT_STACK): %m");

  rl.rlim_cur = rl.rlim_max = 64;
  if (setrlimit(RLIMIT_NOFILE, &rl) < 0)
    die("setrlimit(RLIMIT_NOFILE): %m");

  char **env = setup_environment();
  if (filter_syscalls)
    {
      if (ptrace(PTRACE_TRACEME) < 0)
	die("ptrace(PTRACE_TRACEME): %m");
      /* Trick: Make sure that we are stopped until the boxkeeper wakes up. */
      raise(SIGSTOP);
    }
  execve(args[0], args, env);
  die("execve(\"%s\"): %m", args[0]);
}

static void
usage(void)
{
  fprintf(stderr, "Invalid arguments!\n");
  printf("\
Usage: box [<options>] -- <command> <arguments>\n\
\n\
Options:\n\
-a <level>\tSet file access level (0=none, 1=cwd, 2=/etc,/lib,..., 3=whole fs,\n\
\t\t9=no checks; needs -f)\n\
-c <dir>\tChange directory to <dir> first\n\
-C\t\tAllow sys_clone system call to enable multithreaded applications\n\
\t\t(and runtimes like Java, Mono and Ruby)\n\
-e\t\tInherit full environment of the parent process\n\
-E <var>\tInherit the environment variable <var> from the parent process\n\
-E <var>=<val>\tSet the environment variable <var> to <val>; unset it if <var> is empty\n\
-f\t\tFilter system calls\n\
-F\t\tAllow sys_fork/sys_vfork system call to enable multiprocess applications\n\
\t\t(like compilers)\n\
-i <file>\tRedirect stdin from <file>\n\
-k <size>\tLimit stack size to <size> KB (default: 0=unlimited)\n\
-m <size>\tLimit address space to <size> KB\n\
-M <file>\tOutput process information to <file> (name:value)\n\
-o <file>\tRedirect stdout to <file>\n\
-O <size>\tLimit output file size to <size> KB (default: 0=unlimited)\n\
-p <path>\tPermit access to the specified path (or subtree if it ends with a `/')\n\
-p <path>=<act>\tDefine action for the specified path (<act>=yes/no/rw)\n\
-P <file>\tRedirect <file> as the magic file \"data.in\"\n\
-q\t\tBe quiet (the opposite of -v)\n\
-r <file>\tRedirect stderr to <file>\n\
-s <sys>\tPermit the specified syscall (be careful)\n\
-s <sys>=<act>\tDefine action for the specified syscall (<act>=yes/no/file/noret)\n\
-S <file>\tRead a script from <file> with one commandline parameter pair per line\n\
-t <time>\tSet run time limit (seconds, fractions allowed)\n\
-T\t\tAllow syscalls for measuring run time\n\
-v\t\tBe verbose (use multiple times for even more verbosity)\n\
-w <time>\tSet wall clock time limit (seconds, fractions allowed)\n\
-x <time>\tSet extra timeout, before which a timing-out program is not yet killed,\n\
\t\tso that its real execution time is reported (seconds, fractions allowed)\n\
");
  exit(2);
}

void
process_option (int c, char *argument, int enable_script)
{
  FILE *scriptf;
  char scriptbuffer[1024], *arg;
  
  switch (c)
    {
      case 'a':
	file_access = atol(argument);
	break;
      case 'c':
	set_cwd = argument;
	strcpy(cwd, argument);
	break;
      case 'C':
        allow_threads = 1;
        break;
      case 'e':
	pass_environ = 1;
	break;
      case 'E':
	if (!set_env_action(argument))
	  usage();
	break;
      case 'f':
	filter_syscalls++;
	break;
      case 'F':
	allow_fork = 1;
	break;
      case 'k':
	stack_limit = atol(argument);
	break;
      case 'i':
	redir_stdin = argument;
	break;
      case 'm':
	memory_limit = atol(argument);
	break;
      case 'M':
	meta_open(argument);
	break;
      case 'o':
	redir_stdout = argument;
	break;
	  case 'O':
	output_limit = atol(argument);
	break;
      case 'p':
	if (!set_path_action(argument))
	  usage();
	break;
      case 'P':
	redir_probin = argument;
	break;
	  case 'q':
	verbose--;
	break;
      case 'r':
	redir_stderr = argument;
	break;
      case 's':
	if (!set_syscall_action(argument))
	  usage();
	break;
      case 'S':
        if (!enable_script)
          usage();
        scriptf = fopen(argument, "r");
        if (!scriptf)
          usage();
        
        while (fgets(scriptbuffer, sizeof(scriptbuffer)-1, scriptf))
          {
            arg = scriptbuffer + strlen(scriptbuffer) - 1;
            while (scriptbuffer < arg && (*arg == ' ' || *arg == '\r' || *arg == '\n' || *arg == '\t')) *arg-- = '\0';
            
            arg = scriptbuffer;
            while (*arg == ' ' || *arg == '\r' || *arg == '\n' || *arg == '\t') *arg++ = '\0';
            
            if (strlen(arg) == 0 || arg[0] != '-') continue;
            
            c = arg[1];
            
            arg += 2;
            while (*arg == ' ' || *arg == '\r' || *arg == '\n' || *arg == '\t') *arg++ = '\0';
            
            process_option(c, arg, 0);
          }
        fclose(scriptf);
        break;
      case 't':
	timeout = 1000*atof(argument);
	break;
      case 'T':
	syscall_action[__NR_times] = A_YES;
	break;
      case 'v':
	verbose++;
	break;
      case 'w':
        wall_timeout = 1000*atof(argument);
	break;
      case 'x':
	extra_timeout = 1000*atof(argument);
	break;
      default:
	usage();
    }
}

int
main(int argc, char **argv)
{
  int c, random_fd;
  uid_t uid;
  
  getcwd(cwd, sizeof(cwd));
  
  while ((c = getopt(argc, argv, "a:c:CeE:fFi:k:m:M:o:O:p:P:qr:s:S:t:Tvw:x:")) >= 0)
    process_option(c, optarg, 1);
  if (optind >= argc)
    usage();
  
  random_fd = open("/dev/urandom", O_RDONLY);
  if (random_fd == -1)
    {
      probin_fd = time(0);
    }
  else
    {
      read(random_fd, &probin_fd, 1);
      close(random_fd);      
    }
  probin_fd = 100 + probin_fd & 0x7F;

  sanity_check();
  uid = geteuid();
  if (setreuid(uid, uid) < 0)
    die("setreuid: %m");
  box_pid = fork();
  if (box_pid < 0)
    die("fork: %m");
  if (!box_pid)
    box_inside(argc-optind, argv+optind);
  else
    boxkeeper();
  die("Internal error: fell over edge of the world");
}
