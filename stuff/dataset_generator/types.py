"""TypedDicts for seeder request parameter schemas."""

from __future__ import annotations

from typing import Literal, TypedDict


class UserCreateParams(TypedDict):
    """Parameters for /user/create/."""
    username: str
    email: str
    password: str


class IdentityCreateParams(TypedDict):
    """Parameters for /identity/create/."""
    gender: str
    name: str
    password: str
    school_name: str
    username: str
    country_id: str
    group_alias: str


class SchoolCreateParams(TypedDict):
    """Parameters for /school/create/."""
    name: str
    country_id: str


class ProblemCreateParams(TypedDict):
    """Parameters for /problem/create/."""
    visibility: str
    title: str
    problem_alias: str
    validator: str
    time_limit: int
    validator_time_limit: int
    overall_wall_time_limit: int
    extra_wall_time: int
    memory_limit: int
    output_limit: int
    input_limit: int
    source: str
    show_diff: str
    allow_user_add_tags: str
    languages: str
    email_clarifications: int
    problem_level: str
    selected_tags: str


class ContestCreateParams(TypedDict):
    """Parameters for /contest/create/."""
    visibility: int
    title: str
    alias: str
    description: str
    start_time: str
    finish_time: str
    window_length: int
    scoreboard: float
    score_mode: Literal["partial", "all-or-nothing"] | str
    points_decay_factor: int
    submissions_gap: int
    penalty: int
    feedback: Literal["detailed", "summary"] | str
    penalty_type: str
    languages: str
    penalty_calc_policy: Literal["sum", "max"] | str
    admission_mode: Literal["public", "private"] | str
    show_scoreboard_after: bool
    certificate_cutoff: int


class RunCreateParams(TypedDict):
    """Parameters for /run/create/."""
    language: str
    problem_alias: str
    source: str


class CourseCreateParams(TypedDict):
    """Parameters for /course/create/."""
    alias: str
    name: str
    description: str
    start_time: str
    finish_time: str
    admission_mode: Literal["public", "private"] | str


class CourseAddStudentParams(TypedDict):
    """Parameters for /course/addStudent/."""
    course_alias: str
    usernameOrEmail: str
    share_user_information: bool


class GroupCreateParams(TypedDict):
    """Parameters for /group/create/."""
    alias: str
    name: str
    description: str
