# Transaction functional tests — use cases

Dokument opisuje paczki testów funkcjonalnych dla modułu Transaction, który odpowiada za obsługę przychodów i wydatków użytkownika w aplikacji Expense Tracker.

Testy obejmują endpointy tworzenia, listowania, pobierania szczegółów, aktualizacji oraz usuwania transakcji. Moduł jest ściśle powiązany z portfelami i kategoriami, dlatego testy sprawdzają nie tylko odpowiedzi HTTP, ale także poprawne przeliczanie salda, ownership zasobów oraz zgodność typu transakcji z typem kategorii.

## Cel testów

Testy funkcjonalne sprawdzają pełne zachowanie endpointów HTTP, a nie pojedyncze klasy. W szczególności obejmują:

- statusy HTTP,
- format odpowiedzi JSON,
- zapis transakcji w bazie,
- przypisanie transakcji do aktualnie zalogowanego użytkownika,
- walidację requestów,
- ochronę endpointów przed niezalogowanym użytkownikiem,
- brak dostępu do transakcji, portfeli i kategorii innych użytkowników,
- zgodność typu transakcji z typem kategorii,
- automatyczne zwiększanie i zmniejszanie salda portfela,
- cofanie starego wpływu transakcji przed zastosowaniem nowego,
- poprawne przenoszenie wpływu transakcji pomiędzy portfelami,
- paginację,
- filtrowanie,
- sortowanie,
- obsługę niepoprawnego JSON-a.

---

# CreateTransactionTest

Paczka sprawdza endpoint:

```http
POST /api/transactions
```

## Use case: zalogowany użytkownik może utworzyć wydatek

Sprawdza, że poprawny request tworzenia wydatku:

- zwraca status `201 Created`,
- zwraca JSON z danymi utworzonej transakcji,
- zwraca identyfikator transakcji jako integer,
- zwraca poprawne `walletId`,
- zwraca poprawne `categoryId`,
- zwraca typ `expense`,
- zwraca kwotę jako integer w najmniejszej jednostce waluty,
- zwraca walutę portfela,
- zwraca tytuł i opis,
- zapisuje transakcję w bazie,
- przypisuje transakcję do aktualnie zalogowanego użytkownika,
- zapisuje poprawny typ i kwotę,
- zmniejsza saldo portfela o wartość wydatku.

## Use case: zalogowany użytkownik może utworzyć przychód

Sprawdza, że poprawny request tworzenia przychodu:

- zwraca status `201 Created`,
- zapisuje transakcję typu `income`,
- zwiększa saldo portfela o wartość przychodu.

## Use case: zalogowany użytkownik może użyć kategorii domyślnej

Sprawdza, że transakcja może zostać utworzona z kategorią systemową, która nie jest przypisana do konkretnego użytkownika.

## Use case: gość nie może utworzyć transakcji

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`,
- nie może utworzyć transakcji.

## Use case: użytkownik nie może użyć portfela innego użytkownika

Sprawdza, że request wskazujący cudzy portfel:

- zwraca status `404 Not Found`,
- nie tworzy transakcji,
- nie zmienia salda cudzego portfela.

## Use case: użytkownik nie może użyć kategorii innego użytkownika

Sprawdza, że request wskazujący cudzą kategorię:

- zwraca status `404 Not Found`,
- nie tworzy transakcji.

## Use case: typ transakcji musi pasować do typu kategorii

Sprawdza, że próba utworzenia przychodu z kategorią wydatkową:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia salda portfela.

## Use case: kwota transakcji nie może wynosić zero

Sprawdza, że request z `amount = 0`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy transakcji.

## Use case: kwota transakcji nie może być ujemna

Sprawdza, że request z ujemną kwotą:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy transakcji.

## Use case: niepoprawny JSON jest odrzucany

Sprawdza, że uszkodzony JSON:

- zwraca status `400 Bad Request`,
- nie tworzy transakcji.

---

# GetTransactionTest

Paczka sprawdza endpoint:

```http
GET /api/transactions/{id}
```

## Use case: zalogowany użytkownik może pobrać własną transakcję

Sprawdza, że endpoint:

- zwraca status `200 OK`,
- zwraca poprawne `id`,
- zwraca tytuł,
- zwraca typ,
- zwraca kwotę,
- zwraca `walletId`,
- zwraca `categoryId`.

## Use case: użytkownik nie może pobrać transakcji innego użytkownika

Sprawdza, że próba pobrania cudzego rekordu:

- zwraca status `404 Not Found`,
- nie ujawnia istnienia cudzej transakcji.

## Use case: brakująca transakcja zwraca not found

Sprawdza, że nieistniejący identyfikator:

- zwraca status `404 Not Found`.

## Use case: niepoprawny identyfikator zwraca not found

Sprawdza, że identyfikator niezgodny z wymaganiami routingu:

- zwraca status `404 Not Found`.

## Use case: gość nie może pobrać transakcji

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`.

---

# ListTransactionsTest

Paczka sprawdza endpoint:

```http
GET /api/transactions
```

Endpoint obsługuje paginację i opcjonalne filtry:

```http
GET /api/transactions?page=1&limit=20
GET /api/transactions?type=expense
GET /api/transactions?walletId=1
GET /api/transactions?categoryId=2
GET /api/transactions?from=2024-01-01&to=2024-12-31
```

## Use case: użytkownik widzi tylko własne transakcje

Sprawdza, że lista:

- zwraca status `200 OK`,
- zawiera wyłącznie transakcje aktualnie zalogowanego użytkownika,
- nie zawiera transakcji należących do innych użytkowników.

## Use case: użytkownik bez transakcji otrzymuje pustą listę

Sprawdza, że brak danych:

- zwraca status `200 OK`,
- zwraca pustą kolekcję `items`,
- zwraca poprawne metadane paginacji.

## Use case: transakcje są poprawnie sortowane

Sprawdza sortowanie:

1. `transactionDate DESC`,
2. `createdAt DESC`.

Najnowsze transakcje powinny pojawiać się na początku listy.

## Use case: użytkownik może stronicować transakcje

Sprawdza, że parametry:

```text
page
limit
```

- ograniczają liczbę elementów na stronie,
- przesuwają offset wyników,
- zwracają poprawne `totalItems`,
- zwracają poprawne `totalPages`.

## Use case: użytkownik może filtrować po typie transakcji

Sprawdza filtr:

```http
GET /api/transactions?type=expense
```

Lista powinna zawierać wyłącznie transakcje wybranego typu.

## Use case: niepoprawny typ filtra jest odrzucany

Sprawdza, że wartość spoza enuma, np.:

```http
GET /api/transactions?type=invalid
```

zwraca status `422 Unprocessable Entity`.

## Use case: użytkownik może filtrować po portfelu

Sprawdza filtr:

```http
GET /api/transactions?walletId={id}
```

Lista powinna zawierać wyłącznie transakcje przypisane do wybranego portfela.

## Use case: niepoprawny identyfikator portfela jest odrzucany

Sprawdza, że `walletId = 0`:

- zwraca status `422 Unprocessable Entity`.

## Use case: użytkownik może filtrować po kategorii

Sprawdza filtr:

```http
GET /api/transactions?categoryId={id}
```

Lista powinna zawierać wyłącznie transakcje przypisane do wybranej kategorii.

## Use case: niepoprawny identyfikator kategorii jest odrzucany

Sprawdza, że `categoryId = 0`:

- zwraca status `422 Unprocessable Entity`.

## Use case: użytkownik może filtrować po zakresie dat

Sprawdza filtry:

```http
GET /api/transactions?from=2024-02-01&to=2024-02-29T23:59:59
```

Lista powinna zawierać wyłącznie transakcje z wybranego zakresu.

## Use case: niepoprawny zakres dat jest odrzucany

Sprawdza, że zakres, w którym `from > to`:

- zwraca status `422 Unprocessable Entity`.

## Use case: użytkownik może łączyć filtry

Sprawdza jednoczesne użycie kilku filtrów, np.:

```http
GET /api/transactions?type=expense&walletId=1&categoryId=2&from=2024-02-01&to=2024-02-29T23:59:59
```

Lista powinna zawierać wyłącznie rekordy spełniające wszystkie warunki.

## Use case: niepoprawny numer strony jest odrzucany

Sprawdza, że `page = 0`:

- zwraca status `422 Unprocessable Entity`.

## Use case: zbyt wysoki limit jest odrzucany

Sprawdza, że `limit > 100`:

- zwraca status `422 Unprocessable Entity`.

## Use case: gość nie może listować transakcji

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`.

---

# UpdateTransactionTest

Paczka sprawdza endpoint:

```http
PATCH /api/transactions/{id}
```

## Use case: zalogowany użytkownik może zmienić tytuł transakcji

Sprawdza, że aktualizacja tytułu:

- zwraca status `200 OK`,
- zapisuje nowy tytuł,
- nie zmienia kwoty,
- nie zmienia końcowego salda portfela.

## Use case: zmiana kwoty przelicza saldo portfela

Sprawdza, że aktualizacja `amount`:

- cofa wpływ starej kwoty,
- zapisuje nową kwotę,
- stosuje nowy wpływ na saldo.

## Use case: zmiana typu transakcji odwraca wpływ na saldo

Sprawdza zmianę:

```text
expense → income
```

razem ze zmianą kategorii na zgodną z nowym typem.

Backend powinien:

- cofnąć wpływ starego wydatku,
- zastosować wpływ nowego przychodu,
- zwrócić status `200 OK`,
- zapisać poprawne saldo.

## Use case: pusty PATCH jest odrzucany

Sprawdza, że pusty JSON:

```json
{}
```

- zwraca status `400 Bad Request`,
- nie aktualizuje transakcji.

## Use case: użytkownik nie może edytować cudzej transakcji

Sprawdza, że próba aktualizacji rekordu innego użytkownika:

- zwraca status `404 Not Found`,
- nie zmienia danych transakcji.

## Use case: typ kategorii musi pozostać zgodny z typem transakcji

Sprawdza, że próba przypisania kategorii przychodowej do wydatku:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia salda portfela.

## Use case: zmiana portfela przenosi wpływ transakcji

Sprawdza, że po zmianie `walletId`:

- wpływ transakcji jest cofany ze starego portfela,
- wpływ transakcji jest nakładany na nowy portfel,
- stare saldo wraca do wartości sprzed transakcji,
- nowe saldo zostaje odpowiednio przeliczone.

## Use case: użytkownik nie może przenieść transakcji do cudzego portfela

Sprawdza, że wskazanie portfela innego użytkownika:

- zwraca status `404 Not Found`,
- nie zmienia salda dotychczasowego portfela.

## Use case: gość nie może aktualizować transakcji

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`.

## Use case: niepoprawny JSON jest odrzucany

Sprawdza, że uszkodzony JSON:

- zwraca status `400 Bad Request`,
- nie aktualizuje transakcji.

---

# DeleteTransactionTest

Paczka sprawdza endpoint:

```http
DELETE /api/transactions/{id}
```

## Use case: zalogowany użytkownik może usunąć własną transakcję

Sprawdza, że usunięcie:

- zwraca status `204 No Content`,
- zwraca puste body,
- usuwa rekord z bazy,
- cofa wpływ transakcji na saldo portfela.

## Use case: użytkownik nie może usunąć cudzej transakcji

Sprawdza, że próba usunięcia rekordu innego użytkownika:

- zwraca status `404 Not Found`,
- nie usuwa transakcji.

## Use case: brakująca transakcja zwraca not found

Sprawdza, że próba usunięcia nieistniejącego rekordu:

- zwraca status `404 Not Found`.

## Use case: gość nie może usunąć transakcji

Sprawdza, że niezalogowany użytkownik:

- otrzymuje status `401 Unauthorized`,
- nie usuwa transakcji.

---

# TransactionBalanceTest

Paczka sprawdza logikę salda portfela niezależnie od szczegółowych odpowiedzi JSON.

## Use case: utworzenie wydatku zmniejsza saldo

Sprawdza, że:

```text
expense
```

zmniejsza saldo portfela o kwotę transakcji.

## Use case: utworzenie przychodu zwiększa saldo

Sprawdza, że:

```text
income
```

zwiększa saldo portfela o kwotę transakcji.

## Use case: usunięcie wydatku przywraca saldo

Sprawdza, że usunięcie transakcji typu `expense`:

- usuwa rekord,
- przywraca wcześniej odjętą kwotę.

## Use case: zmiana kwoty przelicza saldo

Sprawdza, że aktualizacja kwoty:

- cofa poprzedni wpływ,
- nakłada nowy wpływ,
- zapisuje końcowe saldo zgodne z nową wartością.

## Use case: zmiana typu odwraca kierunek wpływu

Sprawdza, że zmiana:

```text
expense → income
```

- cofa wpływ wydatku,
- nakłada wpływ przychodu,
- poprawnie aktualizuje saldo.

## Use case: zmiana portfela przenosi saldo

Sprawdza, że zmiana portfela:

- przywraca saldo starego portfela,
- nakłada wpływ na nowy portfel.

## Use case: jednoczesna zmiana portfela i kwoty przelicza oba portfele

Sprawdza bardziej złożony przypadek aktualizacji:

```text
zmiana walletId + zmiana amount
```

Backend powinien:

- cofnąć starą transakcję na poprzednim portfelu,
- przenieść rekord do nowego portfela,
- zastosować nową kwotę,
- poprawnie przeliczyć oba salda.

---

# Podsumowanie pokrycia

Testy funkcjonalne modułu Transaction obejmują pełny przepływ operacji:

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
→ zgodność kategorii z typem transakcji
→ automatyczne naliczanie salda
→ cofanie starego wpływu przed aktualizacją
→ przenoszenie salda pomiędzy portfelami
→ paginacja
→ filtrowanie
→ walidacja requestów
→ ochrona endpointów
```

Moduł można traktować jako funkcjonalnie zabezpieczony na poziomie MVP, ponieważ testy sprawdzają zarówno poprawne scenariusze, jak i przypadki błędne oraz próby dostępu do zasobów innych użytkowników.
