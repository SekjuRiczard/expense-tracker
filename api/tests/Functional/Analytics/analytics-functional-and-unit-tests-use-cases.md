# Analytics functional and unit tests — use cases

Dokument opisuje testy modułu Analytics w aplikacji Expense Tracker.

Moduł Analytics odpowiada wyłącznie za odczyt i agregację danych finansowych. Nie tworzy ani nie modyfikuje portfeli, kategorii, transakcji lub budżetów. Korzysta z istniejących danych i zwraca gotowe podsumowania dla frontendu.

Testy obejmują:

- wykorzystanie budżetu,
- podsumowanie finansowe dla wybranego okresu,
- wydatki pogrupowane według kategorii,
- miesięczny cash flow,
- walidację zakresów dat,
- filtrowanie po walucie,
- ownership danych,
- ochronę endpointów przed niezalogowanym użytkownikiem,
- testy jednostkowe kalkulatorów.

---

# Zakres endpointów

```http
GET /api/analytics/budgets/{id}/usage
GET /api/analytics/summary
GET /api/analytics/categories
GET /api/analytics/cash-flow
```

Każdy endpoint działa w kontekście aktualnie uwierzytelnionego użytkownika.

Dane analityczne są filtrowane po walucie portfela. Moduł nie sumuje razem kwot w różnych walutach.

---

# GetBudgetUsageTest

Paczka sprawdza endpoint:

```http
GET /api/analytics/budgets/{id}/usage
```

Endpoint zwraca wykorzystanie wskazanego budżetu.

Przykładowa odpowiedź:

```json
{
  "budgetId": 1,
  "budgetName": "Budżet domowy",
  "budgetAmount": 300000,
  "currency": "PLN",
  "startDate": "2026-06-01",
  "endDate": "2026-06-30",
  "spent": 185000,
  "remaining": 115000,
  "percentage": 61.67,
  "exceeded": false
}
```

## Use case: użytkownik otrzymuje zerowe wykorzystanie pustego budżetu

Sprawdza, że budżet bez wydatków:

- zwraca status `200 OK`,
- zwraca `spent = 0`,
- zwraca pełną kwotę jako `remaining`,
- zwraca `percentage = 0`,
- zwraca `exceeded = false`.

## Use case: wydatki z okresu budżetu są sumowane

Sprawdza, że endpoint:

- uwzględnia transakcje typu `expense`,
- sumuje wiele wydatków,
- poprawnie wylicza `spent`,
- poprawnie wylicza `remaining`,
- poprawnie wylicza `percentage`,
- nie oznacza budżetu jako przekroczonego, jeżeli wydatki nie przekraczają limitu.

## Use case: wydatek z ostatniego dnia okresu jest uwzględniany

Sprawdza, że transakcja wykonana ostatniego dnia budżetu, np.:

```text
2026-06-30T23:59:59
```

jest uwzględniona w agregacji.

## Use case: przychody nie wpływają na wykorzystanie budżetu

Sprawdza, że transakcje typu `income` nie zwiększają `spent`.

## Use case: wydatki spoza okresu są ignorowane

Sprawdza, że transakcje po dacie końcowej budżetu nie wpływają na wynik.

## Use case: wydatki w innej walucie są ignorowane

Sprawdza, że budżet PLN nie uwzględnia wydatków z portfela EUR.

## Use case: przekroczony budżet jest poprawnie oznaczany

Sprawdza, że gdy `spent > budgetAmount`, endpoint:

- zwraca ujemne `remaining`,
- zwraca `percentage > 100`,
- zwraca `exceeded = true`.

## Use case: transakcje innych użytkowników są ignorowane

Sprawdza, że agregacja wykorzystuje wyłącznie transakcje właściciela budżetu.

## Use case: użytkownik nie może odczytać wykorzystania cudzego budżetu

Sprawdza, że próba odczytu budżetu innego użytkownika:

- zwraca status `404 Not Found`,
- nie ujawnia istnienia cudzego rekordu.

## Use case: brakujący budżet zwraca not found

Sprawdza, że nieistniejący identyfikator budżetu zwraca `404 Not Found`.

## Use case: gość nie może pobrać wykorzystania budżetu

Sprawdza, że niezalogowany użytkownik otrzymuje `401 Unauthorized`.

---

# GetPeriodSummaryTest

Paczka sprawdza endpoint:

```http
GET /api/analytics/summary?from=2026-06-01&to=2026-06-30&currency=PLN
```

Endpoint zwraca podsumowanie finansowe dla wybranego okresu i waluty.

Przykładowa odpowiedź:

```json
{
  "currency": "PLN",
  "from": "2026-06-01",
  "to": "2026-06-30",
  "income": 700000,
  "expense": 200000,
  "balance": 500000,
  "transactionCount": 3
}
```

## Use case: użytkownik otrzymuje puste podsumowanie

Sprawdza, że brak transakcji zwraca zera dla `income`, `expense`, `balance` i `transactionCount`.

## Use case: podsumowanie poprawnie liczy przychody, wydatki i bilans

Sprawdza wzory:

```text
income  = suma transakcji typu income
expense = suma transakcji typu expense
balance = income - expense
```

Sprawdza również liczbę transakcji w okresie.

## Use case: transakcja z ostatniego dnia okresu jest uwzględniana

Sprawdza, że transakcja wykonana ostatniego dnia o późnej godzinie trafia do podsumowania.

## Use case: transakcje spoza okresu są ignorowane

Sprawdza, że agregacja nie uwzględnia rekordów spoza zakresu dat.

## Use case: transakcje w innej walucie są ignorowane

Sprawdza, że podsumowanie PLN nie zawiera transakcji z portfela EUR.

## Use case: transakcje innych użytkowników są ignorowane

Sprawdza, że użytkownik widzi tylko własne agregaty.

## Use case: niepoprawny zakres dat jest odrzucany

Sprawdza, że `from > to` zwraca `422 Unprocessable Entity`.

## Use case: gość nie może pobrać podsumowania

Sprawdza, że niezalogowany użytkownik otrzymuje `401 Unauthorized`.

---

# GetCategoryBreakdownTest

Paczka sprawdza endpoint:

```http
GET /api/analytics/categories?from=2026-06-01&to=2026-06-30&currency=PLN
```

Endpoint zwraca rozkład wydatków według kategorii.

Przykładowa odpowiedź:

```json
[
  {
    "categoryId": 1,
    "categoryName": "Jedzenie",
    "amount": 85000,
    "percentage": 70.83
  },
  {
    "categoryId": 2,
    "categoryName": "Transport",
    "amount": 35000,
    "percentage": 29.17
  }
]
```

## Use case: użytkownik otrzymuje pustą listę kategorii

Sprawdza, że brak wydatków zwraca pustą tablicę JSON.

## Use case: wydatki są grupowane według kategorii

Sprawdza, że endpoint:

- sumuje wiele wydatków przypisanych do tej samej kategorii,
- zwraca osobny rekord dla każdej kategorii,
- zwraca `categoryId`, `categoryName`, `amount` oraz `percentage`.

## Use case: kategorie są sortowane malejąco po kwocie

Sprawdza, że największa kategoria wydatków znajduje się na początku listy.

## Use case: przychody są ignorowane

Sprawdza, że transakcje typu `income` nie pojawiają się w rozkładzie wydatków.

## Use case: wydatek z ostatniego dnia okresu jest uwzględniany

Sprawdza, że transakcja z ostatniego dnia okresu trafia do właściwej kategorii.

## Use case: wydatki spoza okresu są ignorowane

Sprawdza, że transakcje spoza zakresu dat nie wpływają na wyniki.

## Use case: wydatki w innej walucie są ignorowane

Sprawdza, że agregacja dla PLN nie uwzględnia transakcji z portfela EUR.

## Use case: wydatki innych użytkowników są ignorowane

Sprawdza, że endpoint zwraca wyłącznie agregaty aktualnie zalogowanego użytkownika.

## Use case: niepoprawny zakres dat jest odrzucany

Sprawdza, że `from > to` zwraca `422 Unprocessable Entity`.

## Use case: gość nie może pobrać rozkładu kategorii

Sprawdza, że niezalogowany użytkownik otrzymuje `401 Unauthorized`.

---

# GetCashFlowTest

Paczka sprawdza endpoint:

```http
GET /api/analytics/cash-flow?from=2026-01-01&to=2026-12-31&currency=PLN
```

Endpoint zwraca miesięczny cash flow użytkownika.

Przykładowa odpowiedź:

```json
[
  {
    "period": "2026-01",
    "income": 700000,
    "expense": 180000,
    "balance": 520000
  },
  {
    "period": "2026-02",
    "income": 720000,
    "expense": 220000,
    "balance": 500000
  }
]
```

## Use case: użytkownik otrzymuje pusty cash flow

Sprawdza, że brak transakcji zwraca pustą tablicę JSON.

## Use case: transakcje są grupowane według miesiąca

Sprawdza, że endpoint:

- grupuje przychody i wydatki miesięcznie,
- zwraca osobne rekordy dla kolejnych miesięcy,
- zwraca `period`, `income`, `expense` i `balance`.

## Use case: wiele transakcji z jednego miesiąca jest sumowanych

Sprawdza, że kilka przychodów i kilka wydatków z tego samego miesiąca tworzy jeden punkt cash flow.

## Use case: ujemny bilans jest zwracany poprawnie

Sprawdza, że gdy `expense > income`, endpoint zwraca ujemne `balance`.

## Use case: okresy są sortowane chronologicznie

Sprawdza kolejność:

```text
2026-01
2026-02
2026-03
```

## Use case: miesiące z różnych lat są rozdzielane

Sprawdza, że `2025-12` i `2026-01` są traktowane jako dwa osobne okresy.

## Use case: transakcja z ostatniego dnia okresu jest uwzględniana

Sprawdza, że transakcja wykonana ostatniego dnia zakresu trafia do odpowiedniego miesiąca.

## Use case: transakcje spoza okresu są ignorowane

Sprawdza, że endpoint nie uwzględnia rekordów spoza zakresu dat.

## Use case: transakcje w innej walucie są ignorowane

Sprawdza, że cash flow PLN nie zawiera transakcji z portfela EUR.

## Use case: transakcje innych użytkowników są ignorowane

Sprawdza, że użytkownik otrzymuje wyłącznie własne agregaty.

## Use case: niepoprawny zakres dat jest odrzucany

Sprawdza, że `from > to` zwraca `422 Unprocessable Entity`.

## Use case: gość nie może pobrać cash flow

Sprawdza, że niezalogowany użytkownik otrzymuje `401 Unauthorized`.

---

# BudgetUsageCalculatorTest

Paczka testów jednostkowych dla klasy:

```text
BudgetUsageCalculator
```

Kalkulator otrzymuje budżet i sumę wydatków, a następnie wylicza:

```text
spent
remaining
percentage
exceeded
```

## Use case: kalkulator wylicza częściowe wykorzystanie budżetu

Sprawdza, że dla:

```text
budgetAmount = 300000
spent        = 185000
```

wynik to:

```text
remaining  = 115000
percentage = 61.67
exceeded   = false
```

## Use case: kalkulator obsługuje budżet bez wydatków

Sprawdza, że dla `spent = 0` wynik zawiera pełną pozostałą kwotę, `percentage = 0` i `exceeded = false`.

## Use case: kalkulator oznacza przekroczony budżet

Sprawdza, że dla `spent > budgetAmount` wynik zawiera ujemne `remaining`, `percentage > 100` oraz `exceeded = true`.

## Use case: dokładne wykorzystanie 100% nie oznacza przekroczenia

Sprawdza, że `spent = budgetAmount` zwraca `remaining = 0`, `percentage = 100` oraz `exceeded = false`.

## Use case: kalkulator odrzuca budżet z niedodatnią kwotą

Sprawdza ochronę przed dzieleniem przez zero. Dla `budgetAmount <= 0` kalkulator rzuca `LogicException`.

## Use case: kalkulator przekazuje koniec zakresu jako datę wyłączną

Sprawdza, że dla budżetu `2026-06-01 → 2026-06-30` reader otrzymuje:

```text
startDate        = 2026-06-01
endDateExclusive = 2026-07-01
```

---

# CategoryBreakdownCalculatorTest

Paczka testów jednostkowych dla klasy:

```text
CategoryBreakdownCalculator
```

Kalkulator otrzymuje zagregowane wydatki kategorii i wylicza ich procentowy udział.

## Use case: pusta lista zwraca pusty wynik

Sprawdza, że kalkulator poprawnie obsługuje brak danych.

## Use case: kalkulator wylicza udział procentowy kategorii

Sprawdza wzór:

```text
percentage = categoryAmount / totalAmount * 100
```

## Use case: pojedyncza kategoria otrzymuje 100%

Sprawdza, że jedna kategoria obejmująca cały wydatek otrzymuje `percentage = 100`.

## Use case: kalkulator zachowuje kolejność danych wejściowych

Sprawdza, że kalkulator nie sortuje samodzielnie wyników. Sortowanie pozostaje odpowiedzialnością repozytorium.

## Use case: suma równa zero nie powoduje dzielenia przez zero

Sprawdza, że dla `totalAmount = 0` kalkulator zwraca `percentage = 0`.

---

# Podsumowanie pokrycia

Testy modułu Analytics obejmują pełny zakres MVP:

```text
budget usage
→ summary
→ category breakdown
→ monthly cash flow
```

Najważniejsze reguły objęte testami:

```text
ownership
→ filtrowanie po użytkowniku
→ filtrowanie po walucie
→ filtrowanie po zakresie dat
→ uwzględnienie ostatniego dnia okresu
→ ignorowanie przychodów przy analizie wydatków
→ agregacje miesięczne
→ grupowanie kategorii
→ procentowe udziały
→ przekroczenie budżetu
→ ochrona przed dzieleniem przez zero
→ walidacja zakresów dat
→ ochrona endpointów przed guestem
```

