# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project overview

API Siesta is a Symfony 7.1 (PHP >=8.2) REST API for a movie/film-festival voting app (built around the Sitges Film Festival). Users in a group vote on movies; the app also has a CLI importer that pulls the festival's movie/session catalog from a JSON export and enriches it with YouTube trailer links via the Google API client.

## Commands

```bash
composer install                     # install dependencies

docker-compose up                    # php-apache on :8080, MySQL 8.0 on :33060 (db: siesta/siesta/siesta)

bin/console <command>                # Symfony console
bin/console obtain-movies <moviesFile> <sessionsFile> <editionId>   # import festival catalog + trailers

vendor/bin/phpunit                   # run the full test suite
vendor/bin/phpunit tests/Movie/Application/GetMovieByIdUseCaseTest.php  # single test file
vendor/bin/phpunit --filter whenMovieExistsThenReturnsIt              # single test by name

bin/console doctrine:migrations:migrate   # apply migrations (migrations/ uses raw SQL via Doctrine DBAL, no ORM entities)
```

There are no `composer.json` scripts for tests or linting (no `test`, `cs`, `cs-fix`, `stan` scripts), and no ECS/PHPStan/Rector config files exist in the repo — static analysis tooling is not currently set up here, unlike other PHP projects. Run PHPUnit directly as shown above. `rest-api.http` has example requests (REST Client format) against a running local/prod instance, useful as a reference for real payloads.

## Architecture

Hexagonal architecture per bounded context, each under `src/<Context>/{Domain,Application,Infrastructure}`. Framework-bound entry code (HTTP controllers, console commands, event listeners) lives outside `src/`, in `app/`, under the `Siesta\App\` namespace — mirroring the personal convention in `~/.claude/CLAUDE.md`, with one deviation worth knowing:

**Application layer uses `*UseCase` classes, not CQRS Command/CommandHandler pairs.** A use case is a single class with an `execute(...)` method (e.g. `Movie/Application/GetAllMoviesUseCase::execute()`), injected with domain repository interfaces by constructor. Don't introduce Command/CommandHandler splits here unless asked — follow the existing UseCase pattern.

Bounded contexts in `src/`:
- **Movie** — read side of the movie catalog (`GetMovieByIdUseCase`, `GetAllMoviesUseCase`), including votes attached to each movie for display.
- **Vote** — casting and reading votes (`VoteForMovieUseCase`, `GetNextMovieToVoteUseCase`, `GetVotesForMovieByGroupUseCase`).
- **User** — login/auth and group membership (`LoginUserUseCase`, `UserListFromGroupUseCase`, `TokenService` for JWT encode/decode).
- **Extraction** — the festival catalog importer's own model. It has its own `Movie` domain entity, `MovieRepository` interface and Doctrine implementation, separate from the `Movie` context's — this is intentional bounded-context duplication (import/write model vs. read model), not dead code.
- **Shared** — cross-context value objects: `Id`, `Score`, `Date`, `Email`, `Password`, a generic `Collection`, and domain exceptions (`DataNotFound`, `ValueNotValid`, `InternalError`).

`app/` layout:
- `app/Action/<Context>/*Action.php` — invokable Symfony controllers (`extends BaseAction`, `__invoke(Request): Response`). Thin: read the request, call a UseCase, serialize the response. Routes map straight to these classes in `config/routes.yaml` (no route-to-service indirection).
- `app/Listener/JwtChecker.php` — `kernel.request` listener; every route requires a `Bearer` JWT except the ones listed in its `$routes` argument in `config/services.yaml` (currently just `/login`). On success it decodes the JWT claims (`user_id`, `user_name`, `group_id`, `group_name`) and injects them as **request headers** rather than attributes — that's how Actions read the current user/group (see `GetAllMoviesAction` reading `Group-Id` from headers).
- `app/Listener/ExceptionListener.php` — `kernel.exception` listener; maps domain exceptions to HTTP status/JSON body (`ValueNotValid`→400, `DataNotFound`→404, `MethodNotAllowedHttpException`→405, everything else→500). Add new domain exceptions here when they need a specific HTTP status instead of falling through to 500.
- `app/Command/` — console commands. `ObtainMoviesFromJsonCommand` (the `obtain-movies` command) is the festival importer; `MovieTransformer` holds hardcoded lookup maps translating the festival CMS's numeric section/location codes into human-readable names — extend these maps rather than restructuring them when new codes show up. `ObtainMoviesFromLetterboxdCommand` (`obtain-movies-from-letterboxd`) seeds movies (title + YouTube trailer only) from a public Letterboxd list before the official JSON export exists, via the `Extraction\Domain\MovieListFinder` port (`LetterboxdMovieListFinder` adapter, parses the list HTML with `DOMDocument`/`DOMXPath` — no `dom-crawler` dependency, kept out because current versions require PHP 8.4+). Both commands write through the same `Extraction\Domain\MovieRepository::store()`, which upserts by exact movie `title`, so re-running `obtain-movies` later fills in duration/summary/sessions for the same movies. A poster or trailer corrected by a person through `PATCH /movie/{id}/media` sets `movie.poster_locked` / `movie.trailer_locked`, and `store()` never overwrites a locked field.

Persistence uses **Doctrine DBAL `Connection`/QueryBuilder directly**, not the ORM's EntityManager/entities — despite `doctrine/orm` being a dependency. Infrastructure repositories (`Doctrine*Repository` classes) run raw query-builder SQL and hand-map the associative array results into domain entities themselves (see `DoctrineMovieRepository::fromDataToMovie`). Domain repository interfaces only return domain entities, never arrays.

Auth: JWT via `firebase/php-jwt`, secret/algorithm from `JWT_SECRET_KEY`/`JWT_ALGORITHM` env vars, wired in `config/services.yaml`.
