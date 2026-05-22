<?php
require_once 'auth.php';
requireAuth();

$news   = getNews();
$action = $_GET['action'] ?? 'list';
$msg    = '';

// DELETE
if ($action === 'delete' && isset($_GET['id'])) {
    $id   = (int)$_GET['id'];
    $news = array_filter($news, fn($n) => $n['id'] !== $id);
    saveNews($news);
    header('Location: news.php?msg=deleted'); exit;
}

// SAVE (add or edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $item = [
        'id'       => $id ?: (max(array_column($news,'id') ?: [0]) + 1),
        'title'    => trim($_POST['title']),
        'category' => trim($_POST['category']),
        'emoji'    => trim($_POST['emoji']),
        'content'  => trim($_POST['content']),
        'date'     => trim($_POST['date']),
        'image'    => trim($_POST['image']),
    ];
    if ($id) {
        $news = array_map(fn($n) => $n['id'] === $id ? $item : $n, $news);
    } else {
        $news[] = $item;
    }
    saveNews($news);
    header('Location: news.php?msg=saved'); exit;
}

// Edit: load existing
$editing = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $eid = (int)$_GET['id'];
    foreach ($news as $n) { if ($n['id'] === $eid) { $editing = $n; break; } }
}

if (isset($_GET['msg'])) $msg = $_GET['msg'] === 'saved' ? '✅ Actualité enregistrée.' : '🗑️ Actualité supprimée.';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>IBCE-RC — Actualités</title>
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
  .btn-secondary{background:#e5e7eb;color:#374151;} .btn-secondary:hover{background:#d1d5db;}
  .btn-danger{background:#fee2e2;color:#dc2626;} .btn-danger:hover{background:#fecaca;}
  .section{background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,.06);margin-bottom:1.5rem;}
  .section-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;}
  .section-head h2{font-size:1.05rem;color:#1b4332;font-weight:600;}
  table{width:100%;border-collapse:collapse;}
  th{text-align:left;padding:.75rem 1rem;font-size:.78rem;font-weight:600;color:#6b7280;text-transform:uppercase;border-bottom:2px solid #f3f4f6;}
  td{padding:.75rem 1rem;font-size:.88rem;border-bottom:1px solid #f9fafb;vertical-align:middle;}
  tr:last-child td{border-bottom:none;}
  .badge{display:inline-block;padding:.2rem .65rem;border-radius:20px;font-size:.75rem;font-weight:600;background:#d1fae5;color:#065f46;}
  .actions a{margin-right:.5rem;font-size:.82rem;font-weight:600;text-decoration:none;}
  .edit{color:#2d6a4f;} .delete{color:#dc2626;}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.2rem;}
  .form-group{display:flex;flex-direction:column;gap:.4rem;}
  .form-group.full{grid-column:1/-1;}
  label{font-size:.85rem;font-weight:600;color:#374151;}
  input,select,textarea{padding:.75rem 1rem;border:2px solid #e5e7eb;border-radius:8px;font-size:.9rem;outline:none;font-family:inherit;transition:border .2s;width:100%;}
  input:focus,select:focus,textarea:focus{border-color:#40916c;}
  textarea{min-height:100px;resize:vertical;}
  .msg{padding:.85rem 1.2rem;border-radius:8px;margin-bottom:1.5rem;font-size:.9rem;font-weight:500;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;}
  .form-actions{display:flex;gap:1rem;margin-top:1.5rem;}
  .empty{text-align:center;padding:3rem;color:#9ca3af;font-size:.95rem;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="logo">
    <img src="../images/l.png" alt="IBCE-RC" onerror="this.style.display='none'">
    <span>IBCE-RC</span>
  </div>
  <a href="index.php"  class="nav-item"><span>🏠</span> Tableau de bord</a>
  <a href="news.php"   class="nav-item active"><span>📰</span> Actualités</a>
  <a href="stats.php"  class="nav-item"><span>📊</span> Statistiques</a>
  <a href="../index.html" class="nav-item" target="_blank"><span>🌐</span> Voir le site</a>
  <div class="logout"><a href="logout.php">🚪 Déconnexion</a></div>
</div>

<div class="main">
  <div class="topbar">
    <div>
      <h1><?= ($action==='add')?'Nouvelle actualité':(($action==='edit')?'Modifier l\'actualité':'Gestion des actualités') ?></h1>
    </div>
    <?php if($action==='list'): ?>
      <a href="news.php?action=add" class="btn btn-primary">+ Nouvelle actualité</a>
    <?php else: ?>
      <a href="news.php" class="btn btn-secondary">← Retour à la liste</a>
    <?php endif; ?>
  </div>

  <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>

  <?php if($action==='list'): ?>
  <div class="section">
    <div class="section-head"><h2>📰 Toutes les actualités (<?= count($news) ?>)</h2></div>
    <?php if(empty($news)): ?>
      <div class="empty">Aucune actualité. <a href="news.php?action=add">Créer la première →</a></div>
    <?php else: ?>
    <table>
      <thead><tr><th>Titre</th><th>Catégorie</th><th>Date</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach($news as $n): ?>
      <tr>
        <td><?= htmlspecialchars($n['title']) ?></td>
        <td><span class="badge"><?= htmlspecialchars($n['emoji'].' '.$n['category']) ?></span></td>
        <td><?= htmlspecialchars($n['date']) ?></td>
        <td class="actions">
          <a href="news.php?action=edit&id=<?= $n['id'] ?>" class="edit">✏️ Modifier</a>
          <a href="news.php?action=delete&id=<?= $n['id'] ?>" class="delete" onclick="return confirm('Supprimer cette actualité ?')">🗑️ Supprimer</a>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php else: ?>
  <div class="section">
    <form method="POST">
      <?php if($editing): ?><input type="hidden" name="id" value="<?= $editing['id'] ?>"><?php endif; ?>
      <div class="form-grid">
        <div class="form-group full">
          <label>Titre de l'actualité *</label>
          <input type="text" name="title" required placeholder="Ex: Distribution de plants aux agriculteurs" value="<?= htmlspecialchars($editing['title'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Catégorie *</label>
          <input type="text" name="category" required placeholder="Ex: Reboisement" value="<?= htmlspecialchars($editing['category'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Emoji</label>
          <input type="text" name="emoji" placeholder="🌱" value="<?= htmlspecialchars($editing['emoji'] ?? '🌿') ?>">
        </div>
        <div class="form-group full">
          <label>Contenu *</label>
          <textarea name="content" required placeholder="Texte de l'actualité..."><?= htmlspecialchars($editing['content'] ?? '') ?></textarea>
        </div>
        <div class="form-group">
          <label>Date</label>
          <input type="text" name="date" placeholder="Ex: Mai 2026" value="<?= htmlspecialchars($editing['date'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label>Image (chemin)</label>
          <input type="text" name="image" placeholder="images/mon-image.jpg" value="<?= htmlspecialchars($editing['image'] ?? '') ?>">
        </div>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        <a href="news.php" class="btn btn-secondary">Annuler</a>
      </div>
    </form>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
