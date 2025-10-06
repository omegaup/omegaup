"""Seeder utilities: helpers, config loading, and batched sending."""

from __future__ import annotations

import json
import logging
import os
import random
import string
import time
from typing import Any, Callable, Dict, Iterable, Mapping, Optional, TypeVar

import yaml

ParamT = TypeVar("ParamT", bound=Mapping[str, object])


def random_base(length: int, rng: random.Random) -> str:
    """Return a fixed-length base36 string (a-z0-9)."""
    if length < 0:
        raise ValueError("length must be >= 0")
    alphabet = string.ascii_lowercase + string.digits
    return "".join(rng.choice(alphabet) for _ in range(length))


def make_request(
    api: str,
    params: ParamT,
    files: Optional[Dict[str, str]] = None,
) -> Dict[str, object]:
    """Build a request dict expected by `_process_one_request`."""
    if not api or not isinstance(api, str):
        raise ValueError("api must be a non-empty string")
    out: Dict[str, object] = {"api": api, "params": params}
    if files is not None:
        if not isinstance(files, dict):
            raise ValueError("files must be a dict when provided")
        out["files"] = files
    return out


def _resolve_path(root: str, maybe_rel_path: str) -> str:
    """Resolve a relative path against ROOT, keep absolute paths as-is."""
    return (
        maybe_rel_path
        if os.path.isabs(maybe_rel_path)
        else os.path.join(root, maybe_rel_path)
    )


def _safe_load_yaml(path: str) -> Dict[str, Any]:
    """Safely load YAML, returning {} if missing or invalid."""
    if not path:
        return {}
    if not os.path.exists(path):
        return {}
    with open(path, "r", encoding="utf-8") as fh:
        data = yaml.safe_load(fh) or {}
    return data if isinstance(data, dict) else {}


def _extract_counts(raw: Dict[str, Any], env_name: str) -> Dict[str, int]:
    """Extract counts from environment/environtment or legacy root-level."""
    environments = raw.get("environment") or raw.get("environtment") or {}
    if isinstance(environments, dict):
        env_block = environments.get(env_name) or {}
        counts = env_block.get("counts")
        if isinstance(counts, dict):
            return counts
    counts = raw.get("counts")
    if isinstance(counts, dict):
        return counts
    return {}


def _extract_test_zip(raw: Dict[str, Any], root: str) -> str:
    """Extract ZIP path from paths.test_zip, list entry, or legacy key."""
    paths = raw.get("paths")
    if isinstance(paths, dict):
        val = paths.get("test_zip")
        if isinstance(val, str) and val:
            return _resolve_path(root, val)
    if isinstance(paths, list):
        for item in paths:
            if (
                isinstance(item, dict)
                and "test_zip" in item
                and isinstance(item["test_zip"], str)
            ):
                return _resolve_path(root, item["test_zip"])
    legacy = raw.get("test_zip_path")
    if isinstance(legacy, str) and legacy:
        return _resolve_path(root, legacy)
    return _resolve_path(root, "frontend/tests/resources/testproblem.zip")


def load_config(config_path: Optional[str], root: str) -> Dict[str, Any]:
    """Load YAML and return a normalized config dict."""
    raw = _safe_load_yaml(config_path)
    endpoints = raw.get("endpoints") or {}
    if not isinstance(endpoints, dict):
        endpoints = {}
    env_name = os.getenv("SEEDER_ENV", "testing")
    counts = _extract_counts(raw, env_name)
    langs_list = raw.get("langs") or []
    if not isinstance(langs_list, list):
        langs_list = []
    langs_csv = ",".join(langs_list)
    selected_tags = raw.get("selected_tags") or []
    if not isinstance(selected_tags, list):
        selected_tags = []
    selected_tags_json = json.dumps(selected_tags, ensure_ascii=False)
    test_zip_path = _extract_test_zip(raw, root)
    return {
        "endpoints": endpoints,
        "counts": counts,
        "langs_csv": langs_csv,
        "selected_tags_json": selected_tags_json,
        "test_zip_path": test_zip_path,
    }


def send_all(  # pylint: disable=too-many-arguments
    session_obj: object,
    now_ts: float,
    reqs: Iterable[Dict[str, Any]],
    label: str,
    process_one_request: Callable[[object, Dict[str, Any], float], None],
    log_every: int = 10000,
    retries: int = 0,
    backoff_sec: float = 0.5,
) -> None:
    """Send a batch of requests with progress logging and optional retries."""
    ok = 0
    fail = 0
    for idx, req in enumerate(reqs, 1):
        attempt = 0
        while True:
            try:
                process_one_request(session_obj, req, now_ts)
                ok += 1
                break
            except Exception as exc:  # pylint: disable=broad-except
                attempt += 1
                if attempt > retries:
                    fail += 1
                    logging.error("[%s] failed: %s", label, exc)
                    break
                time.sleep(backoff_sec * attempt)
        if log_every and idx % log_every == 0:
            logging.info(
                "[%s] progress=%d ok=%d fail=%d",
                label,
                idx,
                ok,
                fail,
            )
    logging.info(
        "[%s] done ok=%d fail=%d total=%d",
        label,
        ok,
        fail,
        ok + fail,
    )
