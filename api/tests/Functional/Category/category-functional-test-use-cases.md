# Category functional tests — use cases

Dokument opisuje paczki testów funkcjonalnych dla modułu Category, który odpowiada za zarządzanie kategoriami transakcji w aplikacji Expense Tracker.

Moduł Category obsługuje dwa typy kategorii:

- kategorie systemowe / domyślne,
- kategorie użytkownika.

Kategorie systemowe są globalne, mają `user = null` oraz `isDefault = true`. Są dostępne dla każdego zalogowanego użytkownika, ale nie powinny być edytowane ani usuwane przez zwykłego użytkownika.

Kategorie użytkownika są przypisane do konkretnego użytkownika, mają `user = currentUser` oraz `isDefault = false`. Użytkownik może je tworzyć, pobierać, aktualizować i usuwać.

## Cel testów

Testy funkcjonalne sprawdzają pełne zachowanie endpointów HTTP modułu Category. W szczególności obejmują:

- statusy HTTP,
- format odpowiedzi JSON,
- zapis kategorii w bazie,
- przypisanie kategorii użytkownika do aktualnie zalogowanego użytkownika,
- zwracanie kategorii systemowych,
- zwracanie kategorii użytkownika,
- brak widoczności cudzych kategorii użytkownika,
- brak możliwości edycji kategorii systemowych,
- brak możliwości usuwania kategorii systemowych,
- walidację requestów,
- ochronę endpointów przed niezalogowanym użytkownikiem,
- obsługę uszkodzonego JSON-a,
- brak przypadkowej modyfikacji lub usunięcia cudzych danych.

---

# CreateCategoryTest

Paczka sprawdza endpoint:

```http
POST /api/categories
```

## Use case: zalogowany użytkownik może utworzyć kategorię wydatkową

Sprawdza, że poprawny request tworzenia kategorii:

- zwraca status `201 Created`,
- zwraca JSON z danymi utworzonej kategorii,
- zapisuje kategorię w bazie,
- przypisuje kategorię do aktualnie zalogowanego użytkownika,
- ustawia `isDefault` na `false`,
- zapisuje poprawną nazwę kategorii,
- zapisuje typ `expense`,
- zwraca pola `createdAt` i `updatedAt`.

## Use case: zalogowany użytkownik może utworzyć kategorię przychodową

Sprawdza, że request z typem `income`:

- zwraca status `201 Created`,
- tworzy kategorię w bazie,
- zapisuje typ `income`,
- ustawia `isDefault` na `false`.

## Use case: niezalogowany użytkownik nie może utworzyć kategorii

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie tworzy kategorii w bazie.

## Use case: tworzenie kategorii z pustą nazwą

Sprawdza, że request z pustym polem `name`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy kategorii w bazie.

## Use case: tworzenie kategorii ze zbyt długą nazwą

Sprawdza, że request z nazwą dłuższą niż dozwolony limit:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy kategorii w bazie.

## Use case: tworzenie kategorii bez pola `name`

Sprawdza, że request bez wymaganego pola `name`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy kategorii w bazie.

## Use case: tworzenie kategorii bez pola `type`

Sprawdza, że request bez wymaganego pola `type`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy kategorii w bazie.

## Use case: tworzenie kategorii z niepoprawnym typem

Sprawdza, że request z typem spoza enumu `CategoryType`:

- zwraca status `422 Unprocessable Entity`,
- nie tworzy kategorii w bazie.

## Use case: tworzenie kategorii z uszkodzonym JSON-em

Sprawdza, że request z niepoprawnym składniowo JSON-em:

- zwraca status `400 Bad Request`,
- nie tworzy kategorii w bazie.

---

# ListCategoriesTest

Paczka sprawdza endpoint:

```http
GET /api/categories
```

## Use case: zalogowany użytkownik może pobrać kategorie systemowe i własne

Sprawdza, że zalogowany użytkownik:

- otrzymuje status `200 OK`,
- otrzymuje listę kategorii,
- widzi kategorie systemowe,
- widzi swoje własne kategorie,
- otrzymuje kategorie w poprawnym formacie JSON,
- widzi pola `id`, `name`, `type`, `isDefault`, `createdAt`, `updatedAt`.

## Use case: użytkownik nie widzi kategorii innych użytkowników

Sprawdza, że jeżeli w bazie istnieją prywatne kategorie różnych użytkowników:

- aktualny użytkownik dostaje status `200 OK`,
- odpowiedź zawiera kategorie systemowe,
- odpowiedź zawiera prywatne kategorie aktualnego użytkownika,
- odpowiedź nie zawiera prywatnych kategorii innych użytkowników.

## Use case: użytkownik bez własnych kategorii otrzymuje listę kategorii systemowych

Sprawdza, że zalogowany użytkownik, który nie utworzył własnych kategorii:

- otrzymuje status `200 OK`,
- otrzymuje dostępne kategorie systemowe, jeżeli zostały zainicjalizowane,
- nie otrzymuje cudzych kategorii prywatnych.

Jeżeli testy są uruchamiane bez inicjalizacji kategorii systemowych, przypadek może oczekiwać pustej listy. Po dodaniu stałego mechanizmu inicjalizacji domyślnych kategorii bardziej poprawny use case to: użytkownik bez własnych kategorii otrzymuje kategorie domyślne.

## Use case: niezalogowany użytkownik nie może pobrać listy kategorii

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie zwraca listy kategorii.

---

# GetCategoryTest

Paczka sprawdza endpoint:

```http
GET /api/categories/{id}
```

## Use case: zalogowany użytkownik może pobrać kategorię systemową

Sprawdza, że zalogowany użytkownik:

- otrzymuje status `200 OK`,
- może pobrać kategorię z `user = null`,
- otrzymuje poprawne `id`,
- otrzymuje poprawną nazwę,
- otrzymuje poprawny typ,
- otrzymuje `isDefault = true`,
- otrzymuje pola `createdAt` i `updatedAt`.

## Use case: zalogowany użytkownik może pobrać własną kategorię

Sprawdza, że właściciel kategorii:

- otrzymuje status `200 OK`,
- może pobrać kategorię przypisaną do siebie,
- otrzymuje poprawne `id`,
- otrzymuje poprawną nazwę,
- otrzymuje poprawny typ,
- otrzymuje `isDefault = false`.

## Use case: użytkownik nie może pobrać kategorii innego użytkownika

Sprawdza, że użytkownik próbujący pobrać cudzą kategorię prywatną:

- otrzymuje status `404 Not Found`,
- nie dostaje danych cudzej kategorii.

Cudza kategoria prywatna jest traktowana tak, jakby nie istniała dla aktualnego użytkownika.

## Use case: pobranie nieistniejącej kategorii

Sprawdza, że request dla nieistniejącego `id`:

- zwraca status `404 Not Found`.

## Use case: pobranie kategorii z niepoprawnym formatem ID

Sprawdza, że request z niepoprawnym formatem identyfikatora, np. `/api/categories/abc`:

- zwraca status `404 Not Found`,
- nie wykonuje logiki pobierania kategorii.

## Use case: niezalogowany użytkownik nie może pobrać kategorii

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie zwraca danych kategorii.

---

# UpdateCategoryTest

Paczka sprawdza endpoint:

```http
PATCH /api/categories/{id}
```

## Use case: zalogowany użytkownik może zmienić nazwę własnej kategorii

Sprawdza, że właściciel kategorii może zmienić pole `name`:

- zwraca status `200 OK`,
- zwraca kategorię z nową nazwą,
- zapisuje nową nazwę w bazie,
- nie zmienia typu kategorii,
- nie zmienia flagi `isDefault`.

## Use case: zalogowany użytkownik może zmienić typ własnej kategorii

Sprawdza, że właściciel kategorii może zmienić pole `type`:

- zwraca status `200 OK`,
- zwraca kategorię z nowym typem,
- zapisuje nowy typ w bazie,
- nie zmienia nazwy,
- nie zmienia flagi `isDefault`.

## Use case: zalogowany użytkownik może zmienić nazwę i typ własnej kategorii jednocześnie

Sprawdza, że request zawierający `name` i `type`:

- zwraca status `200 OK`,
- aktualizuje nazwę kategorii,
- aktualizuje typ kategorii,
- nie zmienia flagi `isDefault`.

## Use case: aktualizacja pustym payloadem

Sprawdza, że request bez żadnych dozwolonych pól do aktualizacji:

- zwraca status `400 Bad Request`,
- nie zmienia kategorii w bazie.

## Use case: aktualizacja z pustą nazwą

Sprawdza, że request z pustym polem `name`:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia kategorii w bazie.

## Use case: aktualizacja ze zbyt długą nazwą

Sprawdza, że request z nazwą dłuższą niż dozwolony limit:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia kategorii w bazie.

## Use case: aktualizacja z niepoprawnym typem

Sprawdza, że request z typem spoza enumu `CategoryType`:

- zwraca status `422 Unprocessable Entity`,
- nie zmienia kategorii w bazie.

## Use case: użytkownik nie może aktualizować kategorii systemowej

Sprawdza, że próba aktualizacji kategorii z `user = null` oraz `isDefault = true`:

- zwraca status `404 Not Found`,
- nie zmienia kategorii systemowej w bazie.

Kategorie systemowe są tylko do odczytu dla zwykłego użytkownika.

## Use case: użytkownik nie może aktualizować kategorii innego użytkownika

Sprawdza, że próba aktualizacji cudzej kategorii prywatnej:

- zwraca status `404 Not Found`,
- nie zmienia danych cudzej kategorii.

## Use case: aktualizacja nieistniejącej kategorii

Sprawdza, że request dla nieistniejącego `id`:

- zwraca status `404 Not Found`.

## Use case: niezalogowany użytkownik nie może aktualizować kategorii

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie zmienia kategorii w bazie.

## Use case: aktualizacja z uszkodzonym JSON-em

Sprawdza, że request z niepoprawnym składniowo JSON-em:

- zwraca status `400 Bad Request`,
- nie zmienia kategorii w bazie.

---

# DeleteCategoryTest

Paczka sprawdza endpoint:

```http
DELETE /api/categories/{id}
```

## Use case: zalogowany użytkownik może usunąć własną kategorię

Sprawdza, że właściciel kategorii prywatnej:

- otrzymuje status `204 No Content`,
- otrzymuje pustą odpowiedź,
- usuwa kategorię z bazy.

## Use case: użytkownik nie może usunąć kategorii systemowej

Sprawdza, że próba usunięcia kategorii z `user = null` oraz `isDefault = true`:

- zwraca status `404 Not Found`,
- nie usuwa kategorii systemowej z bazy.

Kategorie systemowe są tylko do odczytu dla zwykłego użytkownika.

## Use case: użytkownik nie może usunąć kategorii innego użytkownika

Sprawdza, że próba usunięcia cudzej kategorii prywatnej:

- zwraca status `404 Not Found`,
- nie usuwa kategorii z bazy.

## Use case: usunięcie nieistniejącej kategorii

Sprawdza, że request dla nieistniejącego `id`:

- zwraca status `404 Not Found`.

## Use case: niezalogowany użytkownik nie może usunąć kategorii

Sprawdza, że request bez poprawnej autoryzacji:

- zwraca status `401 Unauthorized`,
- nie usuwa kategorii z bazy.

## Use case: usunięcie jednej kategorii nie usuwa innych kategorii użytkownika

Sprawdza, że jeżeli użytkownik ma więcej niż jedną prywatną kategorię:

- usunięcie jednej kategorii zwraca status `204 No Content`,
- wskazana kategoria zostaje usunięta,
- pozostałe kategorie użytkownika nadal istnieją w bazie.

---

# Default category initialization

Moduł zawiera mechanizm inicjalizacji kategorii domyślnych przez komendę:

```bash
php bin/console app:category:initialize-defaults
```

## Use case: pierwsze uruchomienie inicjalizacji kategorii domyślnych

Sprawdza manualnie lub w teście komendy, że pierwsze uruchomienie:

- tworzy bazowe kategorie systemowe,
- zapisuje je z `user = null`,
- zapisuje je z `isDefault = true`,
- nie przypisuje ich do konkretnego użytkownika,
- zwraca informację o liczbie utworzonych kategorii.

## Use case: ponowne uruchomienie inicjalizacji kategorii domyślnych

Sprawdza manualnie lub w teście komendy, że kolejne uruchomienie:

- nie tworzy duplikatów,
- pozostawia istniejące kategorie domyślne bez zmian,
- zwraca informację, że utworzono `0` nowych kategorii.

---

# Zakres aktualnie potwierdzony testami

Aktualny zestaw testów potwierdza, że moduł Category obsługuje:

- tworzenie kategorii użytkownika,
- tworzenie kategorii typu `expense`,
- tworzenie kategorii typu `income`,
- walidację requestu tworzenia,
- pobieranie listy kategorii,
- zwracanie kategorii systemowych,
- zwracanie własnych kategorii użytkownika,
- ukrywanie cudzych kategorii prywatnych,
- pobieranie pojedynczej kategorii,
- aktualizację nazwy i typu własnej kategorii,
- blokadę aktualizacji kategorii systemowej,
- blokadę aktualizacji cudzej kategorii,
- usuwanie własnej kategorii,
- blokadę usuwania kategorii systemowej,
- blokadę usuwania cudzej kategorii,
- ochronę endpointów przed niezalogowanym użytkownikiem.

# Ważna decyzja domenowa

Kategorie systemowe są globalnymi rekordami dostępnymi dla każdego użytkownika. Nie są kopiowane per użytkownik.

Dane użytkownika będą trackowane w module Transaction przez relację transakcji do użytkownika oraz kategorii. Oznacza to, że wielu użytkowników może korzystać z tej samej kategorii systemowej, ale ich dane finansowe pozostają oddzielone przez `transaction.user_id`.
