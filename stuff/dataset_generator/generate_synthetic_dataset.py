"""Synthetic seeding entrypoint"""

from __future__ import annotations

from argparse import Namespace
import os
import random
import time
import csv
import json
from typing import Dict, Iterable, List, Optional, Type, Any, Mapping, Union

from dataset_generator.types import (
    UserCreateParams,
    SchoolCreateParams,
    ProblemCreateParams,
    ContestCreateParams,
    RunCreateParams,
    CourseCreateParams,
    GroupCreateParams,
    IdentityBulkCreateParams,
)
from dataset_generator.utils import (
    random_base,
    make_request,
    load_config,
    send_all,
    distribute_users_to_requests,
    send_all_with_distributed_users
)


def _iter_users(
    count: int,
    rng: random.Random,
    endpoints: Dict[str, str],
    out_usernames: Optional[List[Dict[str, str]]] = None,
    password: str = "Secret.123"
) -> Iterable[Dict[str, object]]:
    """
    Yield /user/create/ requests.
    """
    for idx in range(count):
        username = f"user_{idx}_{random_base(6, rng)}"
        if out_usernames is not None:
            out_usernames.append({'username': username, 'password': password})
        params: UserCreateParams = {
            "username": username,
            "email": f"{username}@example.com",
            "password": "Secret.123",
        }
        yield make_request(endpoints["user_create"], params)


def _iter_identity_bulk(
    count: int,
    endpoints: Dict[str, str],
    csv_path: str,
    rng: random.Random,
) -> Iterable[Dict[str, object]]:
    """
    Yield /identity/bulkCreate/ requests using existing CSV file.
    Each call creates the existing identities from csv_path
    """
    # Use the provided csv_path parameter
    # Read CSV and convert to JSON format that the API expects
    for i in range(count):
        group_alias = f"synthetic_group_{random_base(6, rng)}"

        yield _make_group(
            endpoints,
            alias=group_alias,
            name=f"Grupo Sintético {i+1}",
            description="Grupo para identidades bulk sintéticas",
        )

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
    Yield /course/create/ requests for private active courses.
    """
    for idx in range(count):
        alias = f"course_active_{idx}_{random_base(5, rng)}"
        if out_aliases is not None:
            out_aliases.append(alias)
        params: CourseCreateParams = {
            "alias": alias,
            "name": f"Curso {idx} (public active)",
            "description": "Curso privado activo",
            "start_time": "$NOW$-1800",
            "finish_time": "$NOW$+7200",
            "admission_mode": "private",
        }
        yield make_request(endpoints["course_create"], params)


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


def get_workers_for_endpoint(
    workers: Dict[str, int],
    endpoint_key: str
) -> int:
    """
    Return the position of the specific worker
    """
    if endpoint_key in workers:
        return int(workers[endpoint_key])
    return int(workers.get("default", 1))


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
    rng = random.Random(int(now_ts * 1000000))

    with session(args, username, password, token):
        user_credentials: List[Dict[str, str]] = []
        send_all(
            None,
            now_ts,
            _iter_users(
                counts.get("users", 0),
                rng,
                endpoints,
                out_usernames=user_credentials,
            ),
            "users",
            workers=get_workers_for_endpoint(cfg["workers"], "user_create"),
            session_ctor=session,
            session_args=args,
            username=username,
            password=password,
            token=token,
            backoff_sec=0.1,
        )

        if user_credentials:
            schools_reqs = distribute_users_to_requests(
                user_credentials,
                _iter_schools(counts["schools"], rng, endpoints),
            )
            send_all_with_distributed_users(
                now_ts,
                schools_reqs,
                "schools",
                workers=get_workers_for_endpoint(
                    cfg["workers"],
                    "school_create"
                ),
                session_ctor=session,
                session_args=args,
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
                    endpoints,
                    cfg["identities_csv_path"],
                    rng,
                ),
                "identities_bulk",
                workers=get_workers_for_endpoint(
                    cfg["workers"],
                    "identity_bulk_create"
                ),
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
        if user_credentials:
            problems_public_reqs = distribute_users_to_requests(
                user_credentials,
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
            )
            send_all_with_distributed_users(
                now_ts,
                problems_public_reqs,
                "problems_public",
                workers=get_workers_for_endpoint(
                    cfg["workers"],
                    "problem_create"
                ),
                session_ctor=session,
                session_args=args,
                token=token,
                backoff_sec=0.1,
                retries=2,
            )

            problems_private_reqs = distribute_users_to_requests(
                user_credentials,
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
            )
            send_all_with_distributed_users(
                now_ts,
                problems_private_reqs,
                "problems_private",
                workers=get_workers_for_endpoint(
                    cfg["workers"],
                    "problem_create"
                ),
                session_ctor=session,
                session_args=args,
                token=token,
                backoff_sec=0.1,
                retries=2,
            )

        if user_credentials:
            for kind, key in [
                ("past", "contests_past"),
                ("future", "contests_future"),
                ("active", "contests_active"),
                ("public", "contests_public"),
                ("private", "contests_private"),
            ]:
                contests_reqs = distribute_users_to_requests(
                    user_credentials,
                    _iter_contests(
                        counts.get(key, 0),
                        kind,
                        rng,
                        endpoints,
                        langs_csv,
                    ),
                )
                send_all_with_distributed_users(
                    now_ts,
                    contests_reqs,
                    f"contests_{kind}",
                    workers=get_workers_for_endpoint(
                        cfg["workers"],
                        "contest_create"
                    ),
                    session_ctor=session,
                    session_args=args,
                    token=token,
                    backoff_sec=0.1,
                    retries=2,
                )

        if user_credentials:
            problem_pool = pub_aliases or priv_aliases
            runs_reqs = distribute_users_to_requests(
                user_credentials,
                _iter_run(
                    counts.get("run", 0),
                    problem_pool,
                    rng,
                    endpoints,
                ),
            )
            send_all_with_distributed_users(
                now_ts,
                runs_reqs,
                "runs",
                log_every=log_every_runs,
                workers=get_workers_for_endpoint(
                    cfg["workers"],
                    "run_create"
                ),
                session_ctor=session,
                session_args=args,
                token=token,
                backoff_sec=0.1,
                retries=2,
            )

            course_aliases: List[str] = []
            courses_reqs = distribute_users_to_requests(
                user_credentials,
                _iter_courses(
                    counts.get("courses", 0),
                    rng,
                    endpoints,
                    out_aliases=course_aliases,
                ),
            )
            send_all_with_distributed_users(
                now_ts,
                courses_reqs,
                "courses",
                workers=get_workers_for_endpoint(
                    cfg["workers"],
                    "course_create"
                ),
                session_ctor=session,
                session_args=args,
                token=token,
                backoff_sec=0.1,
                retries=2,
            )
