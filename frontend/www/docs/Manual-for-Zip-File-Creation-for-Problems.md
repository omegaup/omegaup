In this document, we show you how to manually generate/edit a `.zip` file for an omegaUp problem. This document is intended for more experienced users or those who require more specific functionalities (e.g., Karel problems). If you're just starting to write problems or have simpler needs, we recommend using the [Problem Creator](https://mau-md.github.io/Omegaup-CDP/#/) and watching this [tutorial](https://www.youtube.com/watch?v=cUUP9DqQ1Vg&list=PL43fZBs80z1OdkZqSZte3vXA-8VKyh_ZZ&index=2&t=329s) on how to use it.

If you chose the manual option described in this document, we also recommend watching [part 1](https://www.youtube.com/watch?v=LfyRSsgrvNc) and [part 2](https://www.youtube.com/watch?v=i2aqXXOW5ic) of the tutorial on how to manually create problems for omegaUp.

# Configuration

omegaUp problems have several configurable variables:

* **Validator**:
    * **Token by token**: Reads all tokens (sequences of up to 4,194,304 printable contiguous characters separated by spaces) from the expected output file and the user's output, and validates that both sequences are identical.
    * **Token by token, ignoring case**: Same as above, but converts all tokens to lowercase before comparison.
    * **Numeric tokens with 1e-9 tolerance**: Reads numeric tokens (contiguous sequences of numbers and decimal separators), interprets them as numbers, and checks that both sequences have the same length and their corresponding values differ by no more than 1e-9 absolute or relative error.
    * **Interpret stdout as score**: Reads standard output, converts it to a float number, restricts it to the range [0.0, 1.0], and uses it as the final score. Mostly used for interactive problems to avoid cheating.
    * **Custom validator (`validator.$lang$`)**: Allows a custom program that reads the contestant's stdout (and both the input and expected output files), and prints a float in [0.0, 1.0]. See the [validator.$lang$ (optional)](#validatorlang-optional) section for implementation.

* **Languages**:
    * **C, C++, etc.**: Contestants can submit source code in supported languages.
    * **Karel**: Contestants can submit source code in Karel.
    * **Output only**: Contestants submit a `.zip` file with answers for all cases. To allow single case submission as plain text, there must be only one case named `Main.in`/`Main.out`.
    * **No submissions**: Contestants cannot submit. Used only for displaying content in courses.

* **Validator time limit (ms)**: Max real time in milliseconds the grader waits for the validator to return a verdict per case before returning `JE`.
* **Time limit (ms)**: Max CPU time in milliseconds the contestant's program is allowed per case before it's killed with `TLE`.
* **Total time limit (ms)**: Max real time the grader waits for the entire problem to execute before returning `TLE`. Cases are evaluated lexicographically.
* **Extra time for libinteractive (ms)**: Max real time for the judge’s program per case before it’s terminated with `TLE`.
* **Memory limit (KiB)**: Max RAM (heap+stack) in kibibytes the program can use before being terminated with `MLE`.
* **Output limit (bytes)**: Max bytes a program can write to stdout/stderr before being terminated with `OLE`. This is auto-detected from `.out` file sizes plus 10KiB, unless a custom validator is used.
* **Input limit (bytes)**: Max byte size of the contestant’s program. Use to avoid hardcoded/precomputed solutions.
* **Source**: Attribution or origin of the problem.
* **Public listing**: Whether the problem appears in public listings and is available for contests and courses.
* **Send clarifications via email**: Whether omegaUp can email the problem author when users request clarifications.
* **Tags**: Classification tags for the problem.

# Language Problems (C/C++/Java/Pascal)

To upload a problem to omegaUp, you need to save everything inside a **.ZIP** file (not `.rar`, `.tar.bz2`, `.7z`, `.zx`). The zip filename doesn't matter.

The zip must contain the following elements:

### cases/

* This folder should contain all the test cases with `.in` and `.out` extensions. Filenames don’t matter, but names must be matched, e.g., `1.in 1.out`, `hello.in hello.out`.

* **Do not use `.` in case names unless you're grouping cases:**

* omegaUp supports grouped cases, meaning you must solve all cases in a group to earn points. Useful when possible outputs are limited. To group, separate the group and case name with a `.`. Example: `group1.case1.in group1.case1.out`, `group1.case2.in group1.case2.out`.

* No limit on the number of cases, but recommended total case size is under 100MB.

* More cases means longer grading time per submission and may delay contests, especially if a solution causes `TLE`.

### statements/

* Must contain the problem statement in Markdown format (`es.markdown`). Use [https://omegaup.com/redaccion.php](https://omegaup.com/redaccion.php) to preview formatting.

* Full LaTeX support. Examples at [http://www.thestudentroom.co.uk/wiki/LaTex](http://www.thestudentroom.co.uk/wiki/LaTex).

* For a better contestant experience, make sure the preview looks good, including input/output tables.

* Wrap variable names like `$n$`, `$x$` to highlight them. Use `$x_i$` for subscripts.

### solutions/

* Similar to **statements/**. Contains the problem solution in markdown format. Must be named `es.markdown`, with optional translations: `en.markdown`, `pt.markdown`.

* Examples of problem files can be found [here](https://github.com/omegaup/omegaup/tree/master/frontend/tests/resources), especially [testproblem.zip](https://github.com/omegaup/omegaup/blob/master/frontend/tests/resources/testproblem.zip) which contains solutions.

### interactive/ (optional)

* Interactive problems must be created using [libinteractive](https://omegaup.com/libinteractive/). See that page for more info.

* For a reference structure, use [Cave from IOI 2013](https://omegaup.com/resources/cave.zip).

### validator.$lang$ (optional)

* If a custom validator is needed, include a file `validator.$lang$` in the root of the zip, where `$lang$` is one of `c`, `cpp`, `java`, `p` (Pascal), or `py`. Only one validator is needed and it’s language-independent from the contestant's submission.

* In the validator, you can open `data.in` (same as the input given to the contestant). The validator receives the contestant’s output via standard input.

* It’s equivalent to running: `./contestant < data.in | ./validator casebasename`, where `casebasename` is the `.in` filename without extension.

* You may also open `data.out`, which is the expected output for the current case.

* The validator **must** output a float between 0 and 1 to stdout indicating the percentage correctness. If nothing is printed, it results in `JE`. Less than 0 becomes 0; more than 1 becomes 1.

* Validators also run in the same sandbox as contestant programs.

Validating [sumas](https://omegaup.com/arena/problem/sumas), You can use the following code in C++17:

```cpp
#include <iostream>
#include <fstream>

int main() {
  // Reads "data.in" to get the original input.
  int64_t a, b;
  {
    std::ifstream input("data.in", std::ifstream::in);
    input >> a >> b;
  }
  // You can save anything that helps you evaluate in "data.out".
  int64_t expected_sum;
  {
    std::ifstream output("data.out", std::ifstream::in);
    output >> expected_sum;
  }

  // Reads standard input to get the contestant's output.
  int64_t contestant_sum;
  if (!(std::cin >> contestant_sum)) {
    std::cerr << "Error reading contestant output\n";
    std::cout << 0.0 << '\n';
    return 0;
  }

  // Determines if the answer is incorrect.
  if (expected_sum != contestant_sum && expected_sum != a + b) {
    std::cerr << "Incorrect output\n";
    std::cout << 0.0 << '\n';
    return 0;
  }

  // If execution reaches here, the contestant's output is correct.
  std::cout << 1.0 << '\n';
  return 0;
}
```

Or in Python 3:

```python
#!/usr/bin/python3
# -*- coding: utf-8 -*-

import logging
import sys

def _main():
  # Read "data.in" to get the original input.
  with open('data.in', 'r') as f:
    a, b = [int(x) for x in f.read().strip().split()]
  
  # Read "data.out" to get the expected result.
  with open('data.out', 'r') as f:
    expected_sum = int(f.read().strip())

  score = 0
  try:
    # Read the contestant's output
    contestant_sum = int(input().strip())

    # Check if the output is incorrect
    if contestant_sum not in (expected_sum, a + b):
      print('Incorrect output', file=sys.stderr)
      return

    # If all checks passed, the output is correct
    score = 1
  except:
    logging.exception('Error reading contestant output')
  finally:
    print(score)

if __name__ == '__main__':
  _main()
```

### `testplan` (optional)

* By default, each case has a value of 1/number-of-cases. If you want to assign different values to each case, create a file named `testplan` (without extension) at the root of the .zip. In this file, write one line per case. Each line should contain the name of the file that holds the case (without the extension) and the points for that case. For example, for a problem with cases `cases/caso1.in`, `cases/grupo2.caso1.in`, `cases/grupo2.caso2.in`, the `testplan` would be:

    ```
    caso1 5
    grupo2.caso1 10
    grupo2.caso2 0
    ```

Make sure no file has spaces in its name.

If you want to assign scores to a group (so you don't have to divide it among all the cases in the group), the convention is to assign the full score to the first case of the group and 0 to all the other cases in that group.

## Images
omegaUp already has native support for images :). To insert an image into your description, add the image file to your zip within the `statements/` folder and write in your `es.markdown`:

`![Alt text](image.jpg)`

The supported formats are: jpg, gif, png. Be mindful of the image size, as it cannot be re-scaled in markdown. Try to keep the image width under 650 pixels.

## Example Zips
Here are some example zips we use in omegaUp tests:

https://github.com/omegaup/omegaup/tree/master/frontend/tests/resources

## Known Errors and Bugs in omegaUp

* It is crucial that the `/cases` and `/statements` folders are directly at the root of the .zip, without intermediate folders. [Bug link] (https://github.com/omegaup/omegaup/issues/310) One way to do this in the Linux/Mac console is by using the command `zip -r miproblema.zip *` from the problem directory.
* omegaUp runs on Linux, so there is a difference between uppercase and lowercase letters. If your folder is named `Cases`, it won't be found, just like if your input files end in `.In`.

If you have any questions, contact [joemmanuel](mailto:joemmanuel@gmail.com) and [lhchavez](mailto:lhchavez@lhchavez.com)

# Karel Problems
First, try using https://omegaup.com/karel.js/

If you've already created the cases and don't feel like converting them with karel.js, here are the steps for Windows. First, install Python 2.7 (http://www.python.org/download/releases/2.7.5/) and add Python's path to the PATH environment variable (the default path is C:\Python27 if you choose "next, next" during installation). Once Python is installed and you can verify that you can run "python" from the DOS console, proceed with the following steps:

1. Have these files ready: https://docs.google.com/file/d/0B6Rb3__ksbxDRC1VSDV0amRYNmc/edit?usp=sharing. These include karel.exe (to run a solution with a world), kcl.exe (solution compiler), the Python script (karel_mdo_convert.py), and my script (karel-to-omegaup.bat) that uses everything above.
2. Place the MDO and KEC cases in a folder. To generate them, you can use the Karel case generator. I’m not sure if you have it, but you can download it here: http://www.cimat.mx/~amor/Omi/Utilerias/KarelOMI.zip
3. With that in place, you also need the solution. I program in Java, so I give my solutions a .JS extension (because kcl.exe interprets JS as Karel-Java code), or if you're using Pascal, add .PAS (so kcl.exe interprets it as a Karel-Pascal solution).
4. Now, place the executables, Python script, and my script in the same folder.
5. You can run my script without parameters; it will ask for the solution path (.JS or .PAS) and the cases' path (MDO and KEC) (no need to add the trailing slash). You can also run it from the console with this command: karel-to-omegaup.bat path-solution path-cases. If the path has spaces, use double quotes around the path, like this:

        karel-to-omegaup.bat "karel vs chuzpa\solucion.js" "karel vs chuzpa\casos"

6. If all files are in place, it will first try to compile the solution.js (using kcl.exe, which generates a .KX file with the same name and in the same location as the solution), then it will create the .IN worlds using the MDOs (it looks for all files with the MDO extension in the "path-cases" folder). An important point is that the Python script (karel_mdo_convert.py) requires the KEC to exist. That is, if the MDO is called caso1.MDO, there must be a corresponding caso1.KEC. If everything is correct, the Python script extracts information about beepers, orientation, and position, and adds it to the IN file it generates.

8. Once the IN file is generated, my script runs karel.exe using the generated IN file and the compiled solution (with the KX extension) as parameters, creating the OUT file for that IN. It's essential that the solution is correct, as this affects how the OUT is generated.

9. My BAT script creates a "cases" folder within the folder containing the cases, and that's where the IN and OUT files for Karel are stored.

10. Now you have the "cases" folder with the IN and OUT files. Just create the "statements" folder with the es.markdown file, and compress everything as you would when creating Language problems.
