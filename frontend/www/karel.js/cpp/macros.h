#ifndef MACROS_H_
#define MACROS_H_

#define DISALLOW_COPY_AND_ASSIGN(Type) \
  Type(const Type&) = delete;          \
  Type operator=(const Type&) = delete

#define array_length(x) (sizeof(x) / sizeof(x[0]))

#define HANDLE_EINTR(x)                                     \
  ({                                                        \
    decltype(x) eintr_wrapper_result;                       \
    do {                                                    \
      eintr_wrapper_result = (x);                           \
    } while (eintr_wrapper_result == -1 && errno == EINTR); \
    eintr_wrapper_result;                                   \
  })

#endif  // MACROS_H_
