# Project Folder Structure

```text
final-project/
  app/
    Http/
      Controllers/
        Admin/
        Owner/
        User/
      Middleware/
      Requests/
    Models/
    Services/
      Comparison/
      Geo/
      Pricing/
  database/
    migrations/
    seeders/
    factories/
  resources/
    views/
      admin/
      owner/
      user/
      components/
      layouts/
    css/
    js/
  routes/
    web.php
    api.php (optional)
  docs/
    capstone/
```

## MVC boundaries
- `Controllers`: request orchestration and authorization.
- `Models`: relationships and reusable scopes.
- `Services`: reusable business logic (distance, comparison, pricing stats).
- `Views`: role-based UI pages and reusable components.
