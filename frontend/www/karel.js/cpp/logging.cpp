#include "logging.h"

#include <sys/time.h>
#include <unistd.h>

#include <iomanip>

#include "util.h"

namespace logging {

namespace {

int g_logging_fd = 2;
LogLevel g_min_log_level = INFO;

}  // namespace

void Init(int fd, LogLevel min_log_level) {
  g_logging_fd = fd;
  g_min_log_level = min_log_level;
}

ScopedLogger::ScopedLogger(LogLevel level,
                           const char* filename,
                           size_t line,
                           const char* trailer)
    : level_(level), trailer_(trailer) {
  timeval tv;
  gettimeofday(&tv, nullptr);
  time_t t = tv.tv_sec;
  struct tm local_time;
  localtime_r(&t, &local_time);
  struct tm* tm_time = &local_time;
  *this << "[" << level << " ";
  *this << std::setfill('0') << std::setw(4) << (1900 + tm_time->tm_year) << "-"
        << std::setw(2) << (1 + tm_time->tm_mon) << "-" << std::setw(2)
        << tm_time->tm_mday << "T" << std::setw(2) << tm_time->tm_hour << ":"
        << std::setw(2) << tm_time->tm_min << ":" << std::setw(2)
        << tm_time->tm_sec << "." << std::setw(6) << tv.tv_usec;
  *this << " " << filename << "(" << line << ")] ";
}

ScopedLogger::~ScopedLogger() {
  if (trailer_)
    *this << ": " << trailer_;
  buffer_ << std::endl;

  if (g_logging_fd != -1 && level_ >= g_min_log_level) {
    const std::string str = buffer_.str();
    // Perform best-effort writing into the log file.
    ignore_result(::write(g_logging_fd, str.c_str(), str.size()));
  }

  if (level_ == LogLevel::FATAL)
    abort();
}

}  // namespace logging

std::ostream& operator<<(std::ostream& o, LogLevel level) {
  switch (level) {
    case LogLevel::DEBUG:
      return o << "DBUG";
    case LogLevel::INFO:
      return o << "INFO";
    case LogLevel::WARN:
      return o << "WARN";
    case LogLevel::ERROR:
      return o << "EROR";
    case LogLevel::FATAL:
      return o << "FATL";
  }

  return o;
}
