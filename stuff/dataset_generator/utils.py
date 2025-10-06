from __future__ import annotations
from typing import Any, Dict, Iterable, Callable, Mapping, Optional, TypeVar
import logging
import os
import random
import string
import json
import time
import yaml

ParamT = TypeVar("ParamT", bound=Mapping[str, object])

def random_base(
        length: int, 
        rng: random.Random
    ) -> str:
    if length < 0:
        raise ValueError("length must be >= 0")
    alphabet = string.ascii_lowercase + string.digits
    return "".join(rng.choice(alphabet) for _ in range(length))


def make_request(
    api: str,
    params: ParamT,
    files: Optional[Dict[str, str]] = None,
) -> Dict[str, object]:
    if not api or not isinstance(api, str):
        raise ValueError("api must be a non-empty string")

    out: Dict[str, object] = {"api": api, "params": params}
    if files is not None:
        if not isinstance(files, dict):
            raise ValueError("files must be a dict when provided")
        out["files"] = files
    return out


def _resolve_path(
        root: str, 
        maybe_rel_path: str
    ) -> str:
    if os.path.isabs(maybe_rel_path):
        return maybe_rel_path
    return os.path.join(root, maybe_rel_path)


def _safe_load_yaml(path: str) -> Dict[str, Any]:
    if not path:
        return {}
    if not os.path.exists(path):
        logging.warning("Config YAML no encontrado en %s; se usarán valores por defecto vacíos.", path)
        return {}
    with open(path, "r", encoding="utf-8") as fh:
        data = yaml.safe_load(fh) or {}
    return data if isinstance(data, dict) else {}


def load_config(config_path: Optional[str], root: str) -> Dict[str, Any]:
    raw = _safe_load_yaml(config_path)

    endpoints = raw.get("endpoints") or {}
    counts = raw.get("counts") or {}

    langs_list = raw.get("langs") or []
    if not isinstance(langs_list, list):
        langs_list = []
    langs_csv = ",".join(langs_list)

    selected_tags = raw.get("selected_tags") or []
    if not isinstance(selected_tags, list):
        selected_tags = []
    selected_tags_json = json.dumps(selected_tags, ensure_ascii=False)

    test_zip_path = raw.get("test_zip_path") or "frontend/tests/resources/testproblem.zip"
    test_zip_path = _resolve_path(root, test_zip_path)

    return {
        "endpoints": endpoints,
        "counts": counts,
        "langs_csv": langs_csv,
        "selected_tags_json": selected_tags_json,
        "test_zip_path": test_zip_path,
    }


def send_all(
    session_obj: Any,
    now_ts: float,
    reqs: Iterable[Dict[str, Any]],
    label: str,
    process_one_request: Callable[[Any, Dict[str, Any], float], None],
    log_every: int = 10000,
    retries: int = 0,
    backoff_sec: float = 0.5,
) -> None:
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
            logging.info("[%s] progress=%d ok=%d fail=%d", label, idx, ok, fail)
    logging.info("[%s] done ok=%d fail=%d total=%d", label, ok, fail, ok + fail)
