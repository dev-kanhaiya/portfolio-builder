# Devfolio

Devfolio is a small Laravel portfolio and resume builder for a BCA minor project. The pages are built with Laravel Blade, regular CSS, and a small amount of browser JavaScript. SQLite stores the account, portfolio, profile, skills, projects, work history, and education.

## Start the project

You need PHP 8.3 or newer, Composer, and SQLite support.

```bash
composer setup
composer dev
```

Open `http://127.0.0.1:8000`, create an account, and start a portfolio from the dashboard. `composer setup` installs the PHP packages, creates the SQLite database, runs migrations, and links the local upload folder.

## What is included

- Account registration, login, and logout
- A dashboard to create, edit, clone, publish, unpublish, and delete portfolios
- A split editor with a live preview, five themes, and a plain HTML/CSS editor
- Profile photos, cover banners, and project screenshot uploads saved on Laravel's public disk
- Public portfolio pages and a simple resume page
- A print-friendly resume. Choose **Download PDF** and then **Save as PDF** in the browser's print dialog.

Uploaded JPG and PNG images are converted to WebP when PHP GD supports it. Without GD, Laravel stores the uploaded image in its original supported format.
