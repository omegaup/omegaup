#include "util.h"

#include <stdarg.h>
#include <unistd.h>

#include <fstream>
#include <utility>

#include "karel.h"
#include "logging.h"

ScopedFD::ScopedFD(int fd) : fd_(fd) {}

ScopedFD::~ScopedFD() {
  reset();
}

ScopedFD::ScopedFD(ScopedFD&& fd) : fd_(kInvalidFd) {
  std::swap(fd_, fd.fd_);
}

ScopedFD& ScopedFD::operator=(ScopedFD&& fd) {
  reset();
  std::swap(fd_, fd.fd_);
  return *this;
}

int ScopedFD::get() const {
  return fd_;
}

int ScopedFD::release() {
  int ret = kInvalidFd;
  std::swap(ret, fd_);
  return ret;
}

void ScopedFD::reset(int fd) {
  std::swap(fd, fd_);
  if (fd == kInvalidFd)
    return;
  close(fd);
}

ScopedMmap::ScopedMmap(void* ptr, size_t size) : ptr_(ptr), size_(size) {}

ScopedMmap::~ScopedMmap() {
  reset();
}

void* ScopedMmap::get() {
  return ptr_;
}

const void* ScopedMmap::get() const {
  return ptr_;
}

void ScopedMmap::reset(void* ptr, size_t size) {
  std::swap(ptr, ptr_);
  std::swap(size, size_);
  if (ptr == MAP_FAILED)
    return;
  if (munmap(ptr, size))
    PLOG(ERROR) << "Failed to unmap memory";
}

std::string StringPrintf(const char* format, ...) {
  char path[4096];

  va_list ap;
  va_start(ap, format);
  ssize_t ret = vsnprintf(path, sizeof(path), format, ap);
  va_end(ap);

  return std::string(path, ret);
}

bool WriteFileDescriptor(int fd, std::string_view str) {
  const char* ptr = str.data();
  size_t remaining = str.size();
  ssize_t bytes_written;

  while (remaining && (bytes_written = write(fd, ptr, remaining)) > 0) {
    ptr += bytes_written;
    remaining -= bytes_written;
  }

  return remaining == 0;
}

template <>
std::optional<uint32_t> ParseString(std::string_view str) {
  if (str == "INFINITO")
    return karel::kInfinity;
  uint32_t value;
  std::istringstream is{std::string(str)};
  if (!is || !(is >> value))
    return std::nullopt;
  return value;
}
