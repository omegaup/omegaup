"""
Local request runner compatible with bootstrap signature.
"""

from __future__ import annotations

import logging
import time
from typing import Any, Dict, Mapping, Optional, cast
import yaml

SETTINGS_PATH = "./stuff/dataset_generator/settings.yml"


def _normalize_now(params: Dict[str, Any], now_ts: float) -> None:
    """
    replace $NOW$[+/-offset] tokens in-place with epoch seconds.
    """
    for key, val in list(params.items()):
        if isinstance(val, str) and val.startswith("$NOW$"):
            expr = val.replace("$NOW$", "", 1).strip()
            offset = 0
            if expr:
                try:
                    offset = int(expr)
                except ValueError:
                    offset = 0
            params[key] = int(now_ts + offset)


def _normalize_key(endpoint: str) -> str:
    """
    Normalize an endpoint for dictionary
    lookups (lowercase + trailing slash).
    """
    normalized = endpoint.strip().lower()
    return normalized if normalized.endswith("/") else normalized + "/"


def _ensure_slash(endpoint: str) -> str:
    """
    Ensure trailing slash for real endpoints
    while preserving case.
    """
    normalized = endpoint.strip()
    return normalized if normalized.endswith("/") else normalized + "/"


def _deep_get(
    mapping: Mapping[str, object],
    path: str
) -> object | None:
    """Return a nested value from a mapping using a dot-separated path."""
    current: object = mapping
    for key in path.split("."):
        if not isinstance(current, Mapping) or key not in current:
            return None
        current = current[key]  # type: ignore[index]
    return current


with open(SETTINGS_PATH, "r", encoding="utf-8") as f:
    _SETTINGS: dict[str, object] = yaml.safe_load(f) or {}

_ENDPOINTS: Mapping[str, str] = _SETTINGS.get(
    "endpoints", {}
)  # type: ignore[assignment]
_RULES: list[dict[str, object]] = _SETTINGS.get(
    "resource_checks", []
)  # type: ignore[assignment]

_RESOURCE_CHECKS: dict[str, dict[str, object]] = {}
for rule in _RULES:
    if not isinstance(rule, dict):
        continue

    create_key = rule.get("create")
    if not isinstance(create_key, str):
        continue

    create_endpoint = _ENDPOINTS.get(create_key)
    if not create_endpoint:
        continue

    _RESOURCE_CHECKS[_normalize_key(create_endpoint)] = {
        "check_api": _ensure_slash(str(rule.get("check_api", ""))),
        "params_map": rule.get("params_map", {}),
        "expect": rule.get("expect", []),
    }


def _resource_exists(
    session_object: Any,
    api_endpoint: str,
    request_params: dict[str, object],
) -> bool:
    """
    Return True if the resource
    already exists based on settings.yml rules.
    """
    spec = _RESOURCE_CHECKS.get(_normalize_key(api_endpoint))
    if not isinstance(spec, dict):
        return False

    check_api = spec.get("check_api")
    params_map = spec.get("params_map")
    expect = spec.get("expect")

    if not isinstance(
        check_api,
        str
    ) or not isinstance(
        params_map,
        Mapping
    ) or not isinstance(
        expect,
        list
    ):
        return False

    params_map_typed = cast(Mapping[str, str], params_map)
    expect_typed = cast(list[dict[str, str]], expect)

    check_params = {
        dst: request_params.get(src) for dst, src in params_map_typed.items()
    }
    if any(value in (None, "") for value in check_params.values()):
        return False

    resp = session_object.request(check_api, check_params)
    if not resp:
        return False

    for cond in expect_typed:
        if _deep_get(resp, cond["path"]) != request_params.get(cond["from"]):
            return False

    return True


def process_one_request_local(
    session_obj: Any,
    request: Mapping[str, Any],
    now_ts: float,
    *,
    retries: int = 0,
    backoff_sec: float = 0.5,
) -> None:
    """
    Execute a single request dict with $NOW$ normalization and retry logic.
    """
    api: str = str(request["api"])
    params: Dict[str, Any] = dict(request.get("params", {}))
    files: Optional[Mapping[str, str]] = (
        request.get("files") if "files" in request else None
    )
    fail_ok: bool = bool(request.get("fail_ok", False))

    if any(
        isinstance(v, str) and v.startswith("$NOW$")
        for v in params.values()
    ):
        _normalize_now(params, now_ts)

    if _resource_exists(session_obj, api, params):
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
