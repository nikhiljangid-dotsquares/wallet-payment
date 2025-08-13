# Admin Category Manager

This package allows you to perform CRUD operations for managing categories in the admin panel.

---

## Features

- Create new categories
- View a list of existing categories
- Update category details
- Delete categories
- Feature or sub categories

---

## Requirements

- PHP >=8.2
- Laravel Framework >= 12.x

---

## Installation

### 1. Add Git Repository to `composer.json`

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/pavanraj92/admin-categories.git"
    }
]
```

### 2. Require the package via Composer
    ```bash
    composer require admin/category:@dev
    ```

### 3. Publish assets
    ```bash
    php artisan categories:publish --force
    ```
---


## Usage

1. **Create**: Add a new category with name and description.
2. **Read**: View all categories in a paginated list.
3. **Update**: Edit category information.
4. **Delete**: Remove categories that are no longer needed.

## Admin Panel Routes

| Method | Endpoint           | Description           |
|--------|-------------------|-----------------------|
| GET    | `/categories`     | List all categories   |
| POST   | `/categories`     | Create a new category |
| GET    | `/categories/{id}`| Get category details  |
| PUT    | `/categories/{id}`| Update a category     |
| DELETE | `/categories/{id}`| Delete a category     |

---

## Protecting Admin Routes

Protect your routes using the provided middleware:

```php
Route::middleware(['web','admin.auth'])->group(function () {
    // categories routes here
});
```
---

## Database Tables

- `categories` - Stores role information

---

## License

This package is open-sourced software licensed under the MIT license.