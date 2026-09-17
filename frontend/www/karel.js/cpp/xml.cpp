#include "xml.h"

#include <unistd.h>

#include <limits>

#include <expat.h>

#include "logging.h"
#include "util.h"

namespace xml {

Buffer::Buffer(int fd) : fd_(fd), buffer_(std::make_unique<char[]>(8192)) {}
Buffer::Buffer(Buffer&& other)
    : fd_(-1), buffer_(std::move(other.buffer_)), size_(other.size_) {
  std::swap(fd_, other.fd_);
}
Buffer::~Buffer() {
  if (fd_ == -1)
    return;
  Flush();
}

void Buffer::Flush() {
  WriteFileDescriptor(fd_, std::string_view(buffer_.get(), size_));
  size_ = 0;
}

Writer::Writer(int fd) : buffer_{fd} {}
Writer::~Writer() = default;

Writer::Element Writer::CreateElement(std::string_view name,
                                      std::optional<std::string_view> content) {
  return Writer::Element(this, name, std::move(content));
}

Writer::Element::Element(Writer* writer,
                         std::string_view name,
                         std::optional<std::string_view> content)
    : writer_(writer), name_(name), depth_(writer_->PushDepth()) {
  if (content)
    content_ = std::string(content.value());
  for (size_t i = 0; i < depth_; ++i)
    writer_->buffer_.Add('\t');
  writer_->buffer_.Add('<');
  writer_->buffer_.Add(name_);
}
Writer::Element::Element(Writer::Element&& other)
    : name_(std::move(other.name_)),
      depth_(other.depth_),
      open_(other.open_),
      content_(std::move(other.content_)) {
  std::swap(writer_, other.writer_);
}
Writer::Element::~Element() {
  if (!writer_)
    return;
  writer_->PopDepth();
  if (content_) {
    writer_->buffer_.Add('>');
    writer_->buffer_.Add(content_.value());
    writer_->buffer_.Add("</");
    writer_->buffer_.Add(name_);
    writer_->buffer_.Add(">\n");
    return;
  }
  if (open_) {
    writer_->buffer_.Add("/>\n");
    return;
  }
  for (size_t i = 0; i < depth_; ++i)
    writer_->buffer_.Add('\t');
  writer_->buffer_.Add("</");
  writer_->buffer_.Add(name_);
  writer_->buffer_.Add(">\n");
}

Writer::Element Writer::Element::CreateElement(
    std::string_view name,
    std::optional<std::string_view> content) {
  if (open_) {
    writer_->buffer_.Add(">\n");
    open_ = false;
  }
  return Writer::Element(writer_, name, std::move(content));
}

void Writer::Element::AddAttribute(std::string_view name,
                                   std::string_view value) {
  if (!open_)
    return;
  writer_->buffer_.Add(' ');
  writer_->buffer_.Add(name);
  writer_->buffer_.Add("=\"");
  writer_->buffer_.Add(value);
  writer_->buffer_.Add('"');
}

size_t Writer::PushDepth() {
  return depth_++;
}
void Writer::PopDepth() {
  --depth_;
}

Reader::Reader() = default;
Reader::~Reader() = default;

bool Reader::Parse(int fd, ParseCallback callback) {
  char buffer[4096];
  ssize_t bytes_read;

  State state{true, std::move(callback)};

  XML_Parser parser = XML_ParserCreate(nullptr);
  if (!parser)
    return false;
  XML_SetUserData(parser, &state);
  XML_SetElementHandler(parser, &Reader::StartElementHandler,
                        &Reader::EndElementHandler);

  while (state.success && (bytes_read = read(fd, buffer, sizeof(buffer))) > 0) {
    if (XML_Parse(parser, buffer, bytes_read, false) == XML_STATUS_ERROR) {
      LOG(ERROR) << "Parse error at line " << XML_GetCurrentLineNumber(parser)
                 << ": " << XML_ErrorString(XML_GetErrorCode(parser));
    }
  }
  if (state.success && XML_Parse(parser, buffer, 0, true) == XML_STATUS_ERROR) {
    LOG(ERROR) << "Parse error at line " << XML_GetCurrentLineNumber(parser)
               << ": " << XML_ErrorString(XML_GetErrorCode(parser));
  }

  XML_ParserFree(parser);
  return true;
}

// static
void Reader::StartElementHandler(void* user_data,
                                 const char* name,
                                 const char** attrs) {
  State& state = *reinterpret_cast<State*>(user_data);
  state.success &= state.callback(Element(name, attrs));
}

// static
void Reader::EndElementHandler(void* user_data, const char* name) {}

Reader::Element::Element(const char* name, const char** attrs)
    : name_(name), attrs_(attrs) {}
Reader::Element::Element(Element&&) = default;
Reader::Element::~Element() = default;

std::string_view Reader::Element::GetName() {
  return name_;
}

std::optional<std::string_view> Reader::Element::GetAttribute(
    std::string_view name,
    bool required) {
  for (size_t i = 0; attrs_[i]; i += 2) {
    if (name == attrs_[i])
      return std::make_optional<std::string_view>(attrs_[i + 1]);
  }
  if (required)
    LOG(ERROR) << "Failed to find " << name;
  return std::nullopt;
}

}  // namespace xml
