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

#define NONRET __attribute__((noreturn))
#define UNUSED __attribute__((unused))
#define ARRAY_SIZE(a) (int)(sizeof(a)/sizeof(a[0]))

static int timeout;			/* milliseconds */
static int wall_timeout;
static int extra_timeout;
static int pass_environ;
static int verbose;
static int memory_limit;
static int output_limit;
static int stack_limit;
static char *redir_stdin, *redir_stdout, *redir_stderr, *redir_probin;
static char *set_cwd;
static int probin_fd;
static char cwd[4096];
static int allow_fork;
static int shutting_down;

static pid_t box_pid;
static volatile int timer_tick;
static struct timeval start_time;
static int ticks_per_sec;
static int partial_line;

static int mem_peak_kb;
static int total_ms, wall_ms;

static void die(char *msg, ...) NONRET;
static int sample_mem_peak(void);

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
  shutting_down = 1;

  if (box_pid > 0)
    {
    
      sample_mem_peak();
      
	  kill(-box_pid, SIGKILL);
	  kill(box_pid, SIGKILL);
	if (rc)
      meta_printf("killed:1\n");
    else
      meta_printf("status:OK\n");

      struct rusage rus;
      int p, stat;
          
          do
	    p = wait4(box_pid, &stat, WUNTRACED | __WALL, &rus);
          while (p < 0 && errno == EINTR);
	    final_stats(&rus);
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

/*** Environment rules ***/

struct env_rule {
  char *var;			/* Variable to match */
  char *val;			/* ""=clear, NULL=inherit */
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
  /* First remove the variable if already set */
  int pos = 0;
  while (pos < *env_sizep && !match_env_var(env[pos], r))
    pos++;
  if (pos < *env_sizep)
    {
      (*env_sizep)--;
      env[pos] = env[*env_sizep];
      env[*env_sizep] = NULL;
    }

  /* What is the new value? */
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

  /* Add it at the end of the array */
  env[(*env_sizep)++] = new;
  env[*env_sizep] = NULL;
}

static char **
setup_environment(void)
{
  int i;
  struct env_rule *r;

  /* Link built-in rules with user rules */
  for (i=ARRAY_SIZE(default_env_rules)-1; i >= 0; i--)
    {
      default_env_rules[i].next = first_env_rule;
      first_env_rule = &default_env_rules[i];
    }

  /* Scan the original environment */
  char **orig_env = environ;
  int orig_size = 0;
  while (orig_env[orig_size])
    orig_size++;

  /* For each rule, reserve one more slot and calculate length */
  int num_rules = 0;
  for (r = first_env_rule; r; r=r->next)
    {
      num_rules++;
      r->var_len = strlen(r->var);
    }

  /* Create a new environment */
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

  /* Apply the rules one by one */
  for (r = first_env_rule; r; r=r->next)
    apply_env_rule(env, &size, r);

  /* Return the new env and pass some gossip */
  if (verbose > 1)
    {
      fprintf(stderr, "Passing environment:\n");
      for (i=0; env[i]; i++)
	fprintf(stderr, "\t%s\n", env[i]);
    }
  return env;
}

#ifdef CONFIG_BOX_KERNEL_AMD64

static void
sanity_check(void)
{
}

#else

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
static int
read_proc_file(char *buf, char *name, int *fdp)
{
  int c;

  if (!*fdp)
    {
      sprintf(buf, "/proc/%d/%s", (int) box_pid, name);
      *fdp = open(buf, O_RDONLY);
      if (*fdp < 0) {
	if (shutting_down) return 1;
        die("open(%s): %m", buf);
      }
    }
  lseek(*fdp, 0, SEEK_SET);
  if ((c = read(*fdp, buf, PROC_BUF_SIZE-1)) < 0) {
    if (shutting_down) return 1;
    die("read on /proc/%d/%s: %m", box_pid, name);
  }
  if (c >= PROC_BUF_SIZE-1) {
    if (shutting_down) return 1;
    die("/proc/%d/%s too long", box_pid, name);
  }
  buf[c] = 0;

  return 0;
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

static int
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
  if (read_proc_file(buf, "status", &proc_status_fd))
    return 1;

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

  return 0;
}

static void
boxkeeper(void)
{
  struct sigaction sa;

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
      p = wait4(box_pid, &stat, WUNTRACED | __WALL, &rus);
      if (p < 0)
	{
	  if (errno == EINTR)
	    continue;
	  die("wait4: %m");
	}
      msg("[%d] ", p);
      if (p != box_pid)
	die("wait4: unknown pid %d exited!", p);
      if (WIFEXITED(stat))
	{
	  msg("= [exit]\n");
	  
	  final_stats(&rus);
	  meta_printf("syscall-count:%d\n", -1);
	  if (WEXITSTATUS(stat))
	    {
	      meta_printf("exitcode:%d\n", WEXITSTATUS(stat));
	      err("RE: Exited with error status %d", WEXITSTATUS(stat));
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
	         -1);
	     }
	  box_exit(0);
	}
      if (WIFSIGNALED(stat))
	{
	  meta_printf("exitsig:%d\n", WTERMSIG(stat));
	  final_stats(&rus);
	  err("SG: Caught fatal signal %d", WTERMSIG(stat));
	}
      if (WIFSTOPPED(stat))
	{
	  int sig = WSTOPSIG(stat);
	  if (sig == SIGSTOP)
	    {   
	      msg(">> SIGSTOP\n");
	    }
	  else if (sig != SIGXCPU && sig != SIGXFSZ)
	    {
	      msg(">> Signal %d\n", sig);
	      sample_mem_peak();			/* Signal might be fatal, so update mem-peak */
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

  if (!allow_fork)
    {
      rl.rlim_cur = rl.rlim_max = 1;
      if (setrlimit(RLIMIT_NPROC, &rl) < 0)
        die("setrlimit(RLIMIT_NPROC): %m");
    }

  rl.rlim_cur = rl.rlim_max = (stack_limit ? (rlim_t)stack_limit * 1024 : RLIM_INFINITY);
  if (setrlimit(RLIMIT_STACK, &rl) < 0)
    die("setrlimit(RLIMIT_STACK): %m");

  rl.rlim_cur = rl.rlim_max = 64;
  if (setrlimit(RLIMIT_NOFILE, &rl) < 0)
    die("setrlimit(RLIMIT_NOFILE): %m");

  char **env = setup_environment();
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
	/* ignored */
	break;
      case 'c':
	set_cwd = argument;
	strcpy(cwd, argument);
	break;
      case 'C':
        /* ignored */
        break;
      case 'e':
	pass_environ = 1;
	break;
      case 'E':
	if (!set_env_action(argument))
	  usage();
	break;
      case 'f':
	/* ignored */
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
	/* ignored */
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
	/* ignored */
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
	/* ignored */
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
  probin_fd = 100 + (probin_fd & 0x7F);

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
