#!/usr/bin/env python3
"""
Populate omegaUp with a large synthetic dataset via _process_one_request.
"""
from __future__ import annotations

import argparse
import logging
import os
import random
import string
import time
from typing import Any, Dict, Iterable, List, Optional

import runpy

ROOT = os.getenv("OMEGAUP_ROOT", os.getcwd())
BOOTSTRAP_PATH = os.path.join(ROOT, "stuff", "bootstrap-environment.py")
bootstrap = runpy.run_path(BOOTSTRAP_PATH)

Session = bootstrap["Session"]
_process_one_request = bootstrap["_process_one_request"]

ENDPOINTS: Dict[str, str] = {
    "user_create": "/user/create/",
    "identity_create": "/identity/create/",
    "school_create": "/school/create/",
    "problem_create": "/problem/create/",
    "contest_create": "/contest/create/",
    "run_create": "/run/create/",
    "course_create": "/course/create/",
    "course_add_admin": "/course/addAdmin/",
    "course_add_student": "/course/addStudent/",
    "group_create": "/group/create/",
}

COUNTS: Dict[str, int] = {
    "users": 1_000,
    "identities": 1_000,
    "schools": 1_000,
    "problems_public": 500,
    "problems_private": 500,
    "contests_past": 1_000,
    "contests_future": 1_000,
    "contests_active": 100,
    "contests_public": 1_000,
    "contests_private": 1_000,
    "run": 2_000,
    "notifications": 2_000,
    "courses": 1_000,
}

LANGS = (
    "c11-gcc,c11-clang,cpp11-gcc,cpp11-clang,cpp17-gcc,cpp17-clang,"
    "cpp20-gcc,cpp20-clang,java,kt,py2,py3,rb,cs,pas,hs,lua,go,rs,js"
)
SELECTED_TAGS = (
    '[{"tagname":"problemTagBinarySearchTree","public":true}]'
)
TEST_ZIP_PATH = os.path.join(
    ROOT, "frontend", "tests", "resources", "testproblem.zip"
)


def random_base(length: int, rng: random.Random) -> str:
    """
    Return a random base36-ish string of the given length.
    """
    chars = string.ascii_lowercase + string.digits
    return "".join(rng.choice(chars) for _ in range(length))


def mk_req(
    api: str,
    params: Dict[str, Any],
    files: Optional[Dict[str, Any]] = None,
) -> Dict[str, Any]:
    """
    Build the request dict expected by _process_one_request.
    """
    out: Dict[str, Any] = {"api": api, "params": params}
    if files:
        out["files"] = files
    return out


def iter_users(
    count: int,
    rng: random.Random,
    out_usernames: Optional[List[str]] = None,
) -> Iterable[Dict[str, Any]]:
    """
    Yield /user/create/ requests.
    """
    for idx in range(count):
        username = f"user_{idx}_{random_base(6, rng)}"
        if out_usernames is not None:
            out_usernames.append(username)
        yield mk_req(
            ENDPOINTS["user_create"],
            {
                "username": username,
                "email": f"{username}@example.com",
                "password": "Secret.123",
            },
        )


def iter_identities(
    count: int,
    rng: random.Random,
) -> Iterable[Dict[str, Any]]:
    """
    Yield /identity/create/ requests for a fixed group.
    """
    for idx in range(count):
        yield mk_req(
            ENDPOINTS["identity_create"],
            {
                "gender": "male",
                "name": f"Identity {idx}",
                "password": "Secret.123",
                "school_name": f"Escuela {idx % COUNTS['schools']}",
                "username": (
                    f"grupo_generico:identity_{idx}_{random_base(6, rng)}"
                ),
                "country_id": "mx",
                "group_alias": "grupo_generico",
            },
        )


def iter_schools(
    count: int,
    rng: random.Random,
) -> Iterable[Dict[str, Any]]:
    """
    Yield /school/create/ requests.
    """
    for idx in range(count):
        yield mk_req(
            ENDPOINTS["school_create"],
            {
                "name": f"Escuela {idx} {random_base(4, rng)}",
                "country_id": "mx",
            },
        )


def iter_problems(
    count: int,
    visibility: str,
    rng: random.Random,
    out_aliases: List[str],
) -> Iterable[Dict[str, Any]]:
    """
    Yield /problem/create/ requests with a valid problem ZIP.
    """
    if not os.path.exists(TEST_ZIP_PATH):
        raise FileNotFoundError(TEST_ZIP_PATH)

    for idx in range(count):
        alias = f"prob_{visibility}_{idx}_{random_base(5, rng)}"
        out_aliases.append(alias)
        title = f"Problema {idx} ({visibility})"
        yield {
            "api": ENDPOINTS["problem_create"],
            "params": {
                "visibility": visibility,
                "title": title,
                "problem_alias": alias,
                "validator": "token-numeric",
                "time_limit": 1000,
                "validator_time_limit": 0,
                "overall_wall_time_limit": 1000,
                "extra_wall_time": 0,
                "memory_limit": 32768,
                "output_limit": 10240,
                "input_limit": 10240,
                "source": "omegaUp classics",
                "show_diff": "examples",
                "allow_user_add_tags": "true",
                "languages": LANGS,
                "email_clarifications": 1,
                "problem_level": (
                    "problemLevelBasicIntroductionToProgramming"
                ),
                "selected_tags": SELECTED_TAGS,
            },
            "files": {
                "problem_contents": TEST_ZIP_PATH,
            },
        }


def iter_contests(
    count: int,
    kind: str,
    rng: random.Random,
) -> Iterable[Dict[str, Any]]:
    """
    Yield /contest/create/ requests with fixed parameters.
    """
    if kind == "past":
        start_time = "$NOW$+-5400"
        finish_time = "$NOW$+-1800"
    elif kind == "future":
        start_time = "$NOW$+3600"
        finish_time = "$NOW$+7200"
    else:
        start_time = "$NOW$+-1800"
        finish_time = "$NOW$+7200"

    for idx in range(count):
        alias = f"contest_{kind}_{idx}_{random_base(4, rng)}"
        yield mk_req(
            ENDPOINTS["contest_create"],
            {
                "visibility": 1,
                "title": f"Concurso {idx} ({kind})",
                "alias": alias,
                "description": (
                    f"Concurso {idx} from autogenerate script."
                ),
                "start_time": start_time,
                "finish_time": finish_time,
                "window_length": 0,
                "scoreboard": 100.0,
                "score_mode": "partial",
                "points_decay_factor": 0,
                "submissions_gap": 1200,
                "penalty": 0,
                "feedback": "detailed",
                "penalty_type": "contest_start",
                "languages": LANGS,
                "penalty_calc_policy": "sum",
                "admission_mode": "private",
                "show_scoreboard_after": True,
                "certificate_cutoff": 2,
            },
        )


def iter_run(
    count: int,
    problem_pool: List[str],
    rng: random.Random,
) -> Iterable[Dict[str, Any]]:
    """
    Yield /run/create/ requests cycling through the problem pool.
    """
    pool_size = max(1, len(problem_pool))
    for idx in range(count):
        alias = problem_pool[rng.randrange(pool_size)]
        yield mk_req(
            ENDPOINTS["run_create"],
            {
                "language": "py3",
                "problem_alias": alias,
                "source": f"print({idx})\n",
            },
        )


def iter_courses(
    count: int,
    rng: random.Random,
    out_aliases: Optional[List[str]] = None,
) -> Iterable[Dict[str, Any]]:
    """
    Yield /course/create/ requests for public active courses.
    """
    for idx in range(count):
        alias = f"course_active_{idx}_{random_base(5, rng)}"
        if out_aliases is not None:
            out_aliases.append(alias)
        yield mk_req(
            ENDPOINTS["course_create"],
            {
                "alias": alias,
                "name": f"Curso {idx} (public active)",
                "description": "Curso público activo",
                "start_time": "$NOW$+-1800",
                "finish_time": "$NOW$+7200",
                "admission_mode": "public",
            },
        )


def iter_course_add_students_admin(
    course_alias: str,
    usernames: List[str],
    share_user_information: bool = False,
) -> Iterable[Dict[str, Any]]:
    """
    Yield /course/addStudent/ requests as admin actions.
    """
    for user_name in usernames:
        yield mk_req(
            ENDPOINTS["course_add_student"],
            {
                "course_alias": course_alias,
                "usernameOrEmail": user_name,
                "share_user_information": bool(share_user_information),
            },
        )


def make_group(
    alias: str,
    name: str,
    description: str,
) -> Dict[str, Any]:
    """
    Return a /group/create/ request.
    """
    return mk_req(
        ENDPOINTS["group_create"],
        {
            "alias": alias,
            "name": name,
            "description": description,
        },
    )


def _send_all(
    session_obj: Any,
    now_ts: float,
    reqs: Iterable[Dict[str, Any]],
    label: str,
    log_every: int = 10000,
) -> None:
    """
    Send a batch of requests with periodic progress logging.
    """
    ok = 0
    fail = 0
    for idx, req in enumerate(reqs, 1):
        try:
            _process_one_request(session_obj, req, now_ts)
            ok += 1
        except Exception as exc:  # pylint: disable=broad-except
            fail += 1
            logging.error("[%s] failed: %s", label, exc)
        if log_every and idx % log_every == 0:
            logging.info(
                "[%s] progress=%d ok=%d fail=%d", label, idx, ok, fail
            )
    logging.info(
        "[%s] done ok=%d fail=%d total=%d", label, ok, fail, ok + fail
    )


def main() -> None:
    """
    Orchestrate seeding: open Session, create group, seed users/identities,
    problems, contests, runs, courses, and enrollments.
    """
    logging.basicConfig(
        level=logging.INFO,
        format="%(levelname)s: %(message)s",
    )

    args = argparse.Namespace(
        root_url=os.getenv("OMEGAUP_ROOT_URL", "http://localhost:8001"),
    )
    username = "omegaup"
    password = "omegaup"
    token = os.getenv("OMEGAUP_TOKEN")

    now_ts = time.time()
    rng = random.Random(12345)

    with Session(
        args,
        username,
        password,
        token,
    ) as session_obj:
        group_req = make_group(
            alias="grupo_generico",
            name="Grupo Genérico",
            description="Grupo para identidades sintéticas",
        )
        _process_one_request(session_obj, group_req, now_ts)

        user_usernames: List[str] = []
        _send_all(
            session_obj,
            now_ts,
            iter_users(COUNTS["users"], rng, out_usernames=user_usernames),
            "users",
        )

        _send_all(
            session_obj,
            now_ts,
            iter_identities(COUNTS["identities"], rng),
            "identities",
        )

        pub_aliases: List[str] = []
        priv_aliases: List[str] = []
        _send_all(
            session_obj,
            now_ts,
            iter_problems(
                COUNTS["problems_public"], "public", rng, pub_aliases
            ),
            "problems_public",
        )
        _send_all(
            session_obj,
            now_ts,
            iter_problems(
                COUNTS["problems_private"], "private", rng, priv_aliases
            ),
            "problems_private",
        )

        for kind, key in [
            ("past", "contests_past"),
            ("future", "contests_future"),
            ("active", "contests_active"),
            ("public", "contests_public"),
            ("private", "contests_private"),
        ]:
            _send_all(
                session_obj,
                now_ts,
                iter_contests(COUNTS[key], kind, rng),
                f"contests_{kind}",
            )

        problem_pool = pub_aliases or priv_aliases
        _send_all(
            session_obj,
            now_ts,
            iter_run(COUNTS["run"], problem_pool, rng),
            "runs",
            log_every=50_000,
        )

        course_aliases: List[str] = []
        _send_all(
            session_obj,
            now_ts,
            iter_courses(COUNTS["courses"], rng, out_aliases=course_aliases),
            "courses",
        )

        if user_usernames and course_aliases:
            per_course = min(
                len(user_usernames),
                (COUNTS["notifications"] + COUNTS["courses"] - 1)
                // max(1, COUNTS["courses"]),
            )
            batch = user_usernames[:per_course]
            for course_alias in course_aliases:
                _send_all(
                    session_obj,
                    now_ts,
                    iter_course_add_students_admin(course_alias, batch),
                    f"course_students:{course_alias}",
                    log_every=50_000,
                )
        else:
            logging.warning("Sin usuarios o cursos para inscribir.")

    raise SystemExit(0)


if __name__ == "__main__":
    main()
