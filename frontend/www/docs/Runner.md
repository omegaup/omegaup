*Runner* is *another* web service responsible for compiling and executing the code sent to *Grader*. All information will be transmitted in gzipped JSON format (with `content-type: application/json+gzip`), over HTTPS, using mutual authentication via certificates, and it exposes the following URLs:

# /compile/

Compiles a submission. This call is synchronous.

### Input

> ```
> {
>    'lang': 'lang-name', // one of 'c', 'cpp', 'java', 'py', 'rb', 'pl', 'p'
>    'code': ['code_1', 'code_2', ..., 'code_n']
> }
> ```

The JSON must contain a `lang` field and a `code` field, which holds a list of strings representing the files to be compiled. A temporary folder with a pseudo-random name (using `mkdtemp`) will be created, and the files will be extracted there.
The first file in the list will be saved as `main.lang`, and the others as `f01.lang`, `f02.lang`, `f03.lang`, and so on. No file should depend on its filename to execute (i.e., in Java, files cannot contain public classes), to make things easier. Once all files have been extracted to the file system, the sandbox will run to compile the program using the profile corresponding to the specified language.

### Output

> ```
> {
>     'error': 'The compiler returned .....'
> }
> ```

If there’s a compilation error, the service will return a JSON object with an `error` field containing the compiler output.

> ```
> {
>     'token': 'ABJdfoeKFPer9183409dsfDFPOfkaR834JFDJF='
> }
> ```

If compilation is successful, the service will return a JSON object with a `token` field containing an opaque token, which must be used in subsequent calls to identify the submission.

# /run/

Executes a previously compiled program with a specific _input set_. This call is synchronous and may take a long time (up to the time limit).

### Input

> ```
> {
>     'token': 'ABJdfoeKFPer9183409dsfDFPOfkaR834JFDJF=',
>     'input': 'd41d8cd98f00b204e9800998ecf8427e'
> }
> ```

*Grader* initially assumes that *Runner* always has the input set. Once the input set is located, *Runner* extracts the language from the previously mentioned opaque token (jejeje :P), and with that information, the *Sandbox* executes the program for each input, recording the standard output and metadata such as whether the execution had an error, as well as the time and memory used. It then builds a JSON object with this information and returns it. After the response is sent, the temporary folder and all generated files are automatically deleted.

If *Runner* does not have the input set, it returns a JSON object with the appropriate error.

### Output

> ```
> {
>     'results': [
>         {
>             'name': '05', 'status': 'OK', 'time': 103, 'memory': 1235,
>             'output': 'BlaBlaBla'
>          },
>         {
>             'name': '06', 'status': 'TLE', 'time': 3000, 'memory': 1235,
>          }
>     ]
> }
> ```

This is the information returned in case of success: a list of results for each test case, including time, memory, and output (if no errors occurred).

> ```
> {
>     'error': 'missing input'
> }
> ```

This is the (textual) message returned when *Runner* does not have the requested input set.

# /input/

Uploads an input set to *Runner* for future use.

### Input

> ```
> {
>     'input': 'd41d8cd98f00b204e9800998ecf8427e',
>     'cases': [
>         {
>             'name': '05', 'data': 'blablablablabla'
>         },
>         {
>             'name': '06', 'data': 'blebleblebleble'
>         },
>     ]
> }
> ```

This simply uploads the input files to the system where *Runner* is hosted.

### Output

> ```
> {
>     'status': 'ok'
> }
> ```
