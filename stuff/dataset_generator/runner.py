"""
Local request runner compatible with bootstrap signature.
"""

from __future__ import annotations

import logging
import time
from typing import Any, Dict, Mapping, Optional


def _normalize_now(params: Dict[str, Any], now_ts: float) -> None:
    """Replace $NOW$[+offset] tokens in-place with epoch seconds."""
    for key, val in list(params.items()):
        if isinstance(val, str) and val.startswith("$NOW$"):
            parts = val.split("+", 1)
            offset = 0
            if len(parts) == 2 and parts[1]:
                try:
                    offset = int(parts[1])
                except ValueError:
                    offset = 0
            params[key] = int(now_ts + offset)


def _resource_exists(
    session_obj: Any, api: str, params: Mapping[str, Any]
) -> bool:
    """Best-effort existence checks (mirrors bootstrap logic)."""
    api_norm = api.lower()
    if not api_norm.endswith("/"):
        api_norm += "/"

    if api_norm == "/problem/create/":
        alias = params.get("problem_alias")
        if alias and session_obj.request(
            "/problem/details/", {"problem_alias": alias}
        ):
            logging.warning("Problem %s exists, skipping", alias)
            return True

    if api_norm == "/contest/create/":
        alias = params.get("alias")
        if alias and session_obj.request(
            "/contest/adminDetails/", {"contest_alias": alias}
        ):
            logging.warning("Contest %s exists, skipping", alias)
            return True

    if api_norm == "/course/create/":
        alias = params.get("alias")
        if alias and session_obj.request(
            "/course/adminDetails/", {"alias": alias}
        ):
            logging.warning("Course %s exists, skipping", alias)
            return True

    if api_norm == "/course/createassignment/":
        course_alias = params.get("course_alias")
        assign_alias = params.get("alias")
        if course_alias and assign_alias:
            if session_obj.request(
                "/course/assignmentDetails/",
                {"course": course_alias, "assignment": assign_alias},
            ):
                logging.warning("Assignment %s exists, skipping", assign_alias)
                return True

    if api_norm == "/user/create/":
        username = params.get("username")
        if username and session_obj.request(
            "/user/profile/", {"username": username}
        ):
            logging.warning("User %s exists, skipping", username)
            return True

    return False


def process_one_request_local(
    session_obj: Any,
    request: Mapping[str, Any],
    now_ts: float,
    *,
    check_exists: bool = True,
    retries: int = 0,
    backoff_sec: float = 0.5,
) -> None:
    """
    Execute a single request dict with
    $NOW$ normalization and fail_ok handling.
    """
    api: str = request["api"]  # type: ignore[assignment]
    params: Dict[str, Any] = dict(request.get("params", {}))
    files: Optional[Mapping[str, str]] = request.get(
        "files"
    )  # type: ignore[assignment]
    fail_ok: bool = bool(request.get("fail_ok"))

    _normalize_now(params, now_ts)

    if check_exists and _resource_exists(session_obj, api, params):
        return

    attempt = 0
    while True:
        try:
            logging.info(
                "invoking one request %r", {"api": api, "params": params}
            )
            result = session_obj.request(api, data=params, files=files)
            status = (result or {}).get("status", "error")
            if status != "ok" and not fail_ok:
                raise RuntimeError(
                    f"Request failed: status={status}, result={result}"
                )
            return
        except (ConnectionError, TimeoutError, RuntimeError) as exc:
            attempt += 1
            if attempt > retries:
                raise
            logging.warning(
                "retrying %s (attempt %d): %s", api, attempt, exc
            )
            time.sleep(backoff_sec * attempt)
