
# Teaching Assistant: Environment Variables

To avoid exposing credentials in CI or logs, you can set these environment variables:

- `OMEGAUP_USERNAME` (username)
- `OMEGAUP_PASSWORD` (password)
- `OMEGAUP_LLM_KEY` (LLM API key)

The script will use CLI arguments if provided, otherwise these variables, or prompt you if neither is set.

**Example:**

```sh
export OMEGAUP_USERNAME=your-username
export OMEGAUP_PASSWORD=your-password
export OMEGAUP_LLM_KEY=sk-...
python3 teaching_assistant.py ...
```
