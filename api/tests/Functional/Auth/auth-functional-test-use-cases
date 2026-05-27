# Auth functional tests — use cases

Dokument opisuje paczki testów funkcjonalnych dla flow autoryzacji opartego o HttpOnly cookies, `partial_access_token`, `access_token`, `refresh_token`, sesje oraz PIN.

## Cel testów

Testy funkcjonalne sprawdzają pełne zachowanie endpointów HTTP, a nie tylko pojedyncze klasy. W szczególności obejmują:

- statusy HTTP,
- format odpowiedzi JSON,
- ustawianie i wygaszanie cookies,
- tworzenie i zmianę statusu sesji,
- zapis danych użytkownika,
- blokowanie niepoprawnych stanów flow,
- brak wycieku tokenów w response body.

---

# RegisterTest

Paczka sprawdza endpoint:

```http
POST /api/register
```

## Use case: poprawna rejestracja nowego użytkownika

Sprawdza, że poprawny request rejestracji:

- zwraca poprawny status HTTP,
- tworzy użytkownika w bazie,
- zapisuje zahashowane hasło zamiast plaintextu,
- tworzy sesję dla użytkownika,
- ustawia status sesji `pin_setup_required`,
- nie ustawia jeszcze PIN-u użytkownika,
- ustawia cookie `partial_access_token`,
- nie ustawia cookie `access_token`,
- nie ustawia cookie `refresh_token`,
- ustawia `partial_access_token` jako HttpOnly,
- nie zwraca tokenów w body odpowiedzi.

## Use case: rejestracja z istniejącym emailem

Sprawdza, że próba rejestracji na zajęty email:

- zwraca błąd konfliktu lub walidacji zgodny z aplikacją,
- nie tworzy drugiego użytkownika,
- nie tworzy sesji,
- nie ustawia żadnych cookies autoryzacyjnych.

## Use case: rejestracja z pustym payloadem

Sprawdza, że request bez wymaganych pól:

- zwraca błąd walidacji,
- nie tworzy użytkownika,
- nie tworzy sesji,
- nie ustawia cookies.

## Use case: rejestracja bez username

Sprawdza, że brak pola `username`:

- zwraca błąd walidacji,
- nie tworzy użytkownika,
- nie ustawia cookies.

## Use case: rejestracja bez emaila

Sprawdza, że brak pola `email`:

- zwraca błąd walidacji,
- nie tworzy użytkownika,
- nie ustawia cookies.

## Use case: rejestracja bez hasła

Sprawdza, że brak pola `password`:

- zwraca błąd walidacji,
- nie tworzy użytkownika,
- nie ustawia cookies.

## Use case: rejestracja z pustym username

Sprawdza, że pusty `username`:

- zwraca błąd walidacji,
- nie tworzy użytkownika,
- nie ustawia cookies.

## Use case: rejestracja z niepoprawnym emailem

Sprawdza, że niepoprawny format emaila:

- zwraca błąd walidacji,
- nie tworzy użytkownika,
- nie ustawia cookies.

## Use case: rejestracja ze zbyt krótkim hasłem

Sprawdza, że zbyt krótkie hasło:

- zwraca błąd walidacji,
- nie tworzy użytkownika,
- nie ustawia cookies.

## Use case: rejestracja z uszkodzonym JSON-em

Sprawdza, że malformed JSON:

- zwraca `400 Bad Request`,
- nie tworzy użytkownika,
- nie tworzy sesji,
- nie ustawia cookies.

---

# LoginTest

Paczka sprawdza endpoint:

```http
POST /api/login
```

## Use case: poprawne logowanie użytkownika bez PIN-u

Sprawdza, że użytkownik bez ustawionego PIN-u po poprawnym loginie:

- dostaje odpowiedź `pin_setup_required`,
- dostaje cookie `partial_access_token`,
- nie dostaje cookie `access_token`,
- nie dostaje cookie `refresh_token`,
- ma utworzoną sesję w statusie `pin_setup_required`,
- ma zaktualizowane `lastLoginAt`,
- nie dostaje tokenów w body odpowiedzi.

## Use case: poprawne logowanie użytkownika z PIN-em

Sprawdza, że użytkownik z ustawionym PIN-em po poprawnym loginie:

- dostaje odpowiedź `pin_verification_required`,
- dostaje cookie `partial_access_token`,
- nie dostaje cookie `access_token`,
- nie dostaje cookie `refresh_token`,
- ma utworzoną sesję w statusie `pin_verification_required`,
- ma zaktualizowane `lastLoginAt`,
- nie dostaje tokenów w body odpowiedzi.

## Use case: logowanie na nieistniejący email

Sprawdza, że login dla nieistniejącego użytkownika:

- zwraca `401 Unauthorized`,
- nie tworzy sesji,
- nie ustawia cookies.

## Use case: logowanie z błędnym hasłem

Sprawdza, że błędne hasło:

- zwraca `401 Unauthorized`,
- nie tworzy nowej sesji,
- nie ustawia cookies.

## Use case: logowanie nieaktywnego użytkownika

Sprawdza, że nieaktywny użytkownik:

- nie może się zalogować,
- dostaje `401 Unauthorized`,
- nie ma tworzonej sesji,
- nie dostaje cookies.

## Use case: logowanie z pustym payloadem

Sprawdza, że brak danych logowania:

- zwraca błąd walidacji,
- nie ustawia cookies.

## Use case: logowanie bez emaila

Sprawdza, że brak pola `email`:

- zwraca błąd walidacji,
- nie ustawia cookies.

## Use case: logowanie bez hasła

Sprawdza, że brak pola `password`:

- zwraca błąd walidacji,
- nie ustawia cookies.

## Use case: logowanie z pustym emailem

Sprawdza, że pusty email:

- zwraca błąd walidacji,
- nie ustawia cookies.

## Use case: logowanie z pustym hasłem

Sprawdza, że puste hasło:

- zwraca błąd walidacji,
- nie ustawia cookies.

## Use case: logowanie z niepoprawnym formatem emaila

Sprawdza, że niepoprawny email:

- zwraca błąd walidacji,
- nie ustawia cookies.

## Use case: logowanie z uszkodzonym JSON-em

Sprawdza, że malformed JSON:

- zwraca `400 Bad Request`,
- nie tworzy sesji,
- nie ustawia cookies.

---

# PinSetupTest

Paczka sprawdza endpoint:

```http
POST /api/pin/setup
```

## Use case: ustawienie PIN-u z `partial_access_token`

Sprawdza, że użytkownik po rejestracji lub loginie bez PIN-u może ustawić PIN:

- request z `partial_access_token` przechodzi,
- backend ustawia PIN użytkownika,
- PIN jest zapisany jako hash, nie plaintext,
- sesja zmienia status na `authenticated`,
- pojawia się cookie `access_token`,
- pojawia się cookie `refresh_token`,
- cookie `partial_access_token` zostaje wygaszone,
- `access_token` i `refresh_token` są HttpOnly,
- odpowiedź nie zawiera tokenów w body.

## Use case: ustawienie PIN-u bez tokena

Sprawdza, że request bez żadnego tokena:

- zwraca `401 Unauthorized`,
- nie ustawia żadnych cookies autoryzacyjnych.

## Use case: ustawienie PIN-u z pustym payloadem

Sprawdza, że brak pola `pin`:

- zwraca błąd walidacji,
- nie ustawia PIN-u,
- nie tworzy pełnych cookies,
- sesja pozostaje w statusie `pin_setup_required`.

## Use case: ustawienie PIN-u bez pola `pin`

Sprawdza, że payload bez właściwego pola:

- zwraca błąd walidacji,
- nie ustawia PIN-u,
- nie tworzy `access_token` ani `refresh_token`.

## Use case: ustawienie pustego PIN-u

Sprawdza, że pusty PIN:

- zwraca błąd walidacji,
- nie ustawia PIN-u,
- sesja pozostaje częściowa.

## Use case: ustawienie zbyt krótkiego PIN-u

Sprawdza, że zbyt krótki PIN:

- zwraca błąd walidacji,
- nie ustawia PIN-u,
- nie tworzy pełnej sesji.

## Use case: malformed JSON przy ustawianiu PIN-u

Sprawdza, że uszkodzony JSON:

- zwraca `400 Bad Request`,
- nie ustawia PIN-u,
- sesja pozostaje w statusie `pin_setup_required`.

## Use case: ponowne ustawienie PIN-u po pełnej autoryzacji

Sprawdza, że po poprawnym setupie PIN-u drugi request do `/api/pin/setup`:

- zwraca `403 Forbidden`,
- nie zmienia istniejącego PIN-u,
- sesja pozostaje `authenticated`.

## Use case: setup PIN-u dla użytkownika, który już ma PIN

Sprawdza, że użytkownik posiadający PIN po loginie trafia do flow weryfikacji PIN-u, a nie setupu:

- login ustawia sesję `pin_verification_required`,
- request do `/api/pin/setup` zwraca `403 Forbidden`,
- istniejący PIN nie zostaje nadpisany,
- sesja pozostaje `pin_verification_required`.

---

# PinVerifyTest

Paczka sprawdza endpoint:

```http
POST /api/pin/verify
```

## Use case: poprawna weryfikacja PIN-u z `partial_access_token`

Sprawdza, że użytkownik z ustawionym PIN-em po loginie może zweryfikować PIN:

- login ustawia `partial_access_token`,
- sesja ma status `pin_verification_required`,
- poprawny PIN przełącza sesję na `authenticated`,
- backend ustawia cookie `access_token`,
- backend ustawia cookie `refresh_token`,
- backend wygasza `partial_access_token`,
- pełne cookies są HttpOnly,
- response body nie zawiera tokenów.

## Use case: weryfikacja PIN-u bez tokena

Sprawdza, że request bez tokena:

- zwraca `401 Unauthorized`,
- nie ustawia cookies.

## Use case: błędny PIN

Sprawdza, że błędny PIN:

- zwraca `403 Forbidden`,
- nie autoryzuje sesji,
- nie ustawia `access_token`,
- nie ustawia `refresh_token`,
- sesja pozostaje w statusie `pin_verification_required`.

## Use case: weryfikacja PIN-u z pustym payloadem

Sprawdza, że brak pola `pin`:

- zwraca błąd walidacji,
- nie autoryzuje sesji,
- nie tworzy pełnych cookies.

## Use case: weryfikacja PIN-u bez pola `pin`

Sprawdza, że payload bez właściwego pola:

- zwraca błąd walidacji,
- sesja pozostaje `pin_verification_required`.

## Use case: weryfikacja pustego PIN-u

Sprawdza, że pusty PIN:

- zwraca błąd walidacji,
- nie autoryzuje użytkownika.

## Use case: weryfikacja zbyt krótkiego PIN-u

Sprawdza, że zbyt krótki PIN:

- zwraca błąd walidacji,
- sesja pozostaje częściowa.

## Use case: malformed JSON przy weryfikacji PIN-u

Sprawdza, że uszkodzony JSON:

- zwraca `400 Bad Request`,
- nie autoryzuje sesji.

## Use case: próba weryfikacji PIN-u w statusie `pin_setup_required`

Sprawdza, że użytkownik, który powinien dopiero ustawić PIN:

- nie może wykonać `/api/pin/verify`,
- dostaje `403 Forbidden`,
- nie ma ustawionego PIN-u,
- sesja pozostaje `pin_setup_required`.

## Use case: ponowna weryfikacja PIN-u po pełnej autoryzacji

Sprawdza, że drugi request do `/api/pin/verify` po udanej weryfikacji:

- zwraca `403 Forbidden`,
- sesja pozostaje `authenticated`,
- refresh token pozostaje ustawiony.

## Use case: blokada po trzech błędnych próbach PIN-u

Sprawdza, że po trzech błędnych próbach:

- użytkownik zostaje zablokowany czasowo dla PIN-u,
- `pinLockedUntil` zostaje ustawione,
- sesja nie zostaje autoryzowana,
- nie powstaje refresh token.

---

# RefreshTokenTest

Paczka sprawdza endpoint:

```http
POST /api/token/refresh
```

## Use case: refresh bez `refresh_token`

Sprawdza, że request bez cookie `refresh_token`:

- zwraca `401 Unauthorized`,
- nie ustawia `access_token`,
- nie ustawia `refresh_token`,
- nie ustawia `partial_access_token`.

## Use case: refresh z nieprawidłowym `refresh_token`

Sprawdza, że nieznany lub błędny refresh token:

- zwraca `401 Unauthorized`,
- nie tworzy `access_token`,
- nie tworzy `partial_access_token`.

## Use case: refresh z poprawnym `refresh_token`

Sprawdza, że poprawny refresh token:

- zwraca `200 OK`,
- utrzymuje status sesji `authenticated`,
- ustawia nowy `access_token`,
- ustawia nowy `refresh_token`,
- oba cookies są HttpOnly,
- nowy access token różni się od starego,
- nowy refresh token różni się od starego,
- `partial_access_token` nie jest ustawiany,
- response body nie zawiera tokenów,
- sesja nie jest revoked.

## Use case: stary refresh token po rotacji nie działa

Sprawdza, że po odświeżeniu sesji:

- stary `refresh_token` zostaje unieważniony,
- ponowny refresh z użyciem starego tokena zwraca `401 Unauthorized`.

## Use case: nowy refresh token po rotacji działa

Sprawdza, że po rotacji:

- nowo wydany `refresh_token` może zostać użyty do kolejnego refreshu,
- kolejny refresh również rotuje tokeny,
- drugi refresh token różni się od pierwszego.

## Use case: refresh dla revoked session

Sprawdza, że sesja oznaczona jako revoked:

- nie może zostać odświeżona,
- zwraca `401 Unauthorized`.

## Use case: refresh przy samym `partial_access_token`

Sprawdza, że użytkownik w częściowym flow autoryzacji:

- nie ma `refresh_token`,
- nie może odświeżyć sesji,
- dostaje `401 Unauthorized`.

---

# LogoutTest

Paczka sprawdza endpoint:

```http
POST /api/logout
```

## Use case: logout z pełną autoryzowaną sesją

Sprawdza, że user z ważnym `access_token`:

- może wykonać logout,
- dostaje `204 No Content`,
- sesja zostaje oznaczona jako `revoked`,
- `revokedAt` zostaje ustawione,
- cookie `access_token` zostaje wygaszone,
- cookie `refresh_token` zostaje wygaszone,
- cookie `partial_access_token` zostaje wygaszone.

## Use case: refresh po logout

Sprawdza, że po wylogowaniu:

- próba użycia starego `refresh_token` kończy się `401 Unauthorized`,
- revoked session nie może zostać odświeżona.

## Use case: logout bez tokena

Sprawdza, że request bez żadnego tokena:

- zwraca `401 Unauthorized`,
- nie ustawia ani nie wygasza cookies w odpowiedzi.

## Use case: logout z `partial_access_token`

Sprawdza, że użytkownik w częściowym flow, np. po rejestracji przed PIN-em:

- może wykonać logout,
- dostaje `204 No Content`,
- sesja zostaje oznaczona jako `revoked`,
- `access_token` zostaje wygaszony,
- `refresh_token` zostaje wygaszony,
- `partial_access_token` zostaje wygaszony.

## Use case: drugi logout po pierwszym logout

Sprawdza, że po pierwszym logout:

- cookies są już wygaszone,
- drugi logout nie ma tokena,
- backend zwraca `401 Unauthorized`.

---

# Podsumowanie pokrycia

Aktualny zestaw funkcjonalnych testów auth pokrywa cały cykl życia sesji:

```txt
register
→ partial_access_token
→ pin setup
→ access_token + refresh_token
→ refresh rotation
→ logout
```

oraz alternatywny flow:

```txt
login usera z PIN-em
→ partial_access_token
→ pin verification
→ access_token + refresh_token
→ refresh rotation
→ logout
```

Testy pokrywają też najważniejsze przypadki błędne:

- brak tokena,
- błędny token,
- błędny refresh token,
- revoked session,
- błędne hasło,
- nieistniejący email,
- inactive user,
- malformed JSON,
- invalid payload,
- błędny PIN,
- lock PIN po kilku błędnych próbach,
- próba wykonania endpointu w złym statusie sesji.

## ChangePasswordTest

Paczka testów sprawdza endpoint odpowiedzialny za zmianę hasła zalogowanego użytkownika.

### Endpoint

```http
PATCH /api/password/change

Payload
{
  "oldPassword": "OldPassword123!",
  "newPassword": "NewPassword123!",
  "confirmNewPassword": "NewPassword123!"
}

Use case’y
Zalogowany użytkownik może poprawnie zmienić hasło.
Po zmianie hasła stare hasło nie działa przy ponownym logowaniu.
Po zmianie hasła nowe hasło działa przy ponownym logowaniu.
Próba zmiany hasła z błędnym aktualnym hasłem zwraca 400 Bad Request.
Próba zmiany hasła, gdy newPassword i confirmNewPassword różnią się, zwraca 400 Bad Request.
Próba ustawienia nowego hasła takiego samego jak aktualne zwraca 400 Bad Request.
Niepoprawny payload zwraca 422 Unprocessable Entity.
Pusty payload zwraca błąd walidacji.
Brak pola oldPassword zwraca błąd walidacji.
Brak pola newPassword zwraca błąd walidacji.
Brak pola confirmNewPassword zwraca błąd walidacji.
Puste oldPassword zwraca błąd walidacji.
Puste newPassword zwraca błąd walidacji.
Puste confirmNewPassword zwraca błąd walidacji.
Zbyt krótkie newPassword zwraca błąd walidacji.
Niepoprawny JSON zwraca 400 Bad Request.
Request bez tokena zwraca 401 Unauthorized.
Request z samym partial_access_token zwraca 403 Forbidden.
Zmiana hasła wymaga pełnej autoryzacji przez access_token.

---

# ForgotPasswordTest

Paczka testów sprawdza endpoint odpowiedzialny za rozpoczęcie procesu resetowania hasła przez wysłanie kodu na email.

## Endpoint

```http
POST /api/password/forgot
```

## Payload

```json
{
  "email": "user@example.com"
}
```

## Use case: reset hasła dla istniejącego użytkownika

Sprawdza, że dla istniejącego aktywnego użytkownika:

- endpoint zwraca `200 OK`,
- response ma neutralny status `success`,
- response nie zdradza szczegółów konta,
- w bazie powstaje rekord kodu resetu hasła,
- kod resetu jest zapisany jako hash,
- hash kodu ma długość 64 znaków,
- kod nie jest oznaczony jako użyty,
- kod ma ustawiony czas wygaśnięcia w przyszłości.

## Use case: reset hasła dla nieistniejącego użytkownika

Sprawdza, że dla nieistniejącego emaila:

- endpoint zwraca `200 OK`,
- response jest taki sam jak dla istniejącego użytkownika,
- backend nie zdradza, czy konto istnieje,
- w bazie nie powstaje kod resetu.

## Use case: reset hasła dla nieaktywnego użytkownika

Sprawdza, że dla nieaktywnego użytkownika:

- endpoint zwraca `200 OK`,
- response jest neutralny,
- kod resetu nie zostaje utworzony.

## Use case: błędny payload

Sprawdza, że niepoprawny payload:

- zwraca `422 Unprocessable Entity`,
- obejmuje pusty payload,
- obejmuje brak pola `email`,
- obejmuje puste `email`,
- obejmuje niepoprawny format emaila.

## Use case: malformed JSON

Sprawdza, że niepoprawny JSON:

- zwraca `400 Bad Request`.

---

# ResetPasswordTest

Paczka testów sprawdza endpoint odpowiedzialny za ustawienie nowego hasła na podstawie kodu resetu wysłanego mailem.

## Endpoint

```http
POST /api/password/reset
```

## Payload

```json
{
  "email": "user@example.com",
  "code": "123456",
  "newPassword": "NewPassword123!",
  "confirmNewPassword": "NewPassword123!"
}
```

## Use case: poprawny reset hasła

Sprawdza, że poprawny email, kod i nowe hasła:

- zwracają `200 OK`,
- zwracają status `success`,
- zmieniają hasło użytkownika,
- nowe hasło pasuje do zapisanego hasha,
- stare hasło nie pasuje już do zapisanego hasha,
- kod resetu zostaje oznaczony jako użyty przez ustawienie `usedAt`.

## Use case: stare hasło nie działa po resecie

Sprawdza, że po poprawnym resecie hasła:

- próba logowania starym hasłem zwraca `401 Unauthorized`.

## Use case: nowe hasło działa po resecie

Sprawdza, że po poprawnym resecie hasła:

- logowanie nowym hasłem zwraca `200 OK`.

## Use case: błędny kod resetu

Sprawdza, że niepoprawny kod:

- zwraca `400 Bad Request`,
- zwraca status `error`,
- zwraca komunikat `Invalid or expired password reset code.`,
- nie zmienia hasła użytkownika.

## Use case: wygasły kod resetu

Sprawdza, że kod resetu z `expiresAt` w przeszłości:

- zwraca `400 Bad Request`,
- zwraca status `error`,
- zwraca komunikat `Invalid or expired password reset code.`,
- nie zmienia hasła użytkownika.

## Use case: użyty kod resetu

Sprawdza, że kod oznaczony jako użyty:

- nie może zostać wykorzystany ponownie,
- zwraca `400 Bad Request`.

## Use case: niezgodne nowe hasła

Sprawdza, że gdy `newPassword` i `confirmNewPassword` różnią się:

- backend zwraca `400 Bad Request`,
- zwraca status `error`,
- zwraca komunikat `New password and confirmation do not match.`,
- nie zmienia hasła użytkownika.

## Use case: nowe hasło takie samo jak aktualne

Sprawdza, że próba ustawienia nowego hasła takiego samego jak aktualne:

- zwraca `400 Bad Request`,
- zwraca status `error`,
- zwraca komunikat `New password must be different from current password.`,
- nie zmienia hasła użytkownika.

## Use case: błędny payload

Sprawdza, że niepoprawny payload:

- zwraca `422 Unprocessable Entity`,
- obejmuje pusty payload,
- obejmuje brak pola `email`,
- obejmuje brak pola `code`,
- obejmuje brak pola `newPassword`,
- obejmuje brak pola `confirmNewPassword`,
- obejmuje niepoprawny format emaila,
- obejmuje kod zawierający litery,
- obejmuje zbyt krótki kod,
- obejmuje zbyt krótkie `newPassword`.

## Use case: malformed JSON

Sprawdza, że niepoprawny JSON:

- zwraca `400 Bad Request`.

---

# Dopisek do podsumowania pokrycia

Nowe testy rozszerzają pokrycie funkcjonalne auth o zmianę hasła oraz reset hasła kodem email.

## Flow zmiany hasła

```txt
authenticated user
→ oldPassword + newPassword + confirmNewPassword
→ password changed
→ old password rejected
→ new password accepted
```

## Flow resetu hasła przez kod email

```txt
forgot password
→ generated email code
→ code hash stored in database
→ reset password with email + code + new passwords
→ old password rejected
→ new password accepted
```

## Dodatkowe przypadki błędne

- błędne aktualne hasło przy zmianie hasła,
- niezgodne potwierdzenie nowego hasła,
- nowe hasło takie samo jak aktualne,
- błędny kod resetu hasła,
- wygasły kod resetu hasła,
- użyty kod resetu hasła,
- reset hasła dla nieistniejącego użytkownika nie zdradza istnienia konta,
- reset hasła dla nieaktywnego użytkownika nie tworzy kodu.
