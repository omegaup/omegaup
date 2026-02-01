"""
This script is used to rejudge all the submissions with verdict CE in the
timeframe when omegaUp runners were down.
"""

import datetime
import json
import argparse
from getpass import getpass
import logging
from typing import Callable, Any
import requests
from tqdm import tqdm  # type: ignore
from tqdm.contrib.logging import logging_redirect_tqdm  # type: ignore


logging.basicConfig(level=logging.INFO)
logging.getLogger("httpx").setLevel(logging.WARNING)
LOG = logging.getLogger(__name__)

USERNAME = None
PASSWORD = None
ROWCOUNT = None
BASE_URL = None
COOKIES = None


def get_login_endpoint(username: str, password: str) -> str:
    """endpoint for logging in"""
    return f"api/user/login?usernameOrEmail={username}&password={password}"


def rejudge_submission_endpoint(  # pylint: disable=R0913
    run_alias: str,
) -> str:
    """
    Endpoint for rejudging a submission
    """
    return (
        f"api/run/rejudge?"
        f"run_alias={run_alias}"
    )


def get_runs_list_endpoint(
    rowcount: int,
    offset: int
) -> str:
    """
    returns the list of run_ids from days when omegaUp runners were down.
    """
    endpoint = (
        f"/api/run/list?"
        f"offset={offset}"
        f"&rowcount={rowcount}"
    )

    return endpoint


def get_contents_from_url(
    get_endpoint_fn: Callable[..., str],
    args: dict[str, Any] | None = None
) -> Any:
    """hit the endpoint with GET request"""
    global COOKIES  # pylint: disable=W0603

    if args is None:
        args = {}
    endpoint = get_endpoint_fn(**args)
    url = f"{BASE_URL}/{endpoint}"

    if get_endpoint_fn == get_login_endpoint:  # pylint: disable=W0143
        COOKIES = None

    try:
        if COOKIES is None:
            response = requests.get(url, timeout=10)
            response.raise_for_status()
            COOKIES = response.cookies
        else:
            response = requests.get(url, COOKIES, timeout=10)
            response.raise_for_status()
        data = response.json()
        return data
    except requests.exceptions.RequestException as e:
        LOG.error("An error occurred during the request: %s", e)
        raise
    except json.JSONDecodeError as e:
        LOG.error("JSON decoding failed: %s", e)
        raise


def get_run_ids_list() -> list[str]:
    """
    Extracts global show-run IDs .

    Returns:
        list: List of all the run IDs in a given timeframe
    """
    runs = get_contents_from_url(
        get_runs_list_endpoint,
        {"rowcount": ROWCOUNT, "offset": 0},
    )["runs"]

    start_time = int(datetime.datetime(2025, 2, 20, 0, 0).timestamp())
    end_time = int(datetime.datetime(2025, 2, 21, 23, 59, 59).timestamp())

    run_ids_and_usernames = [
        item["guid"]
        for item in runs
        if item["time"] >= start_time and item["time"] <= end_time
        and item["verdict"] == "CE"
    ]

    return run_ids_and_usernames


def process_rejudge_for_single_run(run_id: str) -> None:
    """
    Process rejudge for a single run

    Returns:
    None
    """

    get_contents_from_url(
        rejudge_submission_endpoint,
        {
            "run_alias": run_id,
        }
    )


def regudge_all_ce_submissions() -> None:
    """
    Regudge all the submissions with verdict CE in the timeframe of omegaUp
    runners were down
    """
    get_contents_from_url(
        get_login_endpoint, {"username": USERNAME, "password": PASSWORD}
    )
    run_ids_list = get_run_ids_list()

    with logging_redirect_tqdm():
        for run_id in tqdm(run_ids_list):
            process_rejudge_for_single_run(run_id)
        print(f"Total of rejudged submissions: {len(run_ids_list)}")


def handle_input() -> None:
    """
    Handles input from the user
    """
    global USERNAME, PASSWORD, BASE_URL, ROWCOUNT  # pylint: disable=W0603
    parser = argparse.ArgumentParser(
        description="Process feedbacks from students"
    )
    parser.add_argument("--username", type=str, help="Your username")
    parser.add_argument("--password", type=str, help="Your password")
    parser.add_argument("--url", type=str, default="https://omegaup.com")
    parser.add_argument("--rowcount", type=int, default=100)

    args = parser.parse_args()

    USERNAME = args.username or input("Enter your username: ")
    PASSWORD = args.password or getpass("Enter your password: ")
    BASE_URL = args.url
    ROWCOUNT = args.rowcount


def main() -> None:
    """
    Takes the input from the user and rejude all the CE submissions
    """

    handle_input()

    regudge_all_ce_submissions()


if __name__ == "__main__":
    main()
