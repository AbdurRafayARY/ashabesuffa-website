<?php
require_once __DIR__ . '/../config/config.php';
header('Content-Type: application/json; charset=utf-8');

$q = clean_input($_GET['q'] ?? '');
if (mb_strlen($q) < 2) { echo json_encode(['results' => []]); exit; }

$like = '%' . $q . '%';
$stmt = db()->prepare("SELECT id, slug, title_en, title_ur, title_ar, 'news' AS type FROM news WHERE status='published' AND (title_en LIKE ? OR title_ur LIKE ? OR title_ar LIKE ?) LIMIT 10");
$stmt->execute([$like,$like,$like]);
$results = $stmt->fetchAll();

echo json_encode(['results' => $results, 'query' => $q]);
