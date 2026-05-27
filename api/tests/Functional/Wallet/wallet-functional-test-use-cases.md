# Wallet functional tests — use cases

Dokument opisuje paczki testów funkcjonalnych dla modułu Wallet, który odpowiada za zarządzanie portfelami użytkownika w aplikacji Expense Tracker.

Testy obejmują endpointy tworzenia, listowania, pobierania szczegółów, aktualizacji oraz usuwania portfeli. Moduł działa w kontekście aktualnie uwierzytelnionego użytkownika, dlatego szczególnie ważne są testy ownership, czyli sprawdzanie, czy użytkownik ma dostęp wyłącznie do własnych portfeli.

## Cel testów

Testy funkcjonalne sprawdzają pełne zachowanie endpointów HTTP, a nie pojedyncze klasy. W szczególności obejmują:

- statusy HTTP,
- format odpowiedzi JSON,
- zapis portfela w bazie,
- przypisanie portfela do aktualnie zalogowanego użytkownika,
- walidację requestów,
- ochronę endpointów przed niezalogowanym użytkownikiem,
- brak dostępu do portfeli innych użytkowników,
- brak możliwości zmiany salda i waluty przez endpoint aktualizacji,
- poprawne usuwanie portfela,
- brak przypadkowego usuwania innych portfeli.

---

# CreateWalletTest

Paczka sprawdza endpoint:

```http
POST /api/wallets
```

## Use case: zalogowany użytkownik może utworzyć portfel

Sprawdza, że poprawny request tworzenia portfela:

- zwraca status `201 Created`,
- zwraca JSON z danymi utworzonego portfela,
- zapisuje portfel w bazie,
- przypisuje portfel do aktualnie zalogowanego użytkownika,
- zapisuje poprawną nazwę portfela,
- zapisuje poprawny typ portfela,
- zapisuje poprawną walutę,
- zapisuje saldo jako integer w najmniejszej jednostce waluty,
- zwraca pola `createdAt` i `updatedAt`.

## Use case: zalogowany użytkownik może utworzyć portfel z zerowym saldem

Sprawdza, że request z `balanceAmount` równym `0`:

- zwraca status `201 Created`,
- tworzy portfel w bazie,
- zwraca `balanceAmount` równe `0`,
- zapisuje `balanceAmount` równe `0` w encji.

## Use case: zalogowany użytkownik może utworzyć portfel bez pola `balanceAmount`

Sprawdza, że request bez pola `balanceAmount`:

- zwraca status `201 Created`,
- tworzy portfel w bazie,
- ustawia domyślne saldo na `0`,
- zwraca `balanceAmount` równe `0`.

## Use case: niezalogowany użytkownik nie może utworzyć portfela

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela z pustą nazwą

Sprawdza, że request z pustym polem `name`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela ze zbyt długą nazwą

Sprawdza, że request z nazwą dłuższą niż dozwolony limit:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela z ujemnym saldem

Sprawdza, że request z `balanceAmount` mniejszym niż `0`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela bez pola `name`

Sprawdza, że request bez wymaganego pola `name`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela bez pola `type`

Sprawdza, że request bez wymaganego pola `type`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela bez pola `currency`

Sprawdza, że request bez wymaganego pola `currency`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela z niepoprawnym typem

Sprawdza, że request z typem spoza enumu `WalletType`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela z niepoprawną walutą

Sprawdza, że request z walutą spoza enumu `CurrencyCode`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy portfela w bazie.

## Use case: tworzenie portfela z uszkodzonym JSON-em

Sprawdza, że request z niepoprawnym składniowo JSON-em:

- zwraca status `400 Bad Request`,
- nie tworzy portfela w bazie.

---

# ListWalletsTest

Paczka sprawdza endpoint:

```http
GET /api/wallets
```

## Use case: zalogowany użytkownik może pobrać listę własnych portfeli

Sprawdza, że zalogowany użytkownik:

- otrzymuje status `200 OK`,
- otrzymuje listę swoich portfeli,
- otrzymuje portfele w poprawnym formacie JSON,
- widzi pola `id`, `name`, `type`, `currency`, `balanceAmount`, `createdAt`, `updatedAt`.

## Use case: użytkownik widzi tylko własne portfele

Sprawdza, że jeżeli w bazie istnieją portfele różnych użytkowników:

- aktualny użytkownik dostaje status `200 OK`,
- odpowiedź zawiera wyłącznie portfele aktualnie zalogowanego użytkownika,
- odpowiedź nie zawiera portfeli innych użytkowników.

## Use case: użytkownik bez portfeli dostaje pustą listę

Sprawdza, że zalogowany użytkownik, który nie ma żadnych portfeli:

- otrzymuje status `200 OK`,
- otrzymuje pustą tablicę JSON.

## Use case: niezalogowany użytkownik nie może pobrać listy portfeli

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie zwraca listy portfeli.

---

# GetWalletTest

Paczka sprawdza endpoint:

```http
GET /api/wallets/{id}
```

## Use case: zalogowany użytkownik może pobrać swój portfel

Sprawdza, że właściciel portfela:

- otrzymuje status `200 OK`,
- otrzymuje dane konkretnego portfela,
- dostaje poprawne `id`,
- dostaje poprawną nazwę,
- dostaje poprawny typ,
- dostaje poprawną walutę,
- dostaje poprawne saldo,
- dostaje pola `createdAt` i `updatedAt`.

## Use case: użytkownik nie może pobrać portfela innego użytkownika

Sprawdza, że użytkownik próbujący pobrać cudzy portfel:

- otrzymuje status `404 Not Found`,
- nie dostaje danych cudzego portfela.

Portfel innego użytkownika jest traktowany tak, jakby nie istniał dla aktualnego użytkownika.

## Use case: pobranie nieistniejącego portfela

Sprawdza, że request dla nieistniejącego `id`:

- zwraca status `404 Not Found`.

## Use case: pobranie portfela z niepoprawnym formatem ID

Sprawdza, że request z niepoprawnym formatem identyfikatora, np. `/api/wallets/abc`:

- zwraca status `404 Not Found`,
- nie wykonuje logiki pobierania portfela.

## Use case: niezalogowany użytkownik nie może pobrać portfela

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie zwraca danych portfela.

---

# UpdateWalletTest

Paczka sprawdza endpoint:

```http
PATCH /api/wallets/{id}
```

## Use case: zalogowany użytkownik może zmienić nazwę portfela

Sprawdza, że właściciel portfela może zmienić pole `name`:

- zwraca status `200 OK`,
- zwraca portfel z nową nazwą,
- zapisuje nową nazwę w bazie,
- nie zmienia typu portfela,
- nie zmienia waluty,
- nie zmienia salda.

## Use case: zalogowany użytkownik może zmienić typ portfela

Sprawdza, że właściciel portfela może zmienić pole `type`:

- zwraca status `200 OK`,
- zwraca portfel z nowym typem,
- zapisuje nowy typ w bazie,
- nie zmienia nazwy,
- nie zmienia waluty,
- nie zmienia salda.

## Use case: zalogowany użytkownik może zmienić nazwę i typ jednocześnie

Sprawdza, że request zawierający `name` i `type`:

- zwraca status `200 OK`,
- aktualizuje nazwę portfela,
- aktualizuje typ portfela,
- nie zmienia waluty,
- nie zmienia salda.

## Use case: aktualizacja pustym payloadem

Sprawdza, że request bez żadnych dozwolonych pól do aktualizacji:

- zwraca status `400 Bad Request`,
- nie zmienia portfela w bazie.

## Use case: aktualizacja z pustą nazwą

Sprawdza, że request z pustym polem `name`:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia portfela w bazie.

## Use case: aktualizacja ze zbyt długą nazwą

Sprawdza, że request z nazwą dłuższą niż dozwolony limit:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia portfela w bazie.

## Use case: aktualizacja z niepoprawnym typem

Sprawdza, że request z typem spoza enumu `WalletType`:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia portfela w bazie.

## Use case: próba zmiany waluty portfela

Sprawdza, że request zawierający pole `currency`:

- zwraca błąd zgodny z aplikacją,
- nie zmienia waluty portfela w bazie.

Waluta portfela nie jest częścią dozwolonego update'u, ponieważ po dodaniu transakcji zmiana waluty mogłaby naruszyć spójność danych finansowych.

## Use case: próba zmiany salda portfela

Sprawdza, że request zawierający pole `balanceAmount`:

- zwraca błąd zgodny z aplikacją,
- nie zmienia salda portfela w bazie.

Saldo nie jest edytowane przez endpoint portfela. Docelowo powinno być zmieniane przez moduł transakcji.

## Use case: użytkownik nie może aktualizować portfela innego użytkownika

Sprawdza, że próba aktualizacji cudzego portfela:

- zwraca status `404 Not Found`,
- nie zmienia danych cudzego portfela.

## Use case: aktualizacja nieistniejącego portfela

Sprawdza, że request dla nieistniejącego `id`:

- zwraca status `404 Not Found`.

## Use case: niezalogowany użytkownik nie może aktualizować portfela

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie zmienia portfela w bazie.

## Use case: aktualizacja z uszkodzonym JSON-em

Sprawdza, że request z niepoprawnym składniowo JSON-em:

- zwraca status `400 Bad Request`,
- nie zmienia portfela w bazie.

---

# DeleteWalletTest

Paczka sprawdza endpoint:

```http
DELETE /api/wallets/{id}
```

## Use case: zalogowany użytkownik może usunąć swój portfel

Sprawdza, że właściciel portfela:

- otrzymuje status `204 No Content`,
- otrzymuje pustą odpowiedź,
- usuwa portfel z bazy.

## Use case: użytkownik nie może usunąć portfela innego użytkownika

Sprawdza, że próba usunięcia cudzego portfela:

- zwraca status `404 Not Found`,
- nie usuwa portfela z bazy.

Portfel innego użytkownika jest traktowany tak, jakby nie istniał dla aktualnego użytkownika.

## Use case: usunięcie nieistniejącego portfela

Sprawdza, że request dla nieistniejącego `id`:

- zwraca status `404 Not Found`.

## Use case: niezalogowany użytkownik nie może usunąć portfela

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie usuwa portfela z bazy.

## Use case: usunięcie jednego portfela nie usuwa innych portfeli użytkownika

Sprawdza, że jeżeli użytkownik ma więcej niż jeden portfel:

- usunięcie jednego portfela zwraca status `204 No Content`,
- wskazany portfel zostaje usunięty,
- pozostałe portfele użytkownika nadal istnieją w bazie.

---

# Zakres aktualnie potwierdzony testami

Aktualny zestaw testów potwierdza, że moduł Wallet obsługuje:

- tworzenie portfela,
- walidację requestu tworzenia,
- pobieranie listy portfeli użytkownika,
- pobieranie pojedynczego portfela,
- aktualizację nazwy i typu portfela,
- blokadę aktualizacji waluty i salda,
- usuwanie portfela,
- izolację danych pomiędzy użytkownikami,
- ochronę endpointów przed niezalogowanym użytkownikiem.