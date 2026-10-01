# Ashabesuffa Foundation — Uploads

User-uploaded files live here.

## Subfolders

| Folder | Purpose | Allowed Types |
|---|---|---|
| `news/` | News article featured images | jpg, jpeg, png, gif, webp |
| `magazine/` | Magazine covers + PDFs | jpg, jpeg, png, webp, pdf |
| `magazine/covers/` | Magazine cover images | jpg, jpeg, png, webp |
| `magazine/pdfs/` | Magazine PDF files | pdf |
| `gallery/` | Gallery images | jpg, jpeg, png, gif, webp |

## Security

- PHP execution is disabled everywhere in this tree.
- Directory listing is disabled.
- Every subfolder has its own `.htaccess` + `index.php` guard.

## Permissions (Linux)

```bash
find uploads -type d -exec chmod 755 {} \;
find uploads -type f -exec chmod 644 {} \;
chown -R www-data:www-data uploads
