#include <cstdint>
#include <cstring>
#include <experimental/string_view>
#include <memory>
#include <type_traits>

#include <emscripten.h>

#include "karel.h"
#include "logging.h"

struct GlobalState {
  std::vector<karel::Instruction>* program = nullptr;
} sGlobalState;

static_assert(std::is_trivially_destructible<GlobalState>::value,
              "GlobalState is not trivially destructible");

EMSCRIPTEN_KEEPALIVE
extern "C" bool compile(const char* c) {
  auto program =
      karel::ParseInstructions(std::experimental::string_view(c, strlen(c)));
  if (!program)
    return false;
  if (sGlobalState.program)
    delete sGlobalState.program;
  sGlobalState.program =
      new std::vector<karel::Instruction>(std::move(program.value()));
  return true;
}

EMSCRIPTEN_KEEPALIVE
extern "C" uint32_t run(karel::Runtime* runtime) {
  static_assert(sizeof(size_t) == 4, "size_t should be of size 4");
  static_assert(sizeof(uint16_t*) == 4, "pointers should be of size 4");

  if (!sGlobalState.program)
    return static_cast<uint32_t>(karel::RunResult::INSTRUCTION);

  return static_cast<uint32_t>(karel::Run(*sGlobalState.program, runtime));
}
