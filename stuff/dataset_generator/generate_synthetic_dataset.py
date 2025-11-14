"""Synthetic seeding entrypoint"""

from __future__ import annotations

from argparse import Namespace
import os
import random
import time
import csv
import json
from typing import Dict, Iterable, List, Optional, Type, Any, Mapping, Union
from dataset_generator.runner import process_one_request_local

from dataset_generator.types import (
    UserCreateParams,
    IdentityCreateParams,
    SchoolCreateParams,
    ProblemCreateParams,
    ContestCreateParams,
    RunCreateParams,
    CourseCreateParams,
    CourseAddStudentParams,
    GroupCreateParams,
    IdentityBulkCreateParams,
)
from dataset_generator.utils import (
    random_base,
    make_request,
    load_config,
    send_all
)


def _iter_users(
    count: int,
    rng: random.Random,
    endpoints: Dict[str, str],
    out_usernames: Optional[List[str]] = None,
) -> Iterable[Dict[str, object]]:
    """
    Yield /user/create/ requests.
    """
    for idx in range(count):
        username = f"user_{idx}_{random_base(6, rng)}"
        if out_usernames is not None:
            out_usernames.append(username)
        params: UserCreateParams = {
            "username": username,
            "email": f"{username}@example.com",
            "password": "Secret.123",
        }
        yield make_request(endpoints["user_create"], params)


def _iter_identities(
    count: int,
    rng: random.Random,
    counts: Dict[str, int],
    endpoints: Dict[str, str],
) -> Iterable[Dict[str, object]]:
    """
    Yield /identity/create/ requests for a fixed group.
    """
    for idx in range(count):
        params: IdentityCreateParams = {
            "gender": "male",
            "name": f"Identity {idx}",
            "password": "Secret.123",
            "school_name": f"Escuela {idx % max(1, counts.get('schools', 1))}",
            "username": f"grupo_generico:identity_{idx}_{random_base(6, rng)}",
            "country_id": "mx",
            "group_alias": "grupo_generico",
        }
        yield make_request(endpoints["identity_create"], params)


def _iter_identity_bulk(
    count: int,
    group_alias: str,
    endpoints: Dict[str, str],
    csv_path: str,
) -> Iterable[Dict[str, object]]:
    """
    Yield /identity/bulkCreate/ requests using existing CSV file.
    Each call creates the existing identities from csv_path
    """

    # Use the provided csv_path parameter
    # Read CSV and convert to JSON format that the API expects
    identities: List[Dict[str, str]] = []
    with open(csv_path, 'r', encoding='utf-8-sig') as file:
        csv_reader = csv.DictReader(file)
        for row in csv_reader:
            identity = {
                "username": f"{group_alias}:{row['username']}",
                "name": row['name'],
                "country_id": row['country_id'].upper(),
                "state_id": row['state_id'].upper(),
                "gender": row['gender'],
                "school_name": row['school_name'],
                "password": "Secret.123",
            }
            identities.append(identity)

    identities_json = json.dumps(identities)

    for _ in range(count):
        params: IdentityBulkCreateParams = {
            "group_alias": group_alias,
            "identities": identities_json,
        }
        yield make_request(endpoints["identity_bulk_create"], params)


def _iter_schools(
    count: int,
    rng: random.Random,
    endpoints: Dict[str, str],
) -> Iterable[Dict[str, object]]:
    """
    Yield /school/create/ requests.
    """
    for idx in range(count):
        params: SchoolCreateParams = {
            "name": f"Escuela {idx} {random_base(4, rng)}",
            "country_id": "mx",
        }
        yield make_request(endpoints["school_create"], params)


def _iter_problems(  # pylint: disable=too-many-arguments
    count: int,
    visibility: str,
    rng: random.Random,
    endpoints: Dict[str, str],
    langs_csv: str,
    selected_tags_json: str,
    zip_path: str,
    out_aliases: List[str],
) -> Iterable[Dict[str, object]]:
    """
    Yield /problem/create/ requests with a valid problem ZIP.
    """
    if not os.path.exists(zip_path):
        raise FileNotFoundError(zip_path)
    for idx in range(count):
        alias = f"prob_{visibility}_{idx}_{random_base(5, rng)}"
        out_aliases.append(alias)
        params: ProblemCreateParams = {
            "visibility": visibility,
            "title": f"Problema {idx} ({visibility})",
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
            "languages": langs_csv,
            "email_clarifications": 1,
            "problem_level": "problemLevelBasicIntroductionToProgramming",
            "selected_tags": selected_tags_json,
        }
        yield make_request(
            endpoints["problem_create"],
            params,
            files={"problem_contents": zip_path},
        )


def _iter_contests(
    count: int,
    kind: str,
    rng: random.Random,
    endpoints: Dict[str, str],
    langs_csv: str,
) -> Iterable[Dict[str, object]]:
    """
    Yield /contest/create/ requests with fixed parameters.
    """
    if kind == "past":
        start_time = "$NOW$-5400"
        finish_time = "$NOW$-1800"
    elif kind == "future":
        start_time = "$NOW$+3600"
        finish_time = "$NOW$+7200"
    else:
        start_time = "$NOW$-1800"
        finish_time = "$NOW$+7200"

    for idx in range(count):
        alias = f"contest_{kind}_{idx}_{random_base(4, rng)}"
        params: ContestCreateParams = {
            "visibility": 1,
            "title": f"Concurso {idx} ({kind})",
            "alias": alias,
            "description": "Concurso generado automáticamente.",
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
            "languages": langs_csv,
            "penalty_calc_policy": "sum",
            "admission_mode": "private",
            "show_scoreboard_after": True,
            "certificate_cutoff": 2,
        }
        yield make_request(endpoints["contest_create"], params)


def _iter_run(
    count: int,
    problem_pool: List[str],
    rng: random.Random,
    endpoints: Dict[str, str],
) -> Iterable[Dict[str, object]]:
    """
    Yield /run/create/ requests cycling through the problem pool.
    """
    pool = problem_pool or []
    size = max(1, len(pool))
    for idx in range(count):
        alias = pool[rng.randrange(size)]
        params: RunCreateParams = {
            "language": "py3",
            "problem_alias": alias,
            "source": f"print({idx})\n",
        }
        yield make_request(endpoints["run_create"], params)


def _iter_courses(
    count: int,
    rng: random.Random,
    endpoints: Dict[str, str],
    out_aliases: Optional[List[str]] = None,
) -> Iterable[Dict[str, object]]:
    """
    Yield /course/create/ requests for public active courses.
    """
    for idx in range(count):
        alias = f"course_active_{idx}_{random_base(5, rng)}"
        if out_aliases is not None:
            out_aliases.append(alias)
        params: CourseCreateParams = {
            "alias": alias,
            "name": f"Curso {idx} (public active)",
            "description": "Curso público activo",
            "start_time": "$NOW$-1800",
            "finish_time": "$NOW$+7200",
            "admission_mode": "public",
        }
        yield make_request(endpoints["course_create"], params)


def _iter_course_add_students_admin(
    course_alias: str,
    usernames: List[str],
    endpoints: Dict[str, str],
    share_user_information: bool = False,
) -> Iterable[Dict[str, object]]:
    """
    Yield /course/addStudent/ requests as admin actions.
    """
    for user_name in usernames:
        params: CourseAddStudentParams = {
            "course_alias": course_alias,
            "usernameOrEmail": user_name,
            "share_user_information": bool(share_user_information),
        }
        yield make_request(endpoints["course_add_student"], params)


def _make_group(
    endpoints: Dict[str, str],
    alias: str,
    name: str,
    description: str,
) -> Dict[str, object]:
    """
    Return a /group/create/ request.
    """
    params: GroupCreateParams = {
        "alias": alias,
        "name": name,
        "description": description,
    }
    return make_request(endpoints["group_create"], params)


def seed_synthetic(
    session: Type[Any],
    *,
    root: str,
    config_path: str,
    seeder_env: str = "testing",
    log_every_runs: int = 50_000,
    session_args: Optional[Union[Namespace, Mapping[str, Any]]] = None,
    ou_username: Optional[str] = None,
    ou_password: Optional[str] = None,
    ou_token: Optional[str] = None,
) -> None:
    """Main entrypoint for generating synthetic data via APIs."""
    os.environ.setdefault("SEEDER_ENV", seeder_env)
    cfg = load_config(config_path, root)

    endpoints: Dict[str, str] = cfg["endpoints"]
    counts: Dict[str, int] = cfg["counts"]
    langs_csv: str = cfg["langs_csv"]
    selected_tags_json: str = cfg["selected_tags_json"]
    test_zip_path: str = cfg["test_zip_path"]

    args = session_args
    username = ou_username
    password = ou_password
    token = ou_token

    now_ts = time.time()
    rng = random.Random(12345)

    dynamic_alias = f"grupo_{int(now_ts)}_{random_base(6, rng)}"
    group_config = cfg.get("generic_group", {}).copy()
    group_config.update({
        "alias": dynamic_alias,
        "name": group_config.get("name", "Grupo Genérico"),
        "description": group_config.get("description",
                                        "Grupo para identidades sintéticas"),
    })

    with session(args, username, password, token) as session_obj:
        process_one_request_local(
            session_obj,
            _make_group(
                endpoints,
                group_config["alias"],
                group_config["name"],
                group_config["description"],
            ),
            now_ts,
        )

        user_usernames: List[str] = []
        send_all(
            None,
            now_ts,
            _iter_users(
                counts.get("users", 0),
                rng,
                endpoints,
                out_usernames=user_usernames,
            ),
            "users",
            workers=8,
            session_ctor=session,
            session_args=args,
            username=username,
            password=password,
            token=token,
            backoff_sec=0.1,
        )

        if counts.get("schools", 0) > 0:
            send_all(
                None,
                now_ts,
                _iter_schools(counts["schools"], rng, endpoints),
                "schools",
                workers=1,
                session_ctor=session,
                session_args=args,
                username=username,
                password=password,
                token=token,
                backoff_sec=0.1,
                retries=2,
            )

        send_all(
            None,
            now_ts,
            _iter_identities(
                counts.get("identities", 0),
                rng,
                counts,
                endpoints,
            ),
            "identities",
            workers=1,
            session_ctor=session,
            session_args=args,
            username=username,
            password=password,
            token=token,
            backoff_sec=0.1,
            retries=2,
        )

        if counts.get("identities_bulk", 0) > 0:
            send_all(
                None,
                now_ts,
                _iter_identity_bulk(
                    counts.get("identities_bulk", 0),
                    group_config["alias"],
                    endpoints,
                    cfg["identities_csv_path"],
                ),
                "identities_bulk",
                workers=1,
                session_ctor=session,
                session_args=args,
                username=username,
                password=password,
                token=token,
                backoff_sec=0.1,
                retries=2,
            )

        pub_aliases: List[str] = []
        priv_aliases: List[str] = []
        send_all(
            None,
            now_ts,
            _iter_problems(
                counts.get("problems_public", 0),
                "public",
                rng,
                endpoints,
                langs_csv,
                selected_tags_json,
                test_zip_path,
                pub_aliases,
            ),
            "problems_public",
            workers=1,
            session_ctor=session,
            session_args=args,
            username=username,
            password=password,
            token=token,
            backoff_sec=0.1,
            retries=2,
        )

        send_all(
            None,
            now_ts,
            _iter_problems(
                counts.get("problems_private", 0),
                "private",
                rng,
                endpoints,
                langs_csv,
                selected_tags_json,
                test_zip_path,
                priv_aliases,
            ),
            "problems_private",
            workers=1,
            session_ctor=session,
            session_args=args,
            username=username,
            password=password,
            token=token,
            backoff_sec=0.1,
            retries=2,
        )

        for kind, key in [
            ("past", "contests_past"),
            ("future", "contests_future"),
            ("active", "contests_active"),
            ("public", "contests_public"),
            ("private", "contests_private"),
        ]:
            send_all(
                None,
                now_ts,
                _iter_contests(
                    counts.get(key, 0),
                    kind,
                    rng,
                    endpoints,
                    langs_csv,
                ),
                f"contests_{kind}",
                workers=1,
                session_ctor=session,
                session_args=args,
                username=username,
                password=password,
                token=token,
                backoff_sec=0.1,
                retries=2,
            )

        problem_pool = pub_aliases or priv_aliases
        send_all(
            None,
            now_ts,
            _iter_run(
                counts.get("run", 0),
                problem_pool,
                rng,
                endpoints,
            ),
            "runs",
            log_every=log_every_runs,
            workers=1,
            session_ctor=session,
            session_args=args,
            username=username,
            password=password,
            token=token,
            backoff_sec=0.1,
            retries=2,
        )

        course_aliases: List[str] = []
        send_all(
            None,
            now_ts,
            _iter_courses(
                counts.get("courses", 0),
                rng,
                endpoints,
                out_aliases=course_aliases,
            ),
            "courses",
            workers=4,
            session_ctor=session,
            session_args=args,
            username=username,
            password=password,
            token=token,
            backoff_sec=0.1,
            retries=2,
        )
