Arena (also known as Frontend for v1) won’t contain any logic for problem validation — that’s _Grader’s_ job. Moreover, Grader can communicate with external judges!

_Grader_ is responsible for managing the queue of submissions waiting to be judged. Once _Arena_/_Frontend_ notifies it that a problem needs to be judged, it checks the corresponding record in the database, forwards it to the appropriate evaluation queue (local, UVa, PKU, TJU, LiveArchive, SPOJ), and changes its status to “waiting.” At that point, _Grader_ “washes its hands” and goes back to waiting for the next notification.

Once _Arena_ is complete, _Grader_ must send a callback when a submission’s result is ready, so that the user can be notified via Comet (for now, _Frontend_ will handle this through polling).

The remote judges have relatively small waiting queues (UVa supports about ~10 concurrent slots, and all the others only one). The reason is that none of those systems were originally designed to have automated consumers of their information. Once a remote server responds with a verdict, the corresponding evaluator must update the submission record and modify the relevant fields.

For local evaluation, _Grader_ must maintain a list of registered Runners so it can send them the source code and (cacheable) input test cases. Each Runner returns, for each test case, either an error notification (if something went wrong) or the program output along with metadata such as runtime and memory usage. With that information, Grader runs the validator on each output, compares it to the expected output, and produces a final numerical score for the submission (usually the sum of all case scores). Finally, _Grader_ updates the submission record in the database.

## Usage

To invoke _Grader_ from the Frontend, simply send a JSON payload like `{'id': 1234}` to `https://localhost:21680/grade/`. That’s it.

Since the call must be made using certificates, you need to use PHP’s cURL library to make the request. I was able to do it from the console using:

`curl --url https://localhost:21680/grade/ -d '{"id": 12345}' -E frontend/omegaup.pem --cacert ssl/omegaup-ca.crt --insecure`

The --insecure flag is required because the grader’s certificate doesn’t include its hostname. If we set localhost as the CN (Common Name) in the grader’s certificate, we can remove that flag and everyone will be happy :)