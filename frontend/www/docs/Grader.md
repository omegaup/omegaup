# OmegaUp Grader Documentation

## Overview

### Arena (Frontend v1)
The **Arena** (Frontend for version 1) is designed without any logic for validating problems—that responsibility is handled by the **Grader**. Additionally, Arena supports communication with external judges.

### Grader
The **Grader** manages the queue for evaluating submitted programs. When notified by **Arena** or the **Frontend** to judge a submission, the Grader:
1. Retrieves the submission record from the database.
2. Routes it to the appropriate evaluator’s queue (e.g., local, UVa, PKU, TJU, LiveArchive, SPOJ).
3. Updates the submission status to "pending."

Once this is done, the Grader waits for the next notification. After the evaluation is complete, the Grader sends a callback with the validation result to Arena, which notifies the end user via Comet. For now, the Frontend relies on polling to check for updates.

### Remote Evaluators
Remote evaluators have limited queue capacities (e.g., UVa supports ~10 concurrent slots, while others support only one). This is because these systems were not designed for automated consumers. When a server returns a verdict, the evaluator updates the submission record in the database with the relevant fields.

### Local Evaluation
For local evaluations, the Grader maintains a list of registered **Runners**. The process is as follows:
1. The Grader sends the source code and test case inputs (cacheable) to a Runner.
2. For each test case, the Runner returns:
   - An error notification if an issue occurs, or
   - The program’s output, including metadata such as execution time and memory usage.
3. The Grader runs the validator to compare each output against the expected output.
4. A final numerical score is calculated for the submission (typically the sum of individual test case scores).
5. The Grader updates the submission record in the database.

## Usage

To invoke the Grader from the Frontend, send a JSON payload like the following to the Grader’s endpoint:

```json
{"id": 1234}
