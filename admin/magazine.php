<?php
/**
 * ============================================================
 * Ashabesuffa Foundation - Admin Magazine Manager
 * Handles: Issues (create/edit/delete) + Articles (create/edit/delete)
 * Features: PDF upload, cover image upload, multilingual fields,
 *           publish toggle, sort order, search, pagination
 * ============================================================
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/security.php';
require_admin();

/* ------------------------------------------------------------
 * Helpers (local)
 * ---------------------------------------------------------- */

function mag_flash($type, $msg) {
    $_SESSION['mag_flash'][$type] = $msg;
}

function mag_take_flash() {
    if (!empty($_SESSION['mag_flash'])) {
        $f = $_SESSION['mag_flash'];
        unset($_SESSION['mag_flash']);
        return $f;
    }
    return null;
}

function mag_month_name($m) {
    $names = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];
    return $names[(int)$m] ?? '';
}

function mag_old($key, $default = '') {
    return isset($_POST[$key]) ? clean_input($_POST[$key]) : $default;
}

/* ------------------------------------------------------------
 * Actions
 * ---------------------------------------------------------- */

$view   = $_GET['view']   ?? 'issues';   // issues | articles
$action = $_GET['action'] ?? 'list';     // list | new | edit | delete
$id     = (int)($_GET['id'] ?? 0);
$issueId = (int)($_GET['issue'] ?? 0);

/* ---------------- ISSUE: DELETE ---------------- */
if ($view === 'issues' && $action === 'delete' && $id) {
    if (!verify_csrf()) {
        mag_flash('error', 'Invalid session.');
        redirect(BASE_URL . 'admin/magazine.php');
    }
    // fetch to remove files
    $stmt = db()->prepare("SELECT cover_image, pdf_file FROM magazine_issues WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if ($row) {
        foreach (['cover_image', 'pdf_file'] as $f) {
            if (!empty($row[$f]) && file_exists(UPLOAD_DIR . $row[$f])) {
                @unlink(UPLOAD_DIR . $row[$f]);
            }
        }
        db()->prepare("DELETE FROM magazine_issues WHERE id=?")->execute([$id]);
        // articles cascade via FK
        mag_flash('success', 'Issue deleted.');
    }
    redirect(BASE_URL . 'admin/magazine.php');
}

/* ---------------- ISSUE: SAVE (new / edit) ---------------- */
if ($view === 'issues' && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'issue') {
    if (!verify_csrf()) {
        mag_flash('error', 'Invalid session.');
        redirect(BASE_URL . 'admin/magazine.php');
    }

    $issueNumber = (int)($_POST['issue_number'] ?? 0);
    $issueYear   = (int)($_POST['issue_year'] ?? date('Y'));
    $issueMonth  = (int)($_POST['issue_month'] ?? date('n'));

    $titleEn = clean_input($_POST['title_en'] ?? '');
    $titleUr = clean_input($_POST['title_ur'] ?? '');
    $titleAr = clean_input($_POST['title_ar'] ?? '');

    $descEn = clean_input($_POST['description_en'] ?? '');
    $descUr = clean_input($_POST['description_ur'] ?? '');
    $descAr = clean_input($_POST['description_ar'] ?? '');

    $isPublished = !empty($_POST['is_published']) ? 1 : 0;

    if (!$issueNumber || !$issueYear || !$issueMonth) {
        mag_flash('error', 'Issue number, year, and month are required.');
        redirect(BASE_URL . 'admin/magazine.php?view=issues&action=' . ($id ? 'edit&id=' . $id : 'new'));
    }

    // Auto-fill title if empty
    if (!$titleEn) $titleEn = 'Issue ' . $issueNumber . ' — ' . mag_month_name($issueMonth) . ' ' . $issueYear;
    if (!$titleUr) $titleUr = 'شمارہ ' . $issueNumber . ' — ' . mag_month_name($issueMonth) . ' ' . $issueYear;
    if (!$titleAr) $titleAr = 'العدد ' . $issueNumber . ' — ' . mag_month_name($issueMonth) . ' ' . $issueYear;

    // Handle uploads
    $coverPath = null;
    $pdfPath   = null;

    if (!empty($_FILES['cover_image']['name'])) {
        $up = upload_file('cover_image', allowed_image_mimes(), 'magazine/covers');
        if (!$up['ok']) {
            mag_flash('error', 'Cover: ' . $up['error']);
            redirect(BASE_URL . 'admin/magazine.php?view=issues&action=' . ($id ? 'edit&id=' . $id : 'new'));
        }
        $coverPath = $up['path'];
    }

    if (!empty($_FILES['pdf_file']['name'])) {
        $up = upload_file('pdf_file', allowed_doc_mimes(), 'magazine/pdf');
        if (!$up['ok']) {
            mag_flash('error', 'PDF: ' . $up['error']);
            redirect(BASE_URL . 'admin/magazine.php?view=issues&action=' . ($id ? 'edit&id=' . $id : 'new'));
        }
        $pdfPath = $up['path'];
    }

    try {
        if ($id) {
            // Update
            $sql = "UPDATE magazine_issues SET
                        issue_number=?, issue_year=?, issue_month=?,
                        title_en=?, title_ur=?, title_ar=?,
                        description_en=?, description_ur=?, description_ar=?,
                        is_published=?
                        " . ($coverPath ? ", cover_image=?" : "") . "
                        " . ($pdfPath ? ", pdf_file=?" : "") . "
                    WHERE id=?";
            $params = [
                $issueNumber, $issueYear, $issueMonth,
                $titleEn, $titleUr, $titleAr,
                $descEn, $descUr, $descAr,
                $isPublished
            ];
            if ($coverPath) $params[] = $coverPath;
            if ($pdfPath)   $params[] = $pdfPath;
            $params[] = $id;

            db()->prepare($sql)->execute($params);
            mag_flash('success', 'Issue updated successfully.');
        } else {
            // Insert
            $stmt = db()->prepare("INSERT INTO magazine_issues
                (issue_number, issue_year, issue_month,
                 title_en, title_ur, title_ar,
                 description_en, description_ur, description_ar,
                 cover_image, pdf_file, is_published)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $issueNumber, $issueYear, $issueMonth,
                $titleEn, $titleUr, $titleAr,
                $descEn, $descUr, $descAr,
                $coverPath, $pdfPath, $isPublished
            ]);
            mag_flash('success', 'Issue created successfully.');
        }
    } catch (PDOException $e) {
        // Duplicate (issue_number, issue_year) unique constraint
        if ($e->getCode() === '23000') {
            mag_flash('error', 'An issue with this number already exists for that year.');
        } else {
            mag_flash('error', 'Database error: ' . $e->getMessage());
        }
    }

    redirect(BASE_URL . 'admin/magazine.php?view=issues');
}

/* ---------------- ARTICLE: DELETE ---------------- */
if ($view === 'articles' && $action === 'delete' && $id) {
    if (!verify_csrf()) {
        mag_flash('error', 'Invalid session.');
        redirect(BASE_URL . 'admin/magazine.php?view=articles');
    }
    $stmt = db()->prepare("SELECT issue_id FROM magazine_articles WHERE id=?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    $backIssue = $row['issue_id'] ?? $issueId;

    db()->prepare("DELETE FROM magazine_articles WHERE id=?")->execute([$id]);
    mag_flash('success', 'Article deleted.');
    redirect(BASE_URL . 'admin/magazine.php?view=articles&issue=' . (int)$backIssue);
}

/* ---------------- ARTICLE: SAVE ---------------- */
if ($view === 'articles' && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'article') {
    if (!verify_csrf()) {
        mag_flash('error', 'Invalid session.');
        redirect(BASE_URL . 'admin/magazine.php?view=articles');
    }

    $articleIssueId = (int)($_POST['issue_id'] ?? 0);
    $titleEn = clean_input($_POST['title_en'] ?? '');
    $titleUr = clean_input($_POST['title_ur'] ?? '');
    $titleAr = clean_input($_POST['title_ar'] ?? '');

    $author = clean_input($_POST['author_name'] ?? '');
    $pageNo = (int)($_POST['page_number'] ?? 0) ?: null;

    $contentEn = $_POST['content_en'] ?? '';
    $contentUr = $_POST['content_ur'] ?? '';
    $contentAr = $_POST['content_ar'] ?? '';

    $slug = slugify($titleEn ?: ('article-' . time()));

    if (!$articleIssueId || !$titleEn) {
        mag_flash('error', 'Issue and English title are required.');
        redirect(BASE_URL . 'admin/magazine.php?view=articles&action=' . ($id ? 'edit&id=' . $id : 'new') . ($articleIssueId ? '&issue=' . $articleIssueId : ''));
    }

    try {
        if ($id) {
            $stmt = db()->prepare("UPDATE magazine_articles SET
                issue_id=?, slug=?,
                title_en=?, title_ur=?, title_ar=?,
                author_name=?, page_number=?,
                content_en=?, content_ur=?, content_ar=?
                WHERE id=?");
            $stmt->execute([
                $articleIssueId, $slug,
                $titleEn, $titleUr, $titleAr,
                $author, $pageNo,
                $contentEn, $contentUr, $contentAr,
                $id
            ]);
            mag_flash('success', 'Article updated.');
        } else {
            $stmt = db()->prepare("INSERT INTO magazine_articles
                (issue_id, slug, title_en, title_ur, title_ar,
                 author_name, page_number,
                 content_en, content_ur, content_ar)
                VALUES (?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([
                $articleIssueId, $slug,
                $titleEn, $titleUr, $titleAr,
                $author, $pageNo,
                $contentEn, $contentUr, $contentAr
            ]);
            mag_flash('success', 'Article created.');
        }
    } catch (PDOException $e) {
        mag_flash('error', 'Database error: ' . $e->getMessage());
    }

    redirect(BASE_URL . 'admin/magazine.php?view=articles&issue=' . $articleIssueId);
}

/* ------------------------------------------------------------
 * Data for the view
 * ---------------------------------------------------------- */

// List of issues for dropdowns
$allIssues = db()->query("SELECT id, issue_number, issue_year, issue_month, title_en FROM magazine_issues ORDER BY issue_year DESC, issue_month DESC")->fetchAll();

// Edit targets
$editIssue = null;
$editArticle = null;

if ($view === 'issues' && $action === 'edit' && $id) {
    $stmt = db()->prepare("SELECT * FROM magazine_issues WHERE id=?");
    $stmt->execute([$id]);
    $editIssue = $stmt->fetch();
}

if ($view === 'articles' && $action === 'edit' && $id) {
    $stmt = db()->prepare("SELECT * FROM magazine_articles WHERE id=?");
    $stmt->execute([$id]);
    $editArticle = $stmt->fetch();
    if ($editArticle) $issueId = (int)$editArticle['issue_id'];
}

// Pagination for lists
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

if ($view === 'issues') {
    $total = (int) db()->query("SELECT COUNT(*) FROM magazine_issues")->fetchColumn();
    $p = paginate($total, $perPage, $page);
    $stmt = db()->prepare("SELECT * FROM magazine_issues ORDER BY issue_year DESC, issue_month DESC LIMIT ? OFFSET ?");
    $stmt->bindValue(1, $p['per_page'], PDO::PARAM_INT);
    $stmt->bindValue(2, $p['offset'], PDO::PARAM_INT);
    $stmt->execute();
    $issuesList = $stmt->fetchAll();
    $articlesList = [];
} else {
    // view = articles
    if ($issueId) {
        $stmt = db()->prepare("SELECT * FROM magazine_issues WHERE id=?");
        $stmt->execute([$issueId]);
        $currentIssue = $stmt->fetch();
    } else {
        $currentIssue = null;
    }

    if ($issueId) {
        $total = (int) db()->prepare("SELECT COUNT(*) FROM magazine_articles WHERE issue_id=?")->execute([$issueId]) ? 0 : 0;
        // fetch directly
        $stmt = db()->prepare("SELECT ma.*, mi.issue_number, mi.issue_year, mi.issue_month
                               FROM magazine_articles ma
                               LEFT JOIN magazine_issues mi ON ma.issue_id=mi.id
                               WHERE ma.issue_id=?
                               ORDER BY ma.page_number ASC, ma.id ASC");
        $stmt->execute([$issueId]);
        $articlesList = $stmt->fetchAll();
        $total = count($articlesList);
        $p = paginate($total, $perPage, $page);
    } else {
        $total = (int) db()->query("SELECT COUNT(*) FROM magazine_articles")->fetchColumn();
        $p = paginate($total, $perPage, $page);
        $stmt = db()->prepare("SELECT ma.*, mi.issue_number, mi.issue_year, mi.issue_month
                               FROM magazine_articles ma
                               LEFT JOIN magazine_issues mi ON ma.issue_id=mi.id
                               ORDER BY mi.issue_year DESC, mi.issue_month DESC, ma.page_number ASC
                               LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $p['per_page'], PDO::PARAM_INT);
        $stmt->bindValue(2, $p['offset'], PDO::PARAM_INT);
        $stmt->execute();
        $articlesList = $stmt->fetchAll();
    }
    $issuesList = [];
}

$flash = mag_take_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Magazine Manager - Admin</title>
<link rel="stylesheet" href="<?= BASE_URL ?>css/style.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/admin.css">
</head>
<body class="admin-body">
<?php require __DIR__ . '/includes/admin-header.php'; ?>
<main class="admin-main">

  <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    <h1 style="margin:0;">Magazine Manager</h1>
    <div>
      <a class="btn btn-sm <?= $view==='issues'?'btn-primary':'btn-outline' ?>" href="?view=issues">Issues</a>
      <a class="btn btn-sm <?= $view==='articles'?'btn-primary':'btn-outline' ?>" href="?view=articles">Articles</a>
      <?php if ($view === 'issues'): ?>
        <a class="btn btn-sm btn-accent" href="?view=issues&action=new">+ New Issue</a>
      <?php else: ?>
        <a class="btn btn-sm btn-accent" href="?view=articles&action=new<?= $issueId ? '&issue='.$issueId : '' ?>">+ New Article</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($flash): ?>
    <?php foreach ($flash as $type => $msg): ?>
      <div class="alert alert-<?= $type === 'success' ? 'success' : 'error' ?>"><?= e($msg) ?></div>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php /* ============================= ISSUES VIEW ============================= */ ?>
  <?php if ($view === 'issues'): ?>

    <?php if ($action === 'new' || $action === 'edit'): ?>
      <?php $isEdit = $action === 'edit' && $editIssue; ?>
      <h2><?= $isEdit ? 'Edit Issue' : 'New Issue' ?></h2>

      <form method="post" enctype="multipart/form-data" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="issue">

        <div class="grid grid-3" style="gap:1rem;">
          <div class="form-group">
            <label>Issue Number *</label>
            <input type="number" name="issue_number" min="1" required
                   value="<?= e($editIssue['issue_number'] ?? ($_POST['issue_number'] ?? '')) ?>">
          </div>
          <div class="form-group">
            <label>Year *</label>
            <input type="number" name="issue_year" min="1900" max="2100" required
                   value="<?= e($editIssue['issue_year'] ?? ($_POST['issue_year'] ?? date('Y'))) ?>">
          </div>
          <div class="form-group">
            <label>Month *</label>
            <select name="issue_month" required>
              <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?= $m ?>" <?= (int)($editIssue['issue_month'] ?? date('n')) === $m ? 'selected' : '' ?>>
                  <?= e(mag_month_name($m)) ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
        </div>

        <div class="grid grid-3" style="gap:1rem;">
          <div class="form-group">
            <label>Title (English)</label>
            <input type="text" name="title_en" value="<?= e($editIssue['title_en'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Title (Urdu)</label>
            <input type="text" name="title_ur" dir="rtl" value="<?= e($editIssue['title_ur'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Title (Arabic)</label>
            <input type="text" name="title_ar" dir="rtl" value="<?= e($editIssue['title_ar'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Description (English)</label>
          <textarea name="description_en" rows="3"><?= e($editIssue['description_en'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Description (Urdu)</label>
          <textarea name="description_ur" rows="3" dir="rtl"><?= e($editIssue['description_ur'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Description (Arabic)</label>
          <textarea name="description_ar" rows="3" dir="rtl"><?= e($editIssue['description_ar'] ?? '') ?></textarea>
        </div>

        <div class="grid grid-2" style="gap:1rem;">
          <div class="form-group">
            <label>Cover Image (JPG/PNG/WebP)</label>
            <input type="file" name="cover_image" accept="image/*">
            <?php if (!empty($editIssue['cover_image'])): ?>
              <div style="margin-top:.5rem;">
                <img src="<?= UPLOAD_URL . e($editIssue['cover_image']) ?>" alt="cover" style="max-height:120px;border-radius:6px;">
                <p style="font-size:.8rem;color:#6b7280;">Current: <?= e($editIssue['cover_image']) ?></p>
              </div>
            <?php endif; ?>
          </div>
          <div class="form-group">
            <label>PDF File (max 8 MB)</label>
            <input type="file" name="pdf_file" accept="application/pdf">
            <?php if (!empty($editIssue['pdf_file'])): ?>
              <p style="font-size:.85rem;margin-top:.5rem;">Current: <a href="<?= UPLOAD_URL . e($editIssue['pdf_file']) ?>" target="_blank"><?= e($editIssue['pdf_file']) ?></a></p>
            <?php endif; ?>
          </div>
        </div>

        <div class="form-group">
          <label><input type="checkbox" name="is_published" value="1" style="width:auto;display:inline-block;"
            <?= !empty($editIssue['is_published']) || !$isEdit ? 'checked' : '' ?>> Published</label>
        </div>

        <div style="display:flex;gap:.5rem;">
          <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update Issue' : 'Create Issue' ?></button>
          <a class="btn btn-outline" href="?view=issues">Cancel</a>
        </div>
      </form>

    <?php else: ?>

      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Cover</th>
            <th>Issue</th>
            <th>Title</th>
            <th>Articles</th>
            <th>PDF</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($issuesList)): ?>
          <tr><td colspan="8" style="text-align:center;color:#6b7280;">No issues yet. Click "New Issue" to add one.</td></tr>
        <?php else: ?>
          <?php foreach ($issuesList as $i): ?>
            <?php
              $stmt = db()->prepare("SELECT COUNT(*) FROM magazine_articles WHERE issue_id=?");
              $stmt->execute([$i['id']]);
              $articleCount = (int)$stmt->fetchColumn();
            ?>
            <tr>
              <td><?= (int)$i['id'] ?></td>
              <td>
                <?php if ($i['cover_image']): ?>
                  <img src="<?= UPLOAD_URL . e($i['cover_image']) ?>" alt="cover" style="width:48px;height:64px;object-fit:cover;border-radius:4px;">
                <?php else: ?>
                  <span style="color:#9ca3af;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <strong>#<?= (int)$i['issue_number'] ?></strong><br>
                <small><?= e(mag_month_name($i['issue_month'])) ?> <?= (int)$i['issue_year'] ?></small>
              </td>
              <td><?= e($i['title_en']) ?></td>
              <td><?= $articleCount ?></td>
              <td>
                <?php if ($i['pdf_file']): ?>
                  <a href="<?= UPLOAD_URL . e($i['pdf_file']) ?>" target="_blank">PDF</a>
                <?php else: ?>
                  <span style="color:#9ca3af;">—</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge badge-<?= $i['is_published'] ? 'published' : 'draft' ?>">
                  <?= $i['is_published'] ? 'Published' : 'Draft' ?>
                </span>
              </td>
              <td>
                <a class="btn btn-sm btn-outline" href="?view=articles&issue=<?= (int)$i['id'] ?>">Articles</a>
                <a class="btn btn-sm btn-outline" href="?view=issues&action=edit&id=<?= (int)$i['id'] ?>">Edit</a>
                <form method="post" action="?view=issues&action=delete&id=<?= (int)$i['id'] ?>" style="display:inline;background:none;padding:0;box-shadow:none;"
                      onsubmit="return confirm('Delete this issue and all its articles?');">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-accent" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>

      <?php if ($p['total_pages'] > 1): ?>
        <div class="pagination">
          <?php for ($n = 1; $n <= $p['total_pages']; $n++): ?>
            <?php if ($n === $p['current']): ?>
              <span class="active"><?= $n ?></span>
            <?php else: ?>
              <a href="?view=issues&page=<?= $n ?>"><?= $n ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  <?php /* ============================= ARTICLES VIEW ============================= */ ?>
  <?php else: ?>

    <div style="display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
      <div>
        <?php if (!empty($currentIssue)): ?>
          <p style="margin:0;">
            Showing articles for:
            <strong>Issue #<?= (int)$currentIssue['issue_number'] ?> — <?= e(mag_month_name($currentIssue['issue_month'])) ?> <?= (int)$currentIssue['issue_year'] ?></strong>
            — <a href="?view=articles">view all</a>
          </p>
        <?php else: ?>
          <p style="margin:0;">Showing all articles across all issues.</p>
        <?php endif; ?>
      </div>
      <form method="get" style="background:none;padding:0;box-shadow:none;display:flex;gap:.5rem;align-items:center;">
        <input type="hidden" name="view" value="articles">
        <select name="issue" onchange="this.form.submit()" style="padding:.4rem .6rem;">
          <option value="0">— All issues —</option>
          <?php foreach ($allIssues as $iss): ?>
            <option value="<?= (int)$iss['id'] ?>" <?= $issueId === (int)$iss['id'] ? 'selected' : '' ?>>
              #<?= (int)$iss['issue_number'] ?> — <?= e(mag_month_name($iss['issue_month'])) ?> <?= (int)$iss['issue_year'] ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <?php if ($action === 'new' || $action === 'edit'): ?>
      <?php $isEdit = $action === 'edit' && $editArticle; ?>
      <h2><?= $isEdit ? 'Edit Article' : 'New Article' ?></h2>

      <form method="post" class="admin-form">
        <?= csrf_field() ?>
        <input type="hidden" name="form" value="article">

        <div class="grid grid-2" style="gap:1rem;">
          <div class="form-group">
            <label>Issue *</label>
            <select name="issue_id" required>
              <option value="">— Select issue —</option>
              <?php foreach ($allIssues as $iss): ?>
                <option value="<?= (int)$iss['id'] ?>"
                  <?= ((int)($editArticle['issue_id'] ?? $issueId)) === (int)$iss['id'] ? 'selected' : '' ?>>
                  #<?= (int)$iss['issue_number'] ?> — <?= e(mag_month_name($iss['issue_month'])) ?> <?= (int)$iss['issue_year'] ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Page Number</label>
            <input type="number" name="page_number" min="1"
                   value="<?= e($editArticle['page_number'] ?? '') ?>">
          </div>
        </div>

        <div class="grid grid-3" style="gap:1rem;">
          <div class="form-group">
            <label>Title (English) *</label>
            <input type="text" name="title_en" required value="<?= e($editArticle['title_en'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Title (Urdu)</label>
            <input type="text" name="title_ur" dir="rtl" value="<?= e($editArticle['title_ur'] ?? '') ?>">
          </div>
          <div class="form-group">
            <label>Title (Arabic)</label>
            <input type="text" name="title_ar" dir="rtl" value="<?= e($editArticle['title_ar'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label>Author Name</label>
          <input type="text" name="author_name" value="<?= e($editArticle['author_name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Content (English)</label>
          <textarea name="content_en" rows="8"><?= e($editArticle['content_en'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Content (Urdu)</label>
          <textarea name="content_ur" rows="8" dir="rtl"><?= e($editArticle['content_ur'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Content (Arabic)</label>
          <textarea name="content_ar" rows="8" dir="rtl"><?= e($editArticle['content_ar'] ?? '') ?></textarea>
        </div>

        <div style="display:flex;gap:.5rem;">
          <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update Article' : 'Create Article' ?></button>
          <a class="btn btn-outline" href="?view=articles<?= $issueId ? '&issue='.$issueId : '' ?>">Cancel</a>
        </div>
      </form>

    <?php else: ?>

      <table class="admin-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Issue</th>
            <th>Page</th>
            <th>Title (EN)</th>
            <th>Author</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($articlesList)): ?>
          <tr><td colspan="6" style="text-align:center;color:#6b7280;">No articles found.</td></tr>
        <?php else: ?>
          <?php foreach ($articlesList as $a): ?>
            <tr>
              <td><?= (int)$a['id'] ?></td>
              <td>
                <?php if (!empty($a['issue_number'])): ?>
                  #<?= (int)$a['issue_number'] ?> — <?= e(mag_month_name($a['issue_month'])) ?> <?= (int)$a['issue_year'] ?>
                <?php else: ?>
                  <em style="color:#9ca3af;">orphan</em>
                <?php endif; ?>
              </td>
              <td><?= $a['page_number'] ? (int)$a['page_number'] : '—' ?></td>
              <td><?= e($a['title_en']) ?></td>
              <td><?= e($a['author_name']) ?></td>
              <td>
                <a class="btn btn-sm btn-outline" href="?view=articles&action=edit&id=<?= (int)$a['id'] ?>">Edit</a>
                <form method="post" action="?view=articles&action=delete&id=<?= (int)$a['id'] ?>&issue=<?= (int)$a['issue_id'] ?>" style="display:inline;background:none;padding:0;box-shadow:none;"
                      onsubmit="return confirm('Delete this article?');">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-accent" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>

      <?php if (!empty($p) && $p['total_pages'] > 1): ?>
        <div class="pagination">
          <?php for ($n = 1; $n <= $p['total_pages']; $n++): ?>
            <?php if ($n === $p['current']): ?>
              <span class="active"><?= $n ?></span>
            <?php else: ?>
              <a href="?view=articles&page=<?= $n ?><?= $issueId ? '&issue='.$issueId : '' ?>"><?= $n ?></a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  <?php endif; ?>

</main>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
