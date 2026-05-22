<?php
require_once 'auth.php';
requireAuth();
$stats = getStats();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    saveStats([
        'hectares' => (int)$_POST['hectares'],
        'arbres'   => (int)$_POST['arbres'],
        'especes'  => (int)$_POST['especes'],
        'familles' => (int)$_POST['familles'],
    ]);
    $msg = '✅ Statistiques mises à jour.';
    $stats = getStats();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>IBCE-RC — Statistiques</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Segoe UI',sans-serif;background:#f3f4f6;color:#111827;}
  .sidebar{position:fixed;top:0;left:0;height:100vh;width:230px;background:linear-gradient(180deg,#1b4332,#2d6a4f);padding:1.5rem 1rem;}
  .sidebar .logo{display:flex;align-items:center;gap:.75rem;margin-bottom:2rem;}
  .sidebar .logo img{width:44px;height:44px;border-radius:50%;object-fit:contain;background:#fff;}
  .sidebar .logo span{color:#fff;font-weight:700;font-size:1rem;}
  .nav-item{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:8px;color:rgba(255,255,255,.75);text-decoration:none;font-size:.9rem;font-weight:500;transition:all .2s;margin-bottom:.25rem;}
  .nav-item:hover,.nav-item.active{background:rgba(255,255,255,.15);color:#fff;}
  .logout{position:absolute;bottom:1.5rem;left:1rem;right:1rem;}
  .logout a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:8px;color:rgba(255,255,255,.6);text-decoration:none;font-size:.88rem;}
  .logout a:hover{background:rgba(255,0,0,.2);color:#fca5a5;}
  .main{margin-left:230px;padding:2rem;}
  h1{font-size:1.5rem;color:#1b4332;margin-bottom:2rem;}
  .section{background:#fff;border-radius:12px;padding:2rem;box-shadow:0 1px 4px rgba(0,0,0,.06);max-width:600px;}
  .msg{padding:.85rem 1.2rem;border-radius:8px;margin-bottom:1.5rem;font-size:.9rem;font-weight:500;background:#d1fae5;color:#065f46;border:1px solid #a7f3d0;}
  .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;}
  .form-group{display:flex;flex-direction:column;gap:.5rem;}
  label{font-size:.85rem;font-weight:600;color:#374151;}
  .stat-label{font-size:.78rem;color:#6b7280;margin-top:.2rem;}
  input[type=number]{padding:.75rem 1rem;border:2px solid #e5e7eb;border-radius:8px;font-size:1.1rem;font-weight:600;color:#1b4332;outline:none;transition:border .2s;width:100%;}
  input[type=number]:focus{border-color:#40916c;}
  .btn{padding:.75rem 2rem;border-radius:8px;font-size:.95rem;font-weight:600;border:none;cursor:pointer;background:#2d6a4f;color:#fff;margin-top:1.5rem;transition:background .2s;}
  .btn:hover{background:#1b4332;}
  .note{font-size:.82rem;color:#6b7280;margin-top:1rem;padding:1rem;background:#f9fafb;border-radius:8px;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="logo">
    <img src="../images/l.png" alt="IBCE-RC" onerror="this.style.display='none'">
    <span>IBCE-RC</span>
  </div>
  <a href="index.php"  class="nav-item"><span>🏠</span> Tableau de bord</a>
  <a href="news.php"   class="nav-item"><span>📰</span> Actualités</a>
  <a href="stats.php"  class="nav-item active"><span>📊</span> Statistiques</a>
  <a href="../index.html" class="nav-item" target="_blank"><span>🌐</span> Voir le site</a>
  <div class="logout"><a href="logout.php">🚪 Déconnexion</a></div>
</div>
<div class="main">
  <h1>📊 Modifier les statistiques</h1>
  <?php if($msg): ?><div class="msg"><?= $msg ?></div><?php endif; ?>
  <div class="section">
    <form method="POST">
      <div class="form-grid">
        <div class="form-group">
          <label>🌿 Hectares protégés</label>
          <input type="number" name="hectares" value="<?= $stats['hectares'] ?>" min="0" required>
          <span class="stat-label">Affiché sur le site : <?= number_format($stats['hectares'],0,',',' ') ?>+ ha</span>
        </div>
        <div class="form-group">
          <label>🌳 Arbres plantés</label>
          <input type="number" name="arbres" value="<?= $stats['arbres'] ?>" min="0" required>
          <span class="stat-label">Affiché sur le site : <?= number_format($stats['arbres'],0,',',' ') ?>+ arbres</span>
        </div>
        <div class="form-group">
          <label>🦋 Espèces documentées</label>
          <input type="number" name="especes" value="<?= $stats['especes'] ?>" min="0" required>
          <span class="stat-label">Affiché sur le site : <?= $stats['especes'] ?>+ espèces</span>
        </div>
        <div class="form-group">
          <label>👨‍👩‍👧 Familles soutenues</label>
          <input type="number" name="familles" value="<?= $stats['familles'] ?>" min="0" required>
          <span class="stat-label">Affiché sur le site : <?= $stats['familles'] ?>+ familles</span>
        </div>
      </div>
      <button type="submit" class="btn">💾 Enregistrer les statistiques</button>
    </form>
    <div class="note">ℹ️ Les modifications sont appliquées immédiatement sur le site public.</div>
  </div>
</div>
</body>
</html>
