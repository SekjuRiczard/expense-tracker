# Budget functional tests — use cases

Dokument opisuje paczki testów funkcjonalnych dla modułu Budget, który odpowiada za zarządzanie budżetami użytkownika w aplikacji Expense Tracker.

Moduł pozwala tworzyć, listować, pobierać, aktualizować oraz usuwać budżety. Budżet może dotyczyć pełnego miesiąca, pełnego roku albo dowolnego niestandardowego zakresu dat. Testy sprawdzają pełne zachowanie endpointów HTTP, walidację danych wejściowych, ownership zasobów oraz ochronę przed duplikatami.

## Cel testów

Testy funkcjonalne obejmują:

- statusy HTTP,
- format odpowiedzi JSON,
- zapis budżetu w bazie,
- przypisanie budżetu do aktualnie zalogowanego użytkownika,
- walidację nazwy i kwoty,
- obsługę typów okresów `monthly`, `yearly` i `custom`,
- walidację zakresów dat,
- ochronę endpointów przed niezalogowanym użytkownikiem,
- brak dostępu do budżetów innych użytkowników,
- sortowanie listy budżetów,
- ochronę przed utworzeniem duplikatu,
- ochronę przed konfliktem podczas aktualizacji,
- poprawne usuwanie budżetu,
- brak przypadkowego usuwania innych rekordów,
- obsługę niepoprawnego JSON-a.

---

# CreateBudgetTest

Paczka sprawdza endpoint:

```http
POST /api/budgets
```

## Use case: zalogowany użytkownik może utworzyć budżet miesięczny

Sprawdza, że poprawny request:

- zwraca status `201 Created`,
- zwraca JSON z danymi utworzonego budżetu,
- zwraca identyfikator budżetu jako integer,
- zapisuje nazwę budżetu,
- zapisuje kwotę jako integer w najmniejszej jednostce waluty,
- zapisuje walutę,
- zapisuje typ okresu `monthly`,
- zapisuje datę początkową,
- zapisuje datę końcową,
- zwraca pola `createdAt` i `updatedAt`,
- zapisuje encję w bazie,
- przypisuje budżet do aktualnie zalogowanego użytkownika.

Przykładowy request:

```json
{
  "name": "Budżet domowy — czerwiec",
  "amount": 400000,
  "currency": "PLN",
  "periodType": "monthly",
  "startDate": "2026-06-01",
  "endDate": "2026-06-30"
}
```

## Use case: zalogowany użytkownik może utworzyć budżet roczny

Sprawdza, że poprawny budżet roczny:

- zwraca status `201 Created`,
- zapisuje typ okresu `yearly`,
- obejmuje dokładnie pełny rok kalendarzowy,
- zapisuje poprawną datę początkową i końcową.

Przykładowy zakres:

```text
2026-01-01 → 2026-12-31
```

## Use case: zalogowany użytkownik może utworzyć budżet niestandardowy

Sprawdza, że budżet typu `custom`:

- zwraca status `201 Created`,
- pozwala ustawić dowolny poprawny zakres dat,
- może używać innej waluty niż PLN.

Przykładowy zakres:

```text
2026-07-10 → 2026-08-18
```

## Use case: użytkownik może utworzyć budżety dla różnych walut

Sprawdza, że ten sam użytkownik może posiadać budżety dla tego samego okresu, jeżeli różnią się walutą.

Przykład:

```text
PLN, monthly, 2026-06-01 → 2026-06-30
EUR, monthly, 2026-06-01 → 2026-06-30
```

## Use case: nie można utworzyć duplikatu budżetu

Sprawdza, że próba utworzenia drugiego budżetu dla tego samego zestawu:

```text
user
currency
periodType
startDate
endDate
```

- zwraca status `409 Conflict`,
- nie zapisuje duplikatu w bazie.

## Use case: gość nie może utworzyć budżetu

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`,
- nie zapisuje budżetu w bazie.

## Use case: pusta nazwa jest odrzucana

Sprawdza, że request z pustą nazwą:

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: zbyt długa nazwa jest odrzucana

Sprawdza, że nazwa dłuższa niż 100 znaków:

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: kwota równa zero jest odrzucana

Sprawdza, że `amount = 0`:

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: ujemna kwota jest odrzucana

Sprawdza, że ujemne `amount`:

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: niepoprawna waluta jest odrzucana

Sprawdza, że waluta spoza obsługiwanego enuma:

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: niepoprawny typ okresu jest odrzucany

Sprawdza, że wartość spoza:

```text
monthly
yearly
custom
```

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: brak wymaganych pól jest odrzucany

Sprawdza osobno brak:

```text
name
amount
currency
periodType
startDate
endDate
```

Każdy taki request:

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: niepoprawny zakres dat jest odrzucany

Sprawdza, że dla budżetu typu `custom`:

```text
startDate > endDate
```

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: budżet miesięczny musi obejmować pełny miesiąc

Sprawdza, że budżet `monthly` z częściowym zakresem, np.:

```text
2026-06-05 → 2026-06-30
```

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: budżet roczny musi obejmować pełny rok

Sprawdza, że budżet `yearly` z częściowym zakresem, np.:

```text
2026-01-01 → 2026-11-30
```

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje budżetu.

## Use case: niepoprawny JSON jest odrzucany

Sprawdza, że uszkodzony JSON:

- zwraca status `400 Bad Request`,
- nie zapisuje budżetu.

---

# ListBudgetsTest

Paczka sprawdza endpoint:

```http
GET /api/budgets
```

## Use case: użytkownik widzi tylko własne budżety

Sprawdza, że lista:

- zwraca status `200 OK`,
- zawiera wyłącznie budżety aktualnie zalogowanego użytkownika,
- nie zawiera budżetów należących do innych użytkowników.

## Use case: użytkownik bez budżetów otrzymuje pustą listę

Sprawdza, że brak danych:

- zwraca status `200 OK`,
- zwraca pustą tablicę JSON.

## Use case: budżety są sortowane malejąco po dacie początkowej

Sprawdza sortowanie:

```text
startDate DESC
createdAt DESC
```

Budżety rozpoczynające się najpóźniej powinny pojawić się na początku listy.

## Use case: gość nie może listować budżetów

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`.

---

# GetBudgetTest

Paczka sprawdza endpoint:

```http
GET /api/budgets/{id}
```

## Use case: zalogowany użytkownik może pobrać własny budżet

Sprawdza, że endpoint:

- zwraca status `200 OK`,
- zwraca poprawne `id`,
- zwraca nazwę,
- zwraca kwotę,
- zwraca walutę,
- zwraca typ okresu,
- zwraca `startDate`,
- zwraca `endDate`,
- zwraca `createdAt`,
- zwraca `updatedAt`.

## Use case: użytkownik nie może pobrać budżetu innego użytkownika

Sprawdza, że próba pobrania cudzego rekordu:

- zwraca status `404 Not Found`,
- nie ujawnia istnienia cudzych danych.

## Use case: brakujący budżet zwraca not found

Sprawdza, że nieistniejący identyfikator:

- zwraca status `404 Not Found`.

## Use case: niepoprawny identyfikator zwraca not found

Sprawdza, że identyfikator niezgodny z wymaganiami routingu, np.:

```http
GET /api/budgets/abc
```

- zwraca status `404 Not Found`.

## Use case: gość nie może pobrać budżetu

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`.

---

# UpdateBudgetTest

Paczka sprawdza endpoint:

```http
PATCH /api/budgets/{id}
```

## Use case: zalogowany użytkownik może zmienić nazwę budżetu

Sprawdza, że aktualizacja samej nazwy:

- zwraca status `200 OK`,
- zapisuje nową nazwę,
- nie zmienia kwoty,
- nie zmienia waluty,
- nie zmienia typu okresu.

## Use case: zalogowany użytkownik może zmienić tylko kwotę

Sprawdza, że PATCH:

```json
{
  "amount": 500000
}
```

- zwraca status `200 OK`,
- aktualizuje kwotę,
- zachowuje pozostałe pola.

## Use case: użytkownik może zmienić budżet na niestandardowy

Sprawdza zmianę:

```text
monthly → custom
```

razem z ustawieniem nowego zakresu dat.

Endpoint powinien:

- zwrócić status `200 OK`,
- zapisać typ `custom`,
- zapisać nową datę początkową,
- zapisać nową datę końcową.

## Use case: użytkownik może zmienić walutę budżetu

Sprawdza, że aktualizacja waluty:

- zwraca status `200 OK`,
- zapisuje nową walutę,
- zachowuje pozostałe pola.

## Use case: pusty PATCH jest odrzucany

Sprawdza, że pusty JSON:

```json
{}
```

- zwraca status `400 Bad Request`,
- nie zmienia budżetu.

## Use case: pusta nazwa jest odrzucana

Sprawdza, że request:

```json
{
  "name": ""
}
```

- zwraca status `422 Unprocessable Entity`,
- nie zmienia zapisanej nazwy.

## Use case: nie można zmienić budżetu miesięcznego na częściowy miesiąc

Sprawdza, że aktualizacja daty początkowej budżetu `monthly` do wartości niezgodnej z początkiem miesiąca:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia zakresu.

## Use case: niepoprawny zakres dat jest odrzucany

Sprawdza, że dla budżetu typu `custom`:

```text
startDate > endDate
```

- zwraca status `422 Unprocessable Entity`,
- nie zapisuje zmian.

## Use case: nie można zaktualizować budżetu do istniejącego już okresu

Sprawdza, że próba zmiany zakresu budżetu tak, aby kolidował z innym rekordem tego samego użytkownika:

- zwraca status `409 Conflict`,
- nie zapisuje konfliktującej aktualizacji.

## Use case: użytkownik nie może edytować cudzego budżetu

Sprawdza, że próba aktualizacji rekordu innego użytkownika:

- zwraca status `404 Not Found`,
- nie zmienia cudzych danych.

## Use case: aktualizacja brakującego budżetu zwraca not found

Sprawdza, że nieistniejący identyfikator:

- zwraca status `404 Not Found`.

## Use case: gość nie może aktualizować budżetu

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`,
- nie zmienia danych.

## Use case: niepoprawny JSON jest odrzucany

Sprawdza, że uszkodzony JSON:

- zwraca status `400 Bad Request`,
- nie aktualizuje budżetu.

---

# DeleteBudgetTest

Paczka sprawdza endpoint:

```http
DELETE /api/budgets/{id}
```

## Use case: zalogowany użytkownik może usunąć własny budżet

Sprawdza, że usunięcie:

- zwraca status `204 No Content`,
- zwraca puste body,
- usuwa rekord z bazy.

## Use case: użytkownik nie może usunąć cudzego budżetu

Sprawdza, że próba usunięcia rekordu innego użytkownika:

- zwraca status `404 Not Found`,
- nie usuwa budżetu.

## Use case: brakujący budżet zwraca not found

Sprawdza, że próba usunięcia nieistniejącego rekordu:

- zwraca status `404 Not Found`.

## Use case: gość nie może usunąć budżetu

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`,
- nie usuwa rekordu.

## Use case: usunięcie jednego budżetu nie usuwa innych budżetów

Sprawdza, że usunięcie wybranego rekordu:

- usuwa wyłącznie wskazany budżet,
- pozostawia pozostałe budżety użytkownika bez zmian.

---

# Podsumowanie pokrycia

Testy funkcjonalne modułu Budget obejmują pełny przepływ:

```text
create
→ list
→ get
→ update
→ delete
```

oraz najważniejsze reguły biznesowe:

```text
ownership
→ monthly jako pełny miesiąc
→ yearly jako pełny rok
→ custom jako dowolny poprawny zakres
→ startDate <= endDate
→ amount > 0
→ ochrona przed duplikatami
→ 409 Conflict dla konfliktów unikalności
→ walidacja requestów
→ malformed JSON
→ ochrona endpointów przed guestem
```
