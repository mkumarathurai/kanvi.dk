# Kanvi continuity

At the start of a new Kanvi session, read these before planning or editing:

1. [Knowledge Base rules](https://drive.google.com/file/d/1ptHkv7HcHHQM8lQfX40ax63qHbs7tgd6/view).
2. [Kanvi project overview](https://drive.google.com/file/d/1Fe-URgOMgkIXbZ5v6xawiORAYvPkTUKQ/view), including the complete decisions section.
3. [Technical snapshot](docs/SNAPSHOT.md). Check its branch, HEAD and unfinished
   work against the current repository; preserve uncommitted changes.
4. The [KAN board](https://mkumarathurai.atlassian.net/jira/software/c/projects/KAN/boards/575),
   which holds the outstanding work. Commit trailers carry its keys; see
   [CLAUDE.md](CLAUDE.md) for the convention and the historical exception.

Use the Google Drive connector for the shared overview. Its current location is
`Knowledge Base/01-Indbakke/Kanvi`, pending Mathi's choice of project area.
If Drive is unavailable, state that limitation and use the local snapshot;
do not claim to have synchronized the Knowledge Base.

For product behavior, read `docs/kanvi-product-solution-spec-v1.1.md` and the
relevant domain documents. For visual changes, read `docs/design/DESIGN-SYSTEM.md`;
the written spec and final v2 logo take precedence over older reference images.

Before reporting completion, consult the shared
[Definition of Done](https://drive.google.com/file/d/1G4E01I1tW3iyNjcmxdDlqMpvRSc1x9cl/view)
and [Verification](https://drive.google.com/file/d/1seGGvDmKA9Eq3XQljUv9FsrizSevRN41/view).
Distinguish verified local work from unverified CI, mail clients and production.

When asked to save a snapshot, update `docs/SNAPSHOT.md` with technical evidence
and the Drive overview with project knowledge. Keep code, commands, hashes and
credentials out of the shared overview. Preserve earlier decisions and record
changes with reasons. A snapshot request alone does not request a commit or deployment.
