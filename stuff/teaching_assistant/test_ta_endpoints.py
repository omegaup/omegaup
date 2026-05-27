"""Endpoint construction tests for the teaching assistant."""

from teaching_assistant import get_runs_from_course_endpoint


def test_get_runs_from_course_endpoint_builds_a_single_query_path() -> None:
    """The runs endpoint should not duplicate its path prefix."""

    endpoint = get_runs_from_course_endpoint(
        course_alias="course",
        assignment_alias="assignment",
    )

    assert endpoint == (
        "api/course/runs?"
        "course_alias=course&"
        "assignment_alias=assignment"
    )