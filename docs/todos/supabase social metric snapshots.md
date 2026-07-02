# Supabase Social Metric Snapshots

ask_after: 2026-07-03T22:29:45+08:00

> Parked (2026-06-17): owner is leaving this open until a snapshot collector (scheduled Laravel job or Supabase Edge Function) exists to write follower/subscriber rows. No action needed before then.

Create and populate the optional Supabase table used by the dashboard for YouTube, TikTok, and other creator connection growth charts.

Recommended table name:

```text
livelatch_social_metric_snapshots
```

Recommended columns are documented in:

```text
docs/platform-runtime/dashboard-analytics-data.md
```

The dashboard already reads this table through `SUPABASE_SOCIAL_METRICS_TABLE`. It will show connected accounts without graphs until follower/subscriber snapshot rows are being written.
