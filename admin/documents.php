<?php
require_once 'auth.php';
requireAuth();

const MAX_DOCUMENT_SIZE = 20971520;

$documentsFile = DATA_DIR . 'documents.json';
$documentsDirectory = __DIR__ . '/../documents/';
$error = '';
$success = '';

function loadDocuments($file) {
    if (!file_exists($file)) return [];

    $documents = json_decode(file_get_contents($file), true);
    if (!is_array($documents)) {
        throw new RuntimeException('Le fichier des documents est illisible.');
    }

    return $documents;
}

function saveDocuments($file, $documents) {
    $json = json_encode(array_values($documents), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if ($json === false) {
        throw new RuntimeException('Impossible de préparer la liste des documents.');
    }

    $temporaryFile = tempnam(DATA_DIR, '.documents-');
    if ($temporaryFile === false) {
        throw new RuntimeException('Impossible de créer le fichier temporaire des documents.');
    }

    if (file_put_contents($temporaryFile, $json, LOCK_EX) === false || !rename($temporaryFile, $file)) {
        if (file_exists($temporaryFile)) unlink($temporaryFile);
        throw new RuntimeException('Impossible d’enregistrer la liste des documents.');
    }
}

function escapeHtml($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

try {
    $documents = loadDocuments($documentsFile);
} catch (RuntimeException $exception) {
    $documents = [];
    $error = $exception->getMessage();
}

if (empty($_SESSION['documents_csrf'])) {
    $_SESSION['documents_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['documents_csrf'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        $error = 'La demande a expiré. Rechargez la page puis réessayez.';
    } elseif (!$error && ($_POST['action'] ?? '') === 'delete') {
        $id = $_POST['id'] ?? '';
        $index = null;
        foreach ($documents as $key => $document) {
            if (($document['id'] ?? '') === $id) {
                $index = $key;
                break;
            }
        }

        if ($index === null) {
            $error = 'Ce document est introuvable.';
        } else {
            $removedDocument = $documents[$index];
            unset($documents[$index]);
            try {
                saveDocuments($documentsFile, $documents);
                if (preg_match('/^documents\/[a-f0-9]{32}\.xlsx$/', $removedDocument['path'] ?? '')) {
                    $storedFile = $documentsDirectory . basename($removedDocument['path']);
                    if (file_exists($storedFile) && !unlink($storedFile)) {
                        $error = 'Le document a été retiré du site, mais son fichier n’a pas pu être supprimé du serveur.';
                    }
                }
                if (!$error) {
                    header('Location: documents.php?msg=deleted');
                    exit;
                }
            } catch (RuntimeException $exception) {
                $error = $exception->getMessage();
            }
        }
    } elseif (!$error && ($_POST['action'] ?? '') === 'upload') {
        $upload = $_FILES['document'] ?? null;
        if (!$upload || $upload['error'] !== UPLOAD_ERR_OK) {
            $uploadError = $upload['error'] ?? UPLOAD_ERR_NO_FILE;
            if ($uploadError === UPLOAD_ERR_INI_SIZE || $uploadError === UPLOAD_ERR_FORM_SIZE) {
                $error = 'Le fichier dépasse la taille maximale autorisée par le serveur.';
            } elseif ($uploadError === UPLOAD_ERR_NO_FILE) {
                $error = 'Choisissez un fichier Excel à téléverser.';
            } else {
                $error = 'Le téléversement a échoué. Réessayez.';
            }
        } elseif ($upload['size'] > MAX_DOCUMENT_SIZE) {
            $error = 'Le fichier ne doit pas dépasser 20 Mo.';
        } elseif (strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION)) !== 'xlsx') {
            $error = 'Seuls les fichiers Excel au format .xlsx sont acceptés.';
        } else {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->file($upload['tmp_name']);
            $signature = file_get_contents($upload['tmp_name'], false, null, 0, 4);
            $allowedMimeTypes = [
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/zip',
                'application/octet-stream',
            ];

            if (!in_array($mimeType, $allowedMimeTypes, true) || $signature !== "PK\x03\x04") {
                $error = 'Le fichier sélectionné n’est pas un classeur Excel .xlsx valide.';
            } elseif (!is_dir($documentsDirectory) && !mkdir($documentsDirectory, 0755, true) && !is_dir($documentsDirectory)) {
                $error = 'Le dossier de stockage des documents ne peut pas être créé.';
            } else {
                $storedName = bin2hex(random_bytes(16)) . '.xlsx';
                $storedPath = $documentsDirectory . $storedName;
                $relativePath = 'documents/' . $storedName;

                if (!move_uploaded_file($upload['tmp_name'], $storedPath)) {
                    $error = 'Le fichier n’a pas pu être enregistré sur le serveur.';
                } else {
                    $documents[] = [
                        'id' => bin2hex(random_bytes(16)),
                        'name' => basename($upload['name']),
                        'path' => $relativePath,
                        'size' => (int)$upload['size'],
                    ];

                    try {
                        saveDocuments($documentsFile, $documents);
                        header('Location: documents.php?msg=uploaded');
                        exit;
                    } catch (RuntimeException $exception) {
                        unlink($storedPath);
                        array_pop($documents);
                        $error = $exception->getMessage();
                    }
                }
            }
        }
    }
}

if (isset($_GET['msg'])) {
    $success = $_GET['msg'] === 'uploaded'
        ? '✅ Document téléversé et publié sur le site.'
        : ($_GET['msg'] === 'deleted' ? '🗑️ Document retiré du site.' : '');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>IBCE-RC — Documents</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Segoe UI',sans-serif;background:#f3f4f6;color:#111827;}
  .sidebar{position:fixed;top:0;left:0;height:100vh;width:230px;background:linear-gradient(180deg,#1b4332,#2d6a4f);padding:1.5rem 1rem;z-index:100;}
  .sidebar .logo{display:flex;align-items:center;gap:.75rem;padding:.5rem;margin-bottom:2rem;}
  .sidebar .logo img{width:44px;height:44px;border-radius:50%;object-fit:contain;background:#fff;}
  .sidebar .logo span{color:#fff;font-weight:700;font-size:1rem;}
  .nav-item{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:8px;color:rgba(255,255,255,.75);text-decoration:none;font-size:.9rem;font-weight:500;transition:all .2s;margin-bottom:.25rem;}
  .nav-item:hover,.nav-item.active{background:rgba(255,255,255,.15);color:#fff;}
  .logout{position:absolute;bottom:1.5rem;left:1rem;right:1rem;}
  .logout a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:8px;color:rgba(255,255,255,.6);text-decoration:none;font-size:.88rem;}
  .logout a:hover{background:rgba(255,0,0,.2);color:#fca5a5;}
  .main{margin-left:230px;padding:2rem;}
  .topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;}
  .topbar h1{font-size:1.5rem;color:#1b4332;}
  .btn{padding:.6rem 1.2rem;border-radius:8px;font-size:.88rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-block;transition:background .2s;}
  .btn-primary{background:#2d6a4f;color:#fff;} .btn-primary:hover{background:#1b4332;}
  .btn-danger{background:#fee2e2;color:#dc2626;}
  .section{background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;}
  .section h2{font-size:1.05rem;color:#1b4332;margin-bottom:1rem;}
  .hint{font-size:.85rem;color:#6b7280;margin-top:.5rem;}
  .upload-form{display:flex;align-items:center;gap:1rem;flex-wrap:wrap;}
  input[type=file]{max-width:100%;padding:.5rem;border:1px solid #d1d5db;border-radius:8px;}
  .msg{padding:.85rem 1.2rem;border-radius:8px;margin-bottom:1.5rem;font-size:.9rem;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;}
  .error{background:#fee2e2;color:#991b1b;border-color:#fecaca;}
  .document{display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:1rem 0;border-bottom:1px solid #f3f4f6;}
  .document:last-child{border-bottom:0;}
  .document-name{font-weight:600;overflow-wrap:anywhere;}
  .document-meta{font-size:.8rem;color:#6b7280;margin-top:.25rem;}
  .empty{text-align:center;padding:2rem;color:#6b7280;}
  .document form{flex-shrink:0;}
  @media(max-width:760px){.sidebar{position:relative;width:auto;height:auto;padding:.75rem;}.sidebar .logo{margin-bottom:.5rem;}.logout{position:static;margin-top:.5rem;}.main{margin:0;padding:1rem;}.topbar{align-items:flex-start;}}
</style>
</head>
<body>
<div class="sidebar">
  <div class="logo">
    <img src="../images/l.png" alt="IBCE-RC" onerror="this.style.display='none'">
    <span>IBCE-RC</span>
  </div>
  <a href="index.php" class="nav-item"><span>🏠</span> Tableau de bord</a>
  <a href="news.php" class="nav-item"><span>📰</span> Actualités</a>
  <a href="documents.php" class="nav-item active"><span>📄</span> Documents</a>
  <a href="stats.php" class="nav-item"><span>📊</span> Statistiques</a>
  <a href="../documents.html" class="nav-item" target="_blank">🌐 Documents publics</a>
  <div class="logout"><a href="logout.php">🚪 Déconnexion</a></div>
</div>

<main class="main">
  <div class="topbar"><h1>Gestion des documents</h1></div>
  <?php if ($success): ?><div class="msg"><?= escapeHtml($success) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="msg error" role="alert"><?= escapeHtml($error) ?></div><?php endif; ?>

  <section class="section">
    <h2>Ajouter un fichier Excel</h2>
    <form class="upload-form" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['documents_csrf']) ?>">
      <input type="hidden" name="action" value="upload">
      <input type="hidden" name="MAX_FILE_SIZE" value="<?= MAX_DOCUMENT_SIZE ?>">
      <input type="file" name="document" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
      <button type="submit" class="btn btn-primary">Téléverser et publier</button>
    </form>
    <p class="hint">Format accepté : Excel .xlsx. Taille maximale : 20 Mo. Les fichiers publiés sont téléchargeables par tous les visiteurs.</p>
  </section>

  <section class="section">
    <h2>Documents publiés (<?= count($documents) ?>)</h2>
    <?php if (!$documents): ?>
      <p class="empty">Aucun document publié pour le moment.</p>
    <?php else: ?>
      <?php foreach ($documents as $document): ?>
        <div class="document">
          <div>
            <div class="document-name"><?= escapeHtml($document['name'] ?? '') ?></div>
            <div class="document-meta"><?= number_format((int)($document['size'] ?? 0) / 1024, 0, ',', ' ') ?> Ko</div>
          </div>
          <form method="POST" onsubmit="return confirm('Retirer ce document du site ?')">
            <input type="hidden" name="csrf_token" value="<?= escapeHtml($_SESSION['documents_csrf']) ?>">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= escapeHtml($document['id'] ?? '') ?>">
            <button type="submit" class="btn btn-danger">Retirer</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
