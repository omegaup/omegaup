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
    session_object: Any,
    api_endpoint: str,
    request_params: Dict[str, Any]
) -> bool:
    """Check if a resource already exists before making a creation request."""
    api = api_endpoint.lower()
    if not api.endswith("/"):
        api += "/"

    if api in {"/identity/create/", "/user/create/"}:
        username = request_params.get("username")
        if username:
            response = session_object.request(
                "/user/profile/", {"username": username}
            ) or {}
            return bool(
                response.get("status") == "ok"
                and response.get("userinfo", {}).get("username") == username
            )

    if api == "/problem/create/":
        alias = request_params.get("problem_alias")
        if alias:
            response = session_object.request(
                "/problem/details/", {"problem_alias": alias}
            ) or {}
            return bool(
                response.get("status") == "ok"
                and response.get("problem", {}).get("alias") == alias
            )

    if api == "/contest/create/":
        alias = request_params.get("alias")
        if alias:
            response = session_object.request(
                "/contest/adminDetails/", {"contest_alias": alias}
            ) or {}
            return bool(
                response.get("status") == "ok"
                and response.get("contest", {}).get("alias") == alias
            )

    if api == "/course/create/":
        alias = request_params.get("alias")
        if alias:
            response = session_object.request(
                "/course/adminDetails/", {"alias": alias}
            ) or {}
            return bool(
                response.get("status") == "ok"
                and response.get("course", {}).get("alias") == alias
            )

    if api == "/course/createassignment/":
        course_alias = request_params.get("course_alias")
        assignment_alias = request_params.get("alias")
        if course_alias and assignment_alias:
            response = session_object.request(
                "/course/assignmentDetails/",
                {"course": course_alias, "assignment": assignment_alias},
            ) or {}
            assignment = {}
            if response.get("status") == "ok":
                assignment = response.get("assignment", {})
            return bool(
                assignment.get("alias") == assignment_alias
                and response.get("course", {}).get("alias") == course_alias
            )

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
    $NOW$ normalization and optional retry logic.
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
                "invoking one request %r",
                {"api": api, "params": params},
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
                "retrying %s (attempt %d): %s",
                api, attempt, exc
            )
            time.sleep(backoff_sec * attempt)
