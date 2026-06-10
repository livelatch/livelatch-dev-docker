# Profile Preview System

The Open Graph work introduced branded preview generation for public profile pages. The main commits are `750cab6`, `67e21b9`, `cc59f68`, `e2209c7`, and `c45776a`.

## What changed

- public profiles gained dynamic Open Graph and Twitter Card metadata
- generated preview image routes were added
- an internal editor, `opengraph.php`, was introduced to shape the card design
- Discord compatibility issues were fixed
- preview generation shifted toward PNG output and improved rendering quality

## Why this matters

This is not just cosmetic metadata. It affects how Livelatch profiles appear when shared across platforms, which directly shapes discoverability and creator presentation.

## Important implementation direction

- generated previews should be treated as PNG in production output
- rendering quality matters more than raw generation simplicity because social crawlers are unforgiving
- Discord-specific behavior already forced implementation changes once, so cross-platform preview testing should remain part of the workflow

## Follow-up context

The summary notes include an explicit owner reminder about font rendering for the Open Graph system. That is a valid next refinement area because typography quality is highly visible in generated social assets.
