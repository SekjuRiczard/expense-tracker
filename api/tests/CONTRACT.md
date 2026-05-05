# API regression contract — auth refactor stage 1

This file documents the current public API behavior that must stay unchanged during the refactor.

## Register

`POST /api/register`

Success:

- HTTP `201`
- `status`: `pin_setup_required`
- `message`: `User created.\nPIN setup required.`
- body contains `token`
- body does not contain `refreshToken`
- `user.hasPin`: `false`

Duplicate email:

- HTTP `409`
- `status`: `error`
- `message`: `User with email "<email>" already exists.`

## Login

`POST /api/login`

User without PIN:

- HTTP `200`
- `status`: `pin_setup_required`
- `message`: `Password verified.\nPIN setup required.`
- body contains `token`
- body does not contain `refreshToken`
- `user.hasPin`: `false`

User with PIN:

- HTTP `200`
- `status`: `pin_verification_required`
- `message`: `Password verified. PIN verification required.`
- body contains `token`
- body does not contain `refreshToken`
- `user.hasPin`: `true`

Invalid credentials:

- HTTP `401`
- `status`: `error`
- `message`: `Invalid email or password.`

Too many attempts:

- HTTP `429`
- `status`: `error`
- `message`: `Too many login attempts.\nTry again later.`

## PIN setup

`POST /api/pin/setup`

Success:

- HTTP `200`
- `status`: `authenticated`
- `message`: `PIN successfully set up.`
- body contains `token`
- body contains `refreshToken`
- `user.hasPin`: `true`

Old partial token after setup:

- HTTP `401`
- `status`: `unauthorized`
- `message`: `Invalid or expired session.`

## PIN verify

`POST /api/pin/verify`

Success:

- HTTP `200`
- `status`: `authenticated`
- `message`: `PIN verified successfully.`
- body contains `token`
- body contains `refreshToken`
- `user.hasPin`: `true`

Wrong PIN:

- HTTP `403`
- `status`: `error`
- `message`: `Invalid PIN.`

Old partial token after verification:

- HTTP `401`
- `status`: `unauthorized`
- `message`: `Invalid or expired session.`

## PIN change

`PUT /api/pin/change`

Success:

- HTTP `200`
- `status`: `success`
- `message`: `PIN successfully changed.`

Partial login token:

- HTTP `403`
- `status`: `pin_verification_required`
- `message`: `PIN authorization is required to access this resource.`

## Refresh token

`POST /api/token/refresh`

Success:

- HTTP `200`
- `status`: `authenticated`
- `message`: `Token refreshed successfully.`
- body contains new `token`
- body contains new `refreshToken`
- `user.hasPin`: `true`

Invalid or rotated refresh token:

- HTTP `401`
- `status`: `error`
- `message`: `Invalid or expired refresh token.`

## Current user

`GET /api/me`

Partial setup session:

- HTTP `200`
- `status`: `pin_setup_required`
- `user.hasPin`: `false`

Partial verification session:

- HTTP `200`
- `status`: `pin_verification_required`
- `user.hasPin`: `true`

Authenticated session:

- HTTP `200`
- `status`: `authenticated`
- `user.hasPin`: `true`

Without token:

- HTTP `401`
- body contains `message`

## Logout

`POST /api/logout`

Success:

- HTTP `200`
- `status`: `success`
- `message`: `Logged out successfully.`

After logout, the same access token must no longer authorize protected operations.
