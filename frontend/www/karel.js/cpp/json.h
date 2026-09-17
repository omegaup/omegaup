#include <iosfwd>
#include <memory>
#include <optional>
#include <string>
#include <string_view>
#include <utility>
#include <vector>

#include "macros.h"

namespace json {

enum class Type { INT, LIST, STRING };

class IntValue;
class ListValue;
class StringValue;

class Value {
 public:
  virtual ~Value() = default;
  virtual Type GetType() const = 0;

  const IntValue& AsInt() const;
  const ListValue& AsList() const;
  const StringValue& AsString() const;

 protected:
  friend std::ostream& operator<<(std::ostream& os, const Value& value);
  virtual void dump(std::ostream& os) const = 0;
};

class IntValue : public Value {
 public:
  explicit IntValue(int32_t value) : value_(value) {}

  Type GetType() const override { return Type::INT; }

	const int32_t value() const { return value_; }

 private:
  void dump(std::ostream& os) const override;

  int32_t value_;

  DISALLOW_COPY_AND_ASSIGN(IntValue);
};

class ListValue : public Value {
 public:
  explicit ListValue(std::vector<std::unique_ptr<Value>> value)
      : value_(std::move(value)) {}

  Type GetType() const override { return Type::LIST; }

  const std::vector<std::unique_ptr<Value>>& value() const { return value_; }

 private:
  void dump(std::ostream& os) const override;

  std::vector<std::unique_ptr<Value>> value_;

  DISALLOW_COPY_AND_ASSIGN(ListValue);
};

class StringValue : public Value {
 public:
  explicit StringValue(std::string_view value) : value_(value) {}

  Type GetType() const override { return Type::STRING; }

  std::string_view value() const { return value_; }

 private:
  void dump(std::ostream& os) const override;

  std::string value_;

  DISALLOW_COPY_AND_ASSIGN(StringValue);
};

std::optional<std::unique_ptr<Value>> Parse(
    std::string_view json);

std::ostream& operator<<(std::ostream& os, const Value& value);

}  // namespace
