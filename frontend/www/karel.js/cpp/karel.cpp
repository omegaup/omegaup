#include "karel.h"

#include <iostream>
#include <memory>
#include <optional>
#include <sstream>
#include <stack>
#include <string>

#include "json.h"
#include "logging.h"
#include "util.h"

namespace karel {

namespace {

constexpr bool kDebug = false;

std::string Stringify(const std::vector<int32_t>& expression_stack) {
  std::ostringstream buffer;
  buffer << "[";
  bool first = true;
  for (int32_t val : expression_stack) {
    if (first)
      first = false;
    else
      buffer << ",";
    buffer << val;
  }
  buffer << "]";
  return buffer.str();
}

std::optional<Opcode> ParseOpcode(std::string_view name) {
  if (name == "HALT")
    return Opcode::HALT;
  if (name == "LINE")
    return Opcode::LINE;
  if (name == "LEFT")
    return Opcode::LEFT;
  if (name == "WORLDWALLS")
    return Opcode::WORLDWALLS;
  if (name == "ORIENTATION")
    return Opcode::ORIENTATION;
  if (name == "ROTL")
    return Opcode::ROTL;
  if (name == "ROTR")
    return Opcode::ROTR;
  if (name == "MASK")
    return Opcode::MASK;
  if (name == "NOT")
    return Opcode::NOT;
  if (name == "AND")
    return Opcode::AND;
  if (name == "OR")
    return Opcode::OR;
  if (name == "EQ")
    return Opcode::EQ;
  if (name == "EZ")
    return Opcode::EZ;
  if (name == "JZ")
    return Opcode::JZ;
  if (name == "JMP")
    return Opcode::JMP;
  if (name == "FORWARD")
    return Opcode::FORWARD;
  if (name == "WORLDBUZZERS")
    return Opcode::WORLDBUZZERS;
  if (name == "BAGBUZZERS")
    return Opcode::BAGBUZZERS;
  if (name == "PICKBUZZER")
    return Opcode::PICKBUZZER;
  if (name == "LEAVEBUZZER")
    return Opcode::LEAVEBUZZER;
  if (name == "LOAD")
    return Opcode::LOAD;
  if (name == "POP")
    return Opcode::POP;
  if (name == "DUP")
    return Opcode::DUP;
  if (name == "DEC")
    return Opcode::DEC;
  if (name == "INC")
    return Opcode::INC;
  if (name == "CALL")
    return Opcode::CALL;
  if (name == "RET")
    return Opcode::RET;
  if (name == "PARAM")
    return Opcode::PARAM;
  LOG(ERROR) << "Invalid mnemonic: " << name;
  return std::nullopt;
}

std::optional<RunResult> ParseRunResult(std::string_view name) {
  if (name == "OK")
    return RunResult::OK;
  if (name == "INSTRUCTION")
    return RunResult::INSTRUCTION;
  if (name == "WALL")
    return RunResult::WALL;
  if (name == "WORLDUNDERFLOW")
    return RunResult::WORLDUNDERFLOW;
  if (name == "BAGUNDERFLOW")
    return RunResult::BAGUNDERFLOW;
  if (name == "STACK")
    return RunResult::STACK;
  LOG(ERROR) << "Invalid run result: " << name;
  return std::nullopt;
}

std::optional<Instruction> ParseInstruction(const json::ListValue& value) {
  if (value.value().size() == 0) {
    LOG(ERROR) << "Empty instruction " << value;
    return std::nullopt;
  }
  if (value.value()[0]->GetType() != json::Type::STRING) {
    LOG(ERROR) << "Non-string mnemonic " << value;
    return std::nullopt;
  }
  std::string_view opcode_name = value.value()[0]->AsString().value();
  auto opcode = ParseOpcode(opcode_name);
  if (!opcode) {
    LOG(ERROR) << "Invalid opcode " << value;
    return std::nullopt;
  }

  Instruction ins{opcode.value(), 0};

  switch (opcode.value()) {
    case Opcode::HALT:
    case Opcode::LEFT:
    case Opcode::WORLDWALLS:
    case Opcode::ORIENTATION:
    case Opcode::ROTL:
    case Opcode::ROTR:
    case Opcode::MASK:
    case Opcode::NOT:
    case Opcode::AND:
    case Opcode::OR:
    case Opcode::EQ:
    case Opcode::FORWARD:
    case Opcode::WORLDBUZZERS:
    case Opcode::BAGBUZZERS:
    case Opcode::PICKBUZZER:
    case Opcode::LEAVEBUZZER:
    case Opcode::POP:
    case Opcode::DUP:
    case Opcode::DEC:
    case Opcode::INC:
    case Opcode::RET:
      // nullary
      if (value.value().size() != 1) {
        LOG(ERROR) << "Unexpected argument to " << value;
        return std::nullopt;
      }
      return ins;

    case Opcode::PARAM:
    case Opcode::LINE:
    case Opcode::LOAD:
    case Opcode::JZ:
    case Opcode::JMP:
      // unary
      if (value.value().size() != 2) {
        LOG(ERROR) << "Unexpected arguments to " << value;
        return std::nullopt;
      }
      if (value.value()[1]->GetType() != json::Type::INT) {
        LOG(ERROR) << "Invalid argument to " << value;
        return std::nullopt;
      }
      ins.arg = value.value()[1]->AsInt().value();
      return ins;

    case Opcode::EZ: {
      // unary, string
      if (value.value().size() != 2) {
        LOG(ERROR) << "Unexpected arguments to " << value;
        return std::nullopt;
      }
      if (value.value()[1]->GetType() != json::Type::STRING) {
        LOG(ERROR) << "Invalid argument to " << value;
        return std::nullopt;
      }
      auto result = ParseRunResult(value.value()[1]->AsString().value());
      if (!result) {
        return std::nullopt;
      }
      ins.arg = static_cast<int32_t>(result.value());
      return ins;
    }

    case Opcode::CALL:
      // binary
      if (value.value().size() != 3) {
        LOG(ERROR) << "Unexpected arguments to " << value;
        return std::nullopt;
      }
      if (value.value()[1]->GetType() != json::Type::INT) {
        LOG(ERROR) << "Invalid argument to " << value;
        return std::nullopt;
      }
      ins.arg = value.value()[1]->AsInt().value();
      return ins;
  }

  return ins;
}

struct StackFrame {
  int32_t pc;
  int32_t param;
  size_t sp;
};

}  // namespace

std::optional<std::vector<Instruction>> ParseInstructions(
    std::string_view program) {
  auto parsed_json = json::Parse(program);
  if (!parsed_json) {
    LOG(ERROR) << "Invalid JSON";
    return std::nullopt;
  }
  if ((*parsed_json)->GetType() != json::Type::LIST) {
    LOG(ERROR) << "Invalid program " << *parsed_json.value();
    return std::nullopt;
  }
  const json::ListValue& list_value = (*parsed_json)->AsList();

  std::vector<Instruction> instructions;
  for (const auto& entry : list_value.value()) {
    if (entry->GetType() != json::Type::LIST) {
      LOG(ERROR) << "Invalid instruction " << *entry;
      return std::nullopt;
    }
    auto instruction = ParseInstruction(entry->AsList());
    if (!instruction)
      return std::nullopt;
    instructions.emplace_back(std::move(instruction.value()));
  }

  return instructions;
}

RunResult Run(const std::vector<Instruction>& program, Runtime* runtime) {
  int32_t pc = 0;
  size_t ic = 0;
  std::stack<StackFrame> function_stack;
  std::vector<int32_t> expression_stack;

  while (static_cast<size_t>(pc) < program.size()) {
    if (ic >= runtime->instruction_limit)
      return RunResult::INSTRUCTION;

    const auto& curr = program[pc];
    if (kDebug) {
      fprintf(stdout, "opcode \"%d %s,%d\"\n",
              static_cast<int32_t>(curr.opcode),
              kOpcodeNames[static_cast<int32_t>(curr.opcode)], curr.arg);
      fflush(stdout);
    }
    switch (curr.opcode) {
      case Opcode::HALT:
        return RunResult::OK;

      case Opcode::LINE:
        runtime->line = curr.arg;
        break;

      case Opcode::LEFT:
        ic++;
        runtime->orientation = (runtime->orientation + 3) & 3;
        if (++runtime->left_count > runtime->left_limit)
          return RunResult::INSTRUCTION;
        break;

      case Opcode::LOAD:
        expression_stack.emplace_back(curr.arg);
        break;

      case Opcode::CALL: {
        ic++;
        int32_t param = expression_stack.back();
        expression_stack.pop_back();

        function_stack.emplace(StackFrame{pc, param, expression_stack.size()});
        pc = curr.arg - 1;

        if (function_stack.size() >= runtime->stack_limit)
          return RunResult::STACK;

        break;
      }

      case Opcode::RET: {
        if (function_stack.empty())
          return RunResult::OK;
        StackFrame& frame = function_stack.top();
        pc = frame.pc;
        if (expression_stack.size() > frame.sp)
          expression_stack.resize(frame.sp);
        function_stack.pop();

        break;
      }

      case Opcode::WORLDWALLS:
        expression_stack.emplace_back(runtime->get_walls());
        break;

      case Opcode::ORIENTATION:
        expression_stack.emplace_back(runtime->orientation);
        break;

      case Opcode::ROTL: {
        int32_t op = expression_stack.back();
        expression_stack.back() = (op + 3) & 3;
        break;
      }

      case Opcode::ROTR: {
        int32_t op = expression_stack.back();
        expression_stack.back() = (op + 1) & 3;
        break;
      }

      case Opcode::MASK: {
        int32_t op = expression_stack.back();
        expression_stack.back() = 1 << op;
        break;
      }

      case Opcode::NOT: {
        int32_t op = expression_stack.back();
        expression_stack.back() = (op == 0) ? 1 : 0;
        break;
      }

      case Opcode::AND: {
        int32_t op2 = expression_stack.back();
        expression_stack.pop_back();
        int32_t op1 = expression_stack.back();
        expression_stack.back() = (op1 & op2) ? 1 : 0;
        break;
      }

      case Opcode::OR: {
        int32_t op2 = expression_stack.back();
        expression_stack.pop_back();
        int32_t op1 = expression_stack.back();
        expression_stack.back() = (op1 | op2) ? 1 : 0;
        break;
      }

      case Opcode::EQ: {
        int32_t op2 = expression_stack.back();
        expression_stack.pop_back();
        int32_t op1 = expression_stack.back();
        expression_stack.back() = (op1 == op2) ? 1 : 0;
        break;
      }

      case Opcode::JZ:
        ic++;
        if (expression_stack.back() == 0)
          pc += curr.arg;
        expression_stack.pop_back();
        break;

      case Opcode::WORLDBUZZERS:
        expression_stack.emplace_back(runtime->get_buzzers());
        break;

      case Opcode::FORWARD: {
        ic++;
        constexpr int32_t dx[] = {-1, 0, 1, 0};
        constexpr int32_t dy[] = {0, 1, 0, -1};
        runtime->x += dx[runtime->orientation];
        runtime->y += dy[runtime->orientation];
        if (++runtime->forward_count > runtime->forward_limit)
          return RunResult::INSTRUCTION;
        break;
      }

      case Opcode::BAGBUZZERS:
        expression_stack.emplace_back(runtime->bag);
        break;

      case Opcode::JMP:
        ic++;
        pc += curr.arg;
        break;

      case Opcode::PICKBUZZER:
        ic++;
        runtime->inc_buzzers(-1);
        if (runtime->bag != kInfinity)
          runtime->bag++;
        if (++runtime->pickbuzzer_count > runtime->pickbuzzer_limit)
          return RunResult::INSTRUCTION;
        break;

      case Opcode::LEAVEBUZZER:
        ic++;
        runtime->inc_buzzers(1);
        if (runtime->bag != kInfinity)
          runtime->bag--;
        if (++runtime->leavebuzzer_count > runtime->leavebuzzer_limit)
          return RunResult::INSTRUCTION;
        break;

      case Opcode::EZ: {
        if (expression_stack.back() == 0)
          return static_cast<RunResult>(curr.arg);
        expression_stack.pop_back();
        break;
      }

      case Opcode::POP:
        expression_stack.pop_back();
        break;

      case Opcode::DUP:
        expression_stack.emplace_back(expression_stack.back());
        break;

      case Opcode::DEC:
        expression_stack.back()--;
        break;

      case Opcode::INC:
        expression_stack.back()++;
        break;

      case Opcode::PARAM:
        expression_stack.emplace_back(function_stack.top().param);
        break;
    }

    pc++;

    if (kDebug) {
      fprintf(stdout,
              "state "
              "{\"pc\":%d,\"stackSize\":%zu,\"expressionStack\":%s\"line\":%zu,"
              "\"ic\":%zu,\"running\":"
              "true}\n",
              pc, function_stack.size(), Stringify(expression_stack).c_str(),
              runtime->line, ic);
      fflush(stdout);
    }
  }

  return RunResult::OK;
}

}  // namespace karel
