# Library Management System
## University Project — PHP MVC Architecture

---

## HOW TO RUN THIS PROJECT

### Step 1 — Requirements
- XAMPP installed (includes Apache + MySQL)
- PHP 7.4 or higher
- A web browser

### Step 2 — Setup the Project
1. Copy the entire `library_system` folder into your XAMPP `htdocs` folder:
   ```
   C:\xampp\htdocs\library_system\
   ```

2. Start **Apache** and **MySQL** from XAMPP Control Panel.

### Step 3 — Create the Database
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click **New** to create a database (name it `library_db`)
3. Click **Import**, then choose the file `database.sql`
4. Click **Go** — all tables and sample data will be created.

### Step 4 — Run the Project
Open your browser and go to:
```
http://localhost/library_system/
```

---

## DEMO LOGIN ACCOUNTS

| Role     | Email                     | Password     |
|----------|---------------------------|--------------|
| Admin    | admin@library.com         | password123  |
| Manager  | manager@library.com       | password123  |
| Librarian| librarian@library.com     | password123  |
| Member   | alice@member.com          | password123  |
| Member   | bob@member.com            | password123  |

---

## PROJECT STRUCTURE

```
library_system/
│
├── index.php                  ← Main router (front controller)
├── database.sql               ← All SQL tables + sample data
├── ajax_search.php            ← AJAX book search endpoint
├── ajax_availability.php      ← AJAX availability check endpoint
│
├── config/
│   ├── Connect.php            ← Database connection function
│   └── Close.php              ← Close connection function
│
├── models/                    ← ONLY SQL queries (no HTML)
│   ├── UserModel.php
│   ├── BookModel.php
│   ├── BorrowModel.php
│   ├── BranchModel.php
│   ├── ReviewModel.php        ← Also has Fine, ReadingList, Reservation functions
│   └── FineModel.php
│
├── controllers/               ← Handle GET/POST, validate, redirect
│   ├── LoginController.php
│   ├── LogoutController.php
│   ├── RegisterController.php
│   ├── BookIndexController.php
│   ├── BookSaveController.php
│   ├── BookUpdateController.php
│   ├── BookDeleteController.php
│   ├── BorrowController.php
│   ├── ReservationController.php
│   ├── ReviewController.php
│   ├── ProfileController.php
│   ├── BranchController.php
│   └── UserController.php
│
├── views/                     ← ONLY HTML + echo (no SQL queries)
│   ├── header.php             ← Shared navbar
│   ├── footer.php             ← Shared footer
│   ├── home.php
│   ├── login.php
│   ├── register.php
│   ├── 404.php
│   │
│   ├── member/
│   │   ├── dashboard.php
│   │   ├── browse_books.php
│   │   ├── book_detail.php
│   │   ├── my_borrows.php
│   │   ├── my_reservations.php
│   │   ├── my_fines.php
│   │   ├── reading_list.php
│   │   └── profile.php
│   │
│   ├── librarian/
│   │   ├── dashboard.php
│   │   ├── book_list.php
│   │   ├── add_book.php
│   │   ├── edit_book.php
│   │   ├── manage_borrows.php
│   │   ├── manage_fines.php
│   │   ├── manage_genres.php
│   │   └── announcements.php
│   │
│   ├── manager/
│   │   ├── dashboard.php
│   │   ├── manage_branches.php
│   │   ├── branch_policy.php
│   │   ├── branch_report.php
│   │   └── transfer_requests.php
│   │
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── manage_users.php
│   │   ├── reports.php
│   │   └── announcements.php
│   │
│   ├── css/
│   │   └── style.css          ← Plain CSS (no Bootstrap/Tailwind)
│   │
│   └── js/
│       └── main.js            ← Plain JS + AJAX (XMLHttpRequest only)
│
└── uploads/                   ← Book cover images, profile pictures
    ├── no_cover.png
    └── default.png
```

---

## MVC EXPLANATION

**MVC = Model, View, Controller**

This is a design pattern that separates the application into 3 layers:

### MODEL (models/ folder)
- Contains ONLY database functions (SQL queries)
- No HTML output, no form handling
- Example: `getAllBooks($conn)` runs a SELECT and returns an array

### VIEW (views/ folder)
- Contains ONLY HTML, forms, tables, and `echo` statements
- No SQL queries allowed here
- Data is passed in from the controller (via variables)

### CONTROLLER (controllers/ folder)
- Receives the user's form submission (GET or POST)
- Validates the data
- Calls model functions to save/fetch data
- Sets session messages
- Redirects using `header()`

### FLOW EXAMPLE (Borrow a Book):
```
Member clicks "Borrow" → form submits POST to index.php?page=do_borrow
  → index.php routes to BorrowController.php
    → validates: is user logged in? is book available?
      → calls BorrowModel: createBorrowRequest($conn, ...)
        → sets $_SESSION['msg'] = "Request submitted!"
          → redirects to index.php?page=my_borrows
            → loads views/member/my_borrows.php
```

---

## GET vs POST EXPLANATION

### POST is used for:
- Login form (`do_login`)
- Registration form (`do_register`)
- Adding a book (`do_save_book`)
- Editing a book (`do_update_book`)
- Submitting a borrow request (`do_borrow&action=request`)
- Submitting a review (`do_review`)
- Updating profile (`do_profile`)
- Creating a branch (`do_branch&action=create`)

### GET is used for:
- Viewing a page (`?page=browse_books`)
- Approving a borrow (`?page=do_borrow&action=approve&id=5`)
- Deleting a book (`?page=do_delete_book&id=3`)
- Viewing a book detail (`?page=book_detail&id=2`)
- Marking a fine paid (`?page=manage_fines&action=paid&id=1`)
- Cancelling a reservation (`?page=do_reservation&action=cancel&id=4`)

---

## SESSION EXPLANATION

Sessions are used to remember who is logged in:

```php
$_SESSION['user']      // user ID (e.g. 4)
$_SESSION['user_name'] // display name (e.g. "Alice Member")
$_SESSION['role']      // role string (e.g. "member", "admin")
$_SESSION['branch_id'] // branch ID for librarian/manager
$_SESSION['msg']       // success message shown on next page
$_SESSION['error']     // error message shown on next page
```

`session_start()` is called at the top of `index.php` (the main router).

---

## AJAX EXPLANATION

AJAX allows the browser to talk to the server without reloading the page.

### Where AJAX is used:
1. **Book Search** (`browse_books.php` + `ajax_search.php`)
   - User types in the search box → `searchBooksAjax()` is called
   - XMLHttpRequest sends GET to `ajax_search.php?keyword=php`
   - Server returns JSON array of matching books
   - JavaScript builds new book cards and shows them

2. **Book Availability** (`book_detail.php` + `ajax_availability.php`)
   - User selects a branch from the dropdown
   - `checkAvailability()` sends GET to `ajax_availability.php`
   - Server returns `{"available": 2, "total": 3}`
   - JavaScript shows "✓ Available (2 copies)" in green

### XMLHttpRequest example:
```javascript
var xhr = new XMLHttpRequest();
xhr.open("GET", "ajax_search.php?keyword=php", true);
xhr.onreadystatechange = function () {
    if (xhr.readyState == 4 && xhr.status == 200) {
        var data = JSON.parse(xhr.responseText);
        // use data...
    }
};
xhr.send();
```

---

## VALIDATION EXPLANATION

### Client-side (JavaScript in main.js):
- Checks empty fields with `if (field == '')`
- Validates email format with regex
- Checks password length
- Confirms password match
- Shows error message below the field using `innerHTML`
- Returns `false` to stop form submission

### Server-side (PHP in Controllers):
- `empty()` — check if field is empty
- `strlen()` — check string length
- `filter_var($email, FILTER_VALIDATE_EMAIL)` — validate email
- `htmlspecialchars()` — sanitize output to prevent XSS
- On error: set `$_SESSION['error']` and `header("Location: ...")`

---

## DATABASE TABLES

| Table                | Purpose                                    |
|----------------------|--------------------------------------------|
| users                | All users (members, librarians, admins)    |
| branches             | Library branch locations                   |
| branch_policies      | Borrowing rules per branch                 |
| genres               | Book genres/categories                     |
| books                | Book catalog                               |
| branch_inventory     | Copies available per branch per book       |
| borrow_records       | Borrow requests and history                |
| reservations         | Book reservations by members               |
| fines                | Late return fines                          |
| book_reviews         | Member reviews and ratings                 |
| announcements        | Library announcements                      |
| inter_branch_requests| Book transfer requests between branches    |
| reading_lists        | Personal reading wishlist per member       |

---

## ROLE FEATURES

| Feature                  | Member | Librarian | Manager | Admin |
|--------------------------|--------|-----------|---------|-------|
| Register / Login         | ✓      | ✓         | ✓       | ✓     |
| Browse & Search Books    | ✓      | ✓         | ✓       | ✓     |
| View Book Details        | ✓      | ✓         | ✓       | ✓     |
| Borrow Request           | ✓      |           |         |       |
| Reserve Books            | ✓      |           |         |       |
| Reading List             | ✓      |           |         |       |
| Write Reviews            | ✓      |           |         |       |
| View My Fines            | ✓      |           |         |       |
| Update Profile           | ✓      | ✓         | ✓       | ✓     |
| Add / Edit / Delete Books|        | ✓         |         | ✓     |
| Manage Genres            |        | ✓         |         | ✓     |
| Approve Borrow Requests  |        | ✓         |         | ✓     |
| Process Returns          |        | ✓         |         | ✓     |
| Manage Fines             |        | ✓         |         | ✓     |
| Post Announcements       |        | ✓         | ✓       | ✓     |
| Manage Branches          |        |           | ✓       | ✓     |
| Set Branch Policies      |        |           | ✓       | ✓     |
| Approve Transfers        |        |           | ✓       | ✓     |
| Branch Reports           |        |           | ✓       | ✓     |
| Manage All Users         |        |           |         | ✓     |
| Platform-wide Reports    |        |           |         | ✓     |

---

*University Project — Library Management System*
*Technologies: PHP, MySQL, HTML, CSS, JavaScript, AJAX, MVC*
