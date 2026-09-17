#include "json.h"

#include <ostream>

#include "logging.h"

namespace json {

namespace {

std::optional<std::unique_ptr<Value>> ParseValue(const char** ptr,
                                                 const char* const end);

std::optional<std::unique_ptr<Value>> ParseList(const char** ptr,
                                                const char* const end) {
  if (**ptr != '[') {
    LOG(ERROR) << "Invalid list entry";
    return std::nullopt;
  }
  (*ptr)++;
  std::vector<std::unique_ptr<Value>> list;
  while (*ptr != end) {
    auto entry = ParseValue(ptr, end);
    if (!entry)
      return std::nullopt;
    list.emplace_back(std::move(entry.value()));
    if (*ptr == end) {
      LOG(ERROR) << "Unterminated list";
      return std::nullopt;
    }
    switch (**ptr) {
      case ',':
        (*ptr)++;
        continue;
        break;
      case ']':
        (*ptr)++;
        return std::optional<std::unique_ptr<Value>>(
            std::make_unique<ListValue>(std::move(list)));
      default:
        LOG(ERROR) << "Invalid list separator " << **ptr;
        return std::nullopt;
    }
  }
  LOG(ERROR) << "Invalid list state";
  return std::nullopt;
}

std::optional<std::unique_ptr<Value>> ParseString(const char** ptr,
                                                  const char* const end) {
  if (**ptr != '"') {
    LOG(ERROR) << "Invalid string entry";
    return std::nullopt;
  }
  const char* string_begin = ++(*ptr);
  for (size_t length = 0; *ptr != end; length++, (*ptr)++) {
    switch (**ptr) {
      case '"':
        (*ptr)++;
        return std::optional<std::unique_ptr<Value>>(
            std::make_unique<StringValue>(
                std::string_view(string_begin, length)));
      case '\\':
        (*ptr)++;
        break;
    }
  }
  LOG(ERROR) << "Invalid string state";
  return std::nullopt;
}

std::optional<std::unique_ptr<Value>> ParseInt(const char** ptr,
                                               const char* const end) {
  int32_t sign = 1;
  if (**ptr == '-') {
    sign = -1;
    (*ptr)++;
  }
  int32_t value = 0;
  for (; *ptr != end; (*ptr)++) {
    if ('0' <= **ptr && **ptr <= '9') {
      value = 10 * value + (**ptr - '0');
    } else {
      break;
    }
  }
  return std::optional<std::unique_ptr<Value>>(
      std::make_unique<IntValue>(sign * value));
}

std::optional<std::unique_ptr<Value>> ParseValue(const char** ptr,
                                                 const char* const end) {
  if (*ptr == end)
    return std::nullopt;

  switch (**ptr) {
    case ' ':
    case '\t':
    case '\n':
    case '\r':
      (*ptr)++;
      return ParseValue(ptr, end);
    case '[':
      return ParseList(ptr, end);
    case '"':
      return ParseString(ptr, end);
    default:
      return ParseInt(ptr, end);
  }
}

}  // namespace

const IntValue& Value::AsInt() const {
  if (GetType() != Type::INT)
    LOG(FATAL) << "Cannot cast value as int";
  return *static_cast<const IntValue*>(this);
}

const ListValue& Value::AsList() const {
  if (GetType() != Type::LIST)
    LOG(FATAL) << "Cannot cast value as list";
  return *static_cast<const ListValue*>(this);
}

const StringValue& Value::AsString() const {
  if (GetType() != Type::STRING)
    LOG(FATAL) << "Cannot cast value as string";
  return *static_cast<const StringValue*>(this);
}

void IntValue::dump(std::ostream& os) const {
  os << value_;
}

void ListValue::dump(std::ostream& os) const {
  os << '[';
  bool print_comma = false;
  for (const auto& entry : value_) {
    if (print_comma)
      os << ',';
    else
      print_comma = true;
    os << *entry;
  }
}

void StringValue::dump(std::ostream& os) const {
  os << '"' << value_ << '"';
}

std::optional<std::unique_ptr<Value>> Parse(std::string_view json) {
  const char* ptr = json.data();
  const char* end = ptr + json.size();
  auto result = ParseValue(&ptr, end);
  if (ptr != end) {
    LOG(ERROR) << "Unconsumed state";
    return std::nullopt;
  }
  return result;
}

std::ostream& operator<<(std::ostream& os, const Value& value) {
  value.dump(os);
  return os;
}

}  // namespace json
