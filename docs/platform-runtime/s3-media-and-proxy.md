# S3 Media And Proxy

The media-storage work on 2026-05-14 is one of the first major architectural departures from stock LinkStack. The key commits are `a06a373`, `94fb68f`, `6e46408`, `7d6435d`, and `41707fc`.

## What changed

- profile image uploads moved away from local `assets/img` assumptions
- S3-compatible storage became the backing store for profile images
- `users.profile_image` was introduced to store the canonical image path
- a media proxy path was added so private media can be served through app-controlled URLs
- the dashboard, studio, public pages, and related avatar views were updated to resolve images more safely

## Why this is significant

This work solves two separate problems:

1. it makes cloud-backed media practical for the platform
2. it avoids exposing raw storage URLs or credentials directly to clients

## Current behavior

- Laravel resolves default avatars, legacy local filenames, full URLs, and S3 object paths
- `MediaController` exposes profile media through app routes
- cache behavior was added to support browser and edge caching

## Design consequence

Any future feature that needs private or semi-private media should study this pattern before inventing a new one. The media proxy approach is already the established direction for sensitive asset delivery.
