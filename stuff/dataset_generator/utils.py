"""Seeder utilities: helpers, config loading, and batched sending."""

from __future__ import annotations

from argparse import Namespace
import json
import logging
import os
import random
import string
import time
from typing import (
    Any,
    Callable,
    Dict,
    Iterable,
    Mapping,
    Optional,
    TypeVar,
    List,
    Union,
    Tuple
)
import math
from concurrent.futures import ThreadPoolExecutor
from tqdm import tqdm
import yaml
from dataset_generator.runner import process_one_request_local

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
    files: Optional[Dict[str, Union[str, Tuple[str, str]]]] = None,
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
    """Extract counts from environment or legacy root-level."""
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


def _extract_identities_csv(raw: Dict[str, Any], root: str) -> str:
    """
    Extract identities CSV path from paths.identities_csv, list entry, or
    use default.
    """
    paths = raw.get("paths")
    if isinstance(paths, dict):
        val = paths.get("identities_csv")
        if isinstance(val, str) and val:
            return _resolve_path(root, val)
    if isinstance(paths, list):
        for item in paths:
            if (
                isinstance(item, dict)
                and "identities_csv" in item
                and isinstance(item["identities_csv"], str)
            ):
                return _resolve_path(root, item["identities_csv"])
    # Default path if not configured
    return _resolve_path(root, "frontend/tests/resources/identities.csv")


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


def load_config(config_path: str, root: str) -> Dict[str, Any]:
    """Load YAML and return a normalized config dict."""
    raw = _safe_load_yaml(config_path)
    endpoints = raw.get("endpoints") or {}
    if not isinstance(endpoints, dict):
        endpoints = {}
    workers = raw.get("workers") or {}
    if not isinstance(workers, dict):
        workers = {}
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

    identities_csv_path = _extract_identities_csv(raw, root)
    generic_group = raw.get("generic_group") or {}
    if not isinstance(generic_group, dict):
        generic_group = {}
    return {
        "endpoints": endpoints,
        "workers": workers,
        "counts": counts,
        "langs_csv": langs_csv,
        "selected_tags_json": selected_tags_json,
        "test_zip_path": test_zip_path,
        "identities_csv_path": identities_csv_path,
        "generic_group": generic_group,
    }


def distribute_users_to_requests(
    user_credentials: List[Dict[str, str]],
    requests: Iterable[Dict[str, Any]],
) -> List[Tuple[Dict[str, str], Dict[str, Any]]]:
    """
    Return a list with all the users
    """
    requests_list = list(requests)
    if not user_credentials:
        raise ValueError("user_credentials cannot be empty")

    result: List[Tuple[Dict[str, str], Dict[str, Any]]] = []
    num_users = len(user_credentials)

    for idx, req in enumerate(requests_list):
        user_cred = user_credentials[idx % num_users]
        result.append((user_cred, req))

    return result


def send_all_with_distributed_users(  # pylint: disable=too-many-arguments
    now_ts: float,
    reqs_with_users: List[Tuple[Dict[str, str], Dict[str, Any]]],
    label: str,
    log_every: int = 10000,
    retries: int = 0,
    backoff_sec: float = 0.5,
    *,
    workers: int = 1,
    session_ctor: Optional[Callable[..., Any]] = None,
    session_args: Optional[Union[Namespace, Mapping[str, Any]]] = None,
    token: Optional[str] = None,
) -> None:
    """
    Use the send_all logic but with a distributed users
    """
    progress_bar = tqdm(
        total=len(reqs_with_users),
        desc=label,
        unit="req",
        mininterval=0.5,
    )

    def _run_batch(
        batch: List[Tuple[Dict[str, str], Dict[str, Any]]]
    ) -> None:
        for i, (user_cred, req) in enumerate(batch, 1):
            attempt = 0
            while True:
                try:
                    # Create a new session for each request with its specific
                    # user
                    if session_ctor is None:
                        raise RuntimeError("session_ctor cannot be None")

                    with session_ctor(
                        session_args,
                        user_cred['username'],
                        user_cred['password'],
                        token
                    ) as s:
                        process_one_request_local(s, req, now_ts)
                    progress_bar.update(1)
                    break
                except Exception as exc:  # pylint: disable=broad-except
                    attempt += 1
                    if attempt > retries:
                        logging.error(
                            "[%s] failed: %s | req=%r",
                            label,
                            exc, {"api": req.get("api")}
                        )
                        progress_bar.update(1)
                        break
                    time.sleep(backoff_sec * attempt)
            if log_every and i % log_every == 0:
                logging.info(
                    "[%s] progress=%d/%d",
                    label,
                    progress_bar.n,
                    progress_bar.total
                )

    try:
        if workers <= 1:
            _run_batch(reqs_with_users)
        else:
            total_requests = len(reqs_with_users) or 1
            effective_workers = max(1, min(workers, total_requests))
            chunk_size = math.ceil(total_requests / effective_workers)
            batches = [
                reqs_with_users[i:i + chunk_size]
                for i in range(0, total_requests, chunk_size)
            ]

            with ThreadPoolExecutor(max_workers=effective_workers) as ex:
                for f in [ex.submit(_run_batch, b) for b in batches]:
                    f.result()
    finally:
        progress_bar.close()


def send_all(  # pylint: disable=too-many-arguments
    session_obj: Any,
    now_ts: float,
    reqs: Iterable[Dict[str, Any]],
    label: str,
    log_every: int = 10000,
    retries: int = 0,
    backoff_sec: float = 0.5,
    *,
    workers: int = 1,
    session_ctor: Optional[Callable[..., Any]] = None,
    session_args: Optional[Union[Namespace, Mapping[str, Any]]] = None,
    username: Optional[str] = None,
    password: Optional[str] = None,
    token: Optional[str] = None,
) -> None:
    """
    Send all API requests in a batched and optionally multi-threaded way.
    """
    requests_list: List[Dict[str, Any]] = list(reqs)
    progress_bar = tqdm(
        total=len(requests_list),
        desc=label,
        unit="req",
        mininterval=0.5,
    )

    def _run_batch(
        local_session: Any,
        batch: List[Dict[str, Any]]
    ) -> None:
        for i, req in enumerate(batch, 1):
            attempt = 0
            while True:
                try:
                    process_one_request_local(
                        local_session,
                        req,
                        now_ts
                    )
                    progress_bar.update(1)
                    break
                except Exception as exc:  # pylint: disable=broad-except
                    attempt += 1
                    if attempt > retries:
                        logging.error(
                            "[%s] failed: %s | req=%r",
                            label,
                            exc, {"api": req.get("api")}
                        )
                        progress_bar.update(1)
                        break
                    time.sleep(backoff_sec * attempt)
            if log_every and i % log_every == 0:
                logging.info(
                    "[%s] progress=%d/%d",
                    label,
                    progress_bar.n,
                    progress_bar.total
                )

    try:
        if workers <= 1:
            if session_obj is not None:
                _run_batch(session_obj, requests_list)
            else:
                if session_ctor is None:
                    raise RuntimeError("session_ctor cannot be None here")

                with session_ctor(
                    session_args,
                    username,
                    password,
                    token
                ) as s:
                    _run_batch(s, requests_list)
        else:
            if session_ctor is None:
                raise RuntimeError("For workers>1 provide 'session_ctor'.")
            total_requests = len(requests_list) or 1
            effective_workers = max(1, min(workers, total_requests))
            chunk_size = math.ceil(total_requests / effective_workers)
            batches = [
                requests_list[i:i + chunk_size]
                for i in range(0, total_requests, chunk_size)
            ]

            def _open_and_run(batch: List[Dict[str, Any]]) -> None:
                if session_ctor is None:
                    raise RuntimeError("session_ctor cannot be None here")

                with session_ctor(
                    session_args,
                    username,
                    password,
                    token
                ) as s:
                    _run_batch(s, batch)
            with ThreadPoolExecutor(max_workers=effective_workers) as ex:
                for f in [ex.submit(_open_and_run, b) for b in batches]:
                    f.result()

        logging.info("[%s] done total=%d", label, len(requests_list))
    finally:
        progress_bar.close()
