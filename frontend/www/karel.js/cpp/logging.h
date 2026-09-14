#ifndef LOGGING_H_
#define LOGGING_H_

#include <cstring>
#include <ostream>
#include <sstream>

enum LogLevel : uint32_t { DEBUG, INFO, WARN, ERROR, FATAL };

std::ostream& operator<<(std::ostream& o, LogLevel level);

#define LOG(level) logging::ScopedLogger(level, __FILE__, __LINE__)
#define PLOG(level) \
  logging::ScopedLogger(level, __FILE__, __LINE__, strerror(errno))

namespace logging {

void Init(int fd, LogLevel min_log_level);

class ScopedLogger : std::ostream {
 public:
  ScopedLogger(LogLevel level,
               const char* filename,
               size_t line,
               const char* trailer = nullptr);
  ~ScopedLogger();

  template <typename T>
  std::ostream& operator<<(const T& t) {
    return buffer_ << t;
  }

 private:
  const LogLevel level_;
  const char* const trailer_;
  std::ostringstream buffer_;
};

}  // namespace logging

#endif  // LOGGING_H_
