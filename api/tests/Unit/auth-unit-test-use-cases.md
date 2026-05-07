# Auth — unit test use cases

Dokument opisuje aktualne paczki testów jednostkowych dla modułu autoryzacji i sesji. Celem unit testów jest sprawdzenie małych klas w izolacji: bez pełnego requestu HTTP, bez prawdziwej bazy danych i bez faktycznego generowania cookies przez kernel.

## Zakres aktualnych unit testów

Aktualnie pokryte są następujące paczki:

```txt
Unit/Auth/Factory/CookieFactoryTest
Unit/Auth/Security/CookieTokenExtractorTest
Unit/Auth/Service/PinServiceTest
Unit/Session/Service/SessionServiceTest
Unit/Auth/Service/AuthTokenServiceTest
```

## 1. `CookieFactoryTest`

### Testowana klasa

```txt
App\Auth\Factory\CookieFactory
```

### Odpowiedzialność klasy

`CookieFactory` odpowiada za tworzenie i wygaszanie ciasteczek używanych w HttpOnly auth flow:

```txt
access_token
refresh_token
partial_access_token
```

Testy sprawdzają, czy cookies mają poprawne właściwości bezpieczeństwa i techniczne parametry wymagane przez backend/frontend flow.

### Use case'y

| Use case | Co sprawdza test |
|---|---|
| Tworzenie cookie HttpOnly | Cookie utworzone przez factory ma flagę `HttpOnly`. |
| Root path | Cookie ma ścieżkę `/`, więc jest dostępne dla całego API. |
| SameSite Lax | Cookie ma `SameSite=Lax`. |
| Secure cookie w trybie secure | Gdy factory dostaje `cookieSecure=true`, cookie ma flagę `Secure`. |
| Non-secure cookie w trybie lokalnym | Gdy factory dostaje `cookieSecure=false`, cookie nie ma flagi `Secure`. |
| TTL cookie | Expiration time jest ustawiany na podstawie przekazanego TTL. |
| Wygaszanie cookie | `expireCookie()` tworzy cookie z pustą wartością i czasem wygaśnięcia w przeszłości. |
| Wygaszanie zachowuje path i SameSite | Expired cookie dalej ma `/` i `SameSite=Lax`, żeby przeglądarka poprawnie usunęła właściwe cookie. |
| Wygaszanie respektuje Secure | Expired cookie również respektuje flagę `cookieSecure`. |

### Dlaczego to jest ważne

Jeżeli factory ustawi inne parametry przy tworzeniu i usuwaniu cookie, przeglądarka może nie nadpisać albo nie usunąć starego cookie. To mogłoby powodować błędy typu użytkownik ma równocześnie stare `partial_access_token` albo nieusunięty `refresh_token`.

---

## 2. `CookieTokenExtractorTest`

### Testowana klasa

```txt
App\Auth\Security\CookieTokenExtractor
```

### Odpowiedzialność klasy

`CookieTokenExtractor` odpowiada za wyciągnięcie tokena JWT z requestu. Obsługuje dwa typy access tokenów:

```txt
access_token          — pełna autoryzacja
partial_access_token  — częściowa autoryzacja przed PIN setup / PIN verify
```

### Use case'y

| Use case | Co sprawdza test |
|---|---|
| Istnieje `access_token` | Extractor zwraca wartość `access_token`. |
| Brak `access_token`, istnieje `partial_access_token` | Extractor zwraca wartość `partial_access_token`. |
| Istnieją oba cookies | Extractor preferuje `access_token`. |
| Brak obu cookies | Extractor zwraca `false`. |
| Pusty `access_token`, poprawny `partial_access_token` | Extractor ignoruje pusty access token i zwraca partial token. |
| Oba cookies są puste | Extractor zwraca `false`. |

### Dlaczego to jest ważne

To zabezpiecza regresję błędu, w którym backend widział cookie w requestcie, ale warstwa JWT nie potrafiła go użyć, bo część kodu czytała tylko `access_token`, a flow PIN-u używało `partial_access_token`.

---

## 3. `PinServiceTest`

### Testowana klasa

```txt
App\Auth\Service\PinService
```

### Odpowiedzialność klasy

`PinService` obsługuje logikę domenową PIN-u:

```txt
setup PIN
verify PIN
change PIN
failed attempts
lockout after invalid PIN attempts
```

### Use case'y

| Use case | Co sprawdza test |
|---|---|
| Setup PIN-u | `setupPin()` hashuje PIN i zapisuje go na użytkowniku. |
| PIN nie jest przechowywany jawnie | Zapisana wartość nie jest równa plain text PIN-owi. |
| Ponowny setup PIN-u jest zablokowany | Jeśli user ma już PIN, `setupPin()` rzuca wyjątek i nie flushuje zmian. |
| Poprawny PIN przechodzi | `verifyPin()` zwraca `true` dla poprawnego PIN-u. |
| Błędny PIN nie przechodzi | `verifyPin()` zwraca `false` dla błędnego PIN-u. |
| Trzy błędne próby blokują użytkownika | Po 3 błędnych próbach ustawiane jest `pinLockedUntil`. |
| Zablokowany użytkownik nie może weryfikować PIN-u | Jeśli `pinLockedUntil` jest w przyszłości, serwis rzuca `AccessDeniedHttpException`. |
| Poprawny PIN czyści licznik błędnych prób | Po poprawnym PIN-ie użytkownik nie jest blokowany na podstawie poprzednich błędów. |
| Zmiana PIN-u z poprawnym starym PIN-em | `changePin()` zapisuje nowy hash PIN-u. |
| Zmiana PIN-u z błędnym starym PIN-em | `changePin()` rzuca `AccessDeniedHttpException` i nie flushuje zmian. |

### Dlaczego to jest ważne

To jest rdzeń logiki PIN-u. Functional testy potwierdzają całe flow HTTP, ale unit testy szybciej wskazują, czy problem jest w samej domenowej logice PIN-u.

---

## 4. `SessionServiceTest`

### Testowana klasa

```txt
App\Session\Service\SessionService
```

### Odpowiedzialność klasy

`SessionService` zarządza sesjami aplikacyjnymi niezależnie od warstwy HTTP:

```txt
tworzenie sesji
hashowanie tokenów
lookup sesji po access/partial tokenie
lookup sesji po refresh tokenie
rotacja tokenów
revokowanie sesji
wygaszanie sesji
czyszczenie starych sesji
```

### Use case'y

| Use case | Co sprawdza test |
|---|---|
| Tworzenie sesji | `createSession()` tworzy sesję dla użytkownika z odpowiednim statusem, IP i User-Agent. |
| Persist i flush przy tworzeniu | Nowa sesja jest przekazywana do `EntityManager::persist()` i `flush()`. |
| Przypisanie access tokena | `assignTokenToSession()` zapisuje hash tokena, nie jawny token. |
| Lookup po access tokenie — brak sesji | Dla nieznanego tokena metoda zwraca `null`. |
| Lookup po access tokenie — aktywna sesja | Dla poprawnego tokena metoda zwraca aktywną sesję. |
| Lookup po access tokenie — sesja wygasła | Wygasła sesja jest oznaczana jako `EXPIRED` i metoda zwraca `null`. |
| Lookup po access tokenie — sesja revoked | Revoked session nie jest zwracana jako aktywna. |
| Oznaczenie jako authenticated | `markSessionAsAuthenticated()` zmienia status na `AUTHENTICATED`, zapisuje hash tokena i ustawia `authenticatedAt`. |
| Revokowanie sesji | `revokeSession()` ustawia status `REVOKED` i `revokedAt`. |
| Delete session — brak sesji | `deleteSession()` nic nie usuwa, jeśli sesja nie istnieje. |
| Delete session — istniejąca sesja | `deleteSession()` usuwa sesję i flushuje zmiany. |
| Przypisanie refresh tokena | `assignRefreshTokenToSession()` zapisuje hash refresh tokena i expiry. |
| Lookup po refresh tokenie — brak sesji | Dla nieznanego refresh tokena metoda zwraca `null`. |
| Lookup po refresh tokenie — authenticated session | Poprawny refresh token dla authenticated session zwraca sesję. |
| Lookup po refresh tokenie — revoked session | Revoked session nie może zostać odświeżona. |
| Lookup po refresh tokenie — expired refresh | Sesja z wygasłym refresh tokenem jest revokowana i metoda zwraca `null`. |
| Lookup po refresh tokenie — partial session | Sesja niebędąca `AUTHENTICATED` nie może używać refresh tokena. |
| Rotacja tokenów | `rotateTokens()` zmienia hash access tokena, hash refresh tokena i expiry refresh tokena. |
| Cleanup expired sessions | `cleanupExpiredSessions()` deleguje usuwanie do repozytorium. |

### Dlaczego to jest ważne

Refresh i logout zależą od poprawnego stanu sesji. Te testy zabezpieczają przypadki, w których stary refresh token mógłby dalej działać, revoked session mogłaby zostać odświeżona albo token mógłby zostać zapisany jawnie zamiast jako hash.

---

## 5. `AuthTokenServiceTest`

### Testowana klasa

```txt
App\Auth\Service\AuthTokenService
```

### Odpowiedzialność klasy

`AuthTokenService` koordynuje tworzenie tokenów JWT, sesji oraz request attributes używanych później przez cookie subscriber:

```txt
_partial_auth_token
_auth_token
_refresh_token
_expire_partial
```

Serwis nie ustawia cookies bezpośrednio. On przygotowuje dane, które później warstwa event subscriberów zamienia na `Set-Cookie`.

### Use case'y

| Use case | Co sprawdza test |
|---|---|
| Tworzenie partial tokena | `createPartialToken()` tworzy sesję, generuje JWT i przypisuje token do sesji. |
| Partial token ustawia request attribute | Po `createPartialToken()` request ma `_partial_auth_token`. |
| Partial token nie ustawia pełnych tokenów | Przy partial auth nie ma `_auth_token`, `_refresh_token` ani `_expire_partial`. |
| Payload partial tokena dla usera bez PIN-u | JWT payload zawiera status `pin_setup_required`, `has_pin=false`, `session_id` i `jti`. |
| Payload partial tokena dla usera z PIN-em | JWT payload zawiera status `pin_verification_required` i `has_pin=true`. |
| Tworzenie authenticated tokena | `createAuthenticatedToken()` generuje access token i oznacza sesję jako authenticated. |
| Authenticated token tworzy refresh token | Serwis generuje refresh token i przekazuje go do session managera. |
| Authenticated token ustawia request attributes | Request dostaje `_auth_token`, `_refresh_token` i `_expire_partial=true`. |
| Authenticated token nie ustawia partial tokena | Po pełnej autoryzacji nie ma `_partial_auth_token`. |
| Refresh authenticated token | `refreshAuthenticatedToken()` generuje nowy access token i nowy refresh token. |
| Refresh rotuje tokeny w sesji | Serwis wywołuje `rotateTokens()` na session managerze. |
| Refresh ustawia request attributes | Request dostaje nowe `_auth_token` i `_refresh_token`. |
| Refresh nie wygasza partial tokena | Przy refreshu nie ma `_expire_partial`, bo partial token powinien już nie istnieć. |
| Refresh tokeny są losowe | Kolejne wygenerowane refresh tokeny są różne. |

### Dlaczego to jest ważne

Ten serwis jest klejem między JWT, sesją i cookies. Unit testy sprawdzają, czy właściwe dane trafiają do session managera i request attributes, bez uruchamiania całego kernela HTTP.

---

## Podsumowanie pokrycia unit testów

| Paczka | Główna odpowiedzialność | Typ regresji, który łapie |
|---|---|---|
| `CookieFactoryTest` | Parametry cookies | Błędne HttpOnly/Secure/SameSite/expiry. |
| `CookieTokenExtractorTest` | Pobieranie JWT z cookies | Brak obsługi `partial_access_token` albo złe preferowanie tokenów. |
| `PinServiceTest` | Logika PIN-u | Brak hashowania, brak lockout, błędna zmiana PIN-u. |
| `SessionServiceTest` | Lifecycle sesji | Stare tokeny, revoked sessions, expired sessions, rotacja tokenów. |
| `AuthTokenServiceTest` | Koordynacja JWT + sesja + request attributes | Brak ustawienia `_auth_token`, `_refresh_token`, `_partial_auth_token` albo błędny payload JWT. |

## Co ewentualnie można dodać później

Aktualne unity są wystarczające dla głównego auth lifecycle. Dodatkowo można jeszcze dorzucić:

```txt
AuthCookieSubscriberTest
SessionAuthorizationSubscriberTest
```

### `AuthCookieSubscriberTest`

Potencjalne use case'y:

```txt
- `_partial_auth_token` ustawia `partial_access_token`
- `_auth_token` ustawia `access_token`
- `_refresh_token` ustawia `refresh_token`
- `_expire_partial` wygasza `partial_access_token`
- `_logout` wygasza `access_token`, `refresh_token` i `partial_access_token`
```

### `SessionAuthorizationSubscriberTest`

Potencjalne use case'y:

```txt
- publiczne endpointy przechodzą bez sesji
- endpointy PIN przechodzą z partial session
- zwykłe endpointy są blokowane dla partial session
- authenticated session przechodzi dla zwykłych endpointów
- brak sesji daje 401
- subscriber ustawia `app_session` w request attributes
```

Te dwa testy są dobrym kolejnym krokiem, ale nie są krytyczne, bo główne scenariusze są już pokryte functional testami.
