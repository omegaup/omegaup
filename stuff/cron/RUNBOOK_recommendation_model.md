# Recommendation model training runbook

This covers the scheduled training of the problem recommendation model
(`build_problem_rec_model.py`) and the control plane pieces around it.

## What the job does

Every solve feeds a co-occurrence model that answers "a user just solved problem
X, what should they try next". `build_problem_rec_model.py` reads accepted
submissions, trains that model, measures its quality with a MAP score (mean
average precision, higher is better), and writes the model out as an SQLite file
that the serving side reads.

## Schedule

The job is registered in the `Cron_Jobs` registry with a weekly schedule
(`0 4 * * 0`, Sundays 04:00 UTC). Training is heavier than the daily jobs and the
model does not need to change more often than that. The registry row is
informational in this repo; the real scheduling is the Kubernetes CronJob in the
`omegaup/prod` repo, which needs the same entry added there (a one line change).

## The guardrail (write audit publish)

A bad training run must never replace a good model. Before the new model is
published it is audited:

- if its MAP score is below `--min-map-score` (default 0.3) it is not saved;
- if its MAP score regressed more than `--max-map-regression` (default 0.05)
  below the last published model, it is not saved.

When the audit fails the previous model file stays in place, the run is recorded
as a failure with a `skip_reason`, and the cron exits non zero so the platform
alerting notices. This is the same idea as a pre deploy smoke test.

## Where the history lives

Each run records two rows:

- `Cron_Runs` (via the shared runner): start, end, status, duration, per phase
  timings, and the dataset size in `rows_affected`.
- `Recommendation_Model_Runs`: the MAP score, dataset size, training params,
  output path, whether it was `published`, and the `skip_reason` if it was not.

The `cron_run_id` column links the two, so the model quality history and the cron
execution history line up.

## Running it by hand

Against the real database:

    python3 stuff/cron/build_problem_rec_model.py --output /path/to/model.db

Local dry run that reads from a sqlite fixture and records nothing:

    python3 stuff/cron/build_problem_rec_model.py \
        --sqlite-database stuff/cron/testdata.db \
        --output /tmp/model.db --no-track

Force a deterministic run (useful for reproducing a score):

    python3 stuff/cron/build_problem_rec_model.py --output /tmp/model.db \
        --rng-seed 0

## How to check on it

- Admin dashboard: `/admin/crons` lists `build_problem_rec_model.py` with its
  recent runs, status, duration and dataset size.
- Database, latest quality:

      SELECT map_score, dataset_size, published, skip_reason, created_at
      FROM Recommendation_Model_Runs
      ORDER BY created_at DESC
      LIMIT 10;

- Last model that actually went live:

      SELECT map_score, output_path, created_at
      FROM Recommendation_Model_Runs
      WHERE published = 1
      ORDER BY created_at DESC
      LIMIT 1;

## Recovering from a bad run

If a run is skipped by the guardrail the previous model is still the live one, so
there is usually nothing to do. Investigate why the score dropped (a data issue,
too few submissions, a parameter change) and rerun once the cause is understood.
The rerun can be triggered from the admin dashboard.
