"""
Local request runner compatible with bootstrap signature.
"""

from __future__ import annotations

import logging
import time
from typing import Any, Dict, Mapping, Optional
import yaml
from dataset_generator.types import ResourceCheck, ResourceRule

DEFAULT_SETTINGS_PATH = "./stuff/dataset_generator/settings.yml"


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
    """
    Return a nested value from a mapping using a dot-separated path.
    """
    current: object = mapping
    for key in path.split("."):
        if not isinstance(current, Mapping) or key not in current:
            return None
        current = current[key]
    return current


def _load_settings(
    settings_path: str
) -> tuple[Dict[str, str], list[ResourceRule]]:
    """
    Load endpoints and resource_checks from settings.yml.
    """
    # TODO: Validate the full settings.yml schema.
    # - Use TypedDicts + dataclasses to enforce types.
    # - Verify required keys: 'endpoints' and
    #   'resource_checks', including inner types.
    # - Fail fast with a clear error message if
    #   the file is invalid or missing fields.
    with open(settings_path, "r", encoding="utf-8") as f:
        settings = yaml.safe_load(f) or {}

    endpoints: Dict[str, str] = {}
    endpoints_obj = settings.get("endpoints")
    if isinstance(endpoints_obj, Mapping):
        endpoints = {
            k: v for k, v in endpoints_obj.items()
            if isinstance(k, str) and isinstance(v, str)
        }

    rules: list[ResourceRule] = []
    rules_obj = settings.get("resource_checks")
    if isinstance(rules_obj, list):
        for item in rules_obj:
            if not isinstance(item, Mapping):
                continue
            create = item.get("create")
            check_api = item.get("check_api")
            params_map = item.get("params_map")
            expect = item.get("expect")
            if not (
                isinstance(create, str)
                and isinstance(check_api, str)
                and isinstance(params_map, Mapping)
            ):
                continue
            rule: ResourceRule = {
                "create": create,
                "check_api": check_api,
                "params_map": {
                    k: v
                    for k, v in params_map.items()
                    if isinstance(k, str) and isinstance(v, str)
                },
                "expect": list(expect) if isinstance(
                    expect,
                    list
                ) else [],
            }
            rules.append(rule)

    return endpoints, rules


def _build_resource_checks(
    endpoints: Mapping[str, str],
    rules: list[ResourceRule],
) -> dict[str, ResourceCheck]:
    """
    Build in-memory resource-check registry from endpoints and rules.
    """
    registry: dict[str, ResourceCheck] = {}
    for rule in rules:
        create_key = rule.get("create")
        if not isinstance(create_key, str):
            continue
        create_endpoint = endpoints.get(create_key)
        if not isinstance(create_endpoint, str):
            continue

        check_api = rule.get("check_api")
        params_map = rule.get("params_map")
        expect = rule.get("expect")

        if not isinstance(check_api, str):
            continue

        params_map_typed: Dict[str, str] = dict(params_map) if isinstance(
            params_map,
            Mapping
        ) else {}
        expect_typed: list[Dict[str, str]] = list(expect) if isinstance(
            expect,
            list
        ) else []

        registry[_normalize_key(create_endpoint)] = ResourceCheck(
            check_api=_ensure_slash(check_api),
            params_map=params_map_typed,
            expect=expect_typed,
        )
    return registry


def load_resource_checks(
    settings_path: str = DEFAULT_SETTINGS_PATH
) -> dict[str, ResourceCheck]:
    """
    Convenience loader that returns a registry keyed by create endpoint.
    """
    endpoints, rules = _load_settings(settings_path)
    return _build_resource_checks(endpoints, rules)


def _resource_exists(
    session_object: Any,
    api_endpoint: str,
    request_params: Dict[str, Any],
    resource_checks: Mapping[str, ResourceCheck],
) -> bool:
    """
    Return True if the resource already exists using the provided registry.
    """
    spec = resource_checks.get(_normalize_key(api_endpoint))
    if spec is None:
        return False

    check_params = {
        dst: request_params.get(src) for dst, src in spec.params_map.items()
    }
    if any(value in (None, "") for value in check_params.values()):
        return False

    resp = session_object.request(spec.check_api, check_params)
    if not resp:
        return False

    for cond in spec.expect:
        path = cond.get("path")
        source = cond.get("from")
        if not isinstance(path, str) or not isinstance(source, str):
            return False
        if _deep_get(resp, path) != request_params.get(source):
            return False
    return True


def process_one_request_local(
    session_obj: Any,
    request: Mapping[str, Any],
    now_ts: float,
    *,
    retries: int = 0,
    backoff_sec: float = 0.5,
    settings_path: Optional[str] = None,
    resource_checks: Optional[Mapping[str, ResourceCheck]] = None,
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

    registry = resource_checks or load_resource_checks(
        settings_path or DEFAULT_SETTINGS_PATH
    )

    if _resource_exists(session_obj, api, params, registry):
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
