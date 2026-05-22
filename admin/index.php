<?php
require_once 'auth.php';
requireAuth();
$news  = getNews();
$stats = getStats();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IBCE-RC — Tableau de Bord</title>
<style>
  *{margin:0;padding:0;box-sizing:border-box;}
  body{font-family:'Segoe UI',sans-serif;background:#f3f4f6;color:#111827;}
  .sidebar{position:fixed;top:0;left:0;height:100vh;width:230px;background:linear-gradient(180deg,#1b4332,#2d6a4f);padding:1.5rem 1rem;z-index:100;}
  .sidebar .logo{display:flex;align-items:center;gap:.75rem;padding:.5rem;margin-bottom:2rem;}
  .sidebar .logo img{width:44px;height:44px;border-radius:50%;object-fit:contain;background:#fff;}
  .sidebar .logo span{color:#fff;font-weight:700;font-size:1rem;line-height:1.2;}
  .nav-item{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:8px;color:rgba(255,255,255,.75);text-decoration:none;font-size:.9rem;font-weight:500;transition:all .2s;margin-bottom:.25rem;}
  .nav-item:hover,.nav-item.active{background:rgba(255,255,255,.15);color:#fff;}
  .nav-item .ico{font-size:1.1rem;}
  .logout{margin-top:auto;position:absolute;bottom:1.5rem;left:1rem;right:1rem;}
  .logout a{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;border-radius:8px;color:rgba(255,255,255,.6);text-decoration:none;font-size:.88rem;transition:all .2s;}
  .logout a:hover{background:rgba(255,0,0,.2);color:#fca5a5;}
  .main{margin-left:230px;padding:2rem;}
  .topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;}
  .topbar h1{font-size:1.5rem;color:#1b4332;}
  .topbar p{font-size:.85rem;color:#6b7280;margin-top:.2rem;}
  .btn-primary{background:#2d6a4f;color:#fff;padding:.6rem 1.2rem;border-radius:8px;text-decoration:none;font-size:.88rem;font-weight:600;border:none;cursor:pointer;transition:background .2s;}
  .btn-primary:hover{background:#1b4332;}
  .cards{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-bottom:2.5rem;}
  .stat-card{background:#fff;border-radius:12px;padding:1.5rem;box-shadow:0 1px 4px rgba(0,0,0,.06);border-left:4px solid #40916c;}
  .stat-card .num{font-size:2rem;font-weight:700;color:#1b4332;}
  .stat-card .lbl{font-size:.8rem;color:#6b7280;margin-top:.3rem;}
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
  .actions a:hover{text-decoration:underline;}
  .shortcut{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;}
  .shortcut a{background:#f0fdf4;border:2px solid #bbf7d0;border-radius:10px;padding:1.2rem;text-align:center;text-decoration:none;color:#1b4332;font-weight:600;font-size:.88rem;transition:all .2s;}
  .shortcut a:hover{background:#dcfce7;border-color:#86efac;}
  .shortcut a .big{font-size:1.8rem;display:block;margin-bottom:.4rem;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="logo">
    <img src="../images/l.png" alt="IBCE-RC" onerror="this.style.display='none'">
    <span>IBCE-RC<br><small style="font-weight:400;font-size:.75rem;opacity:.7;">Administration</small></span>
  </div>
  <a href="index.php" class="nav-item active"><span class="ico">🏠</span> Tableau de bord</a>
  <a href="news.php" class="nav-item"><span class="ico">📰</span> Actualités</a>
  <a href="stats.php" class="nav-item"><span class="ico">📊</span> Statistiques</a>
  <a href="../index.html" class="nav-item" target="_blank"><span class="ico">🌐</span> Voir le site</a>
  <div class="logout"><a href="logout.php"><span>🚪</span> Déconnexion</a></div>
</div>

<div class="main">
  <div class="topbar">
    <div>
      <h1>Tableau de bord</h1>
      <p>Bienvenue dans l'espace d'administration IBCE-RC</p>
    </div>
    <a href="news.php?action=add" class="btn-primary">+ Nouvelle actualité</a>
  </div>

  <div class="cards">
    <div class="stat-card">
      <div class="num"><?= number_format($stats['hectares'],0,',',' ') ?>+</div>
      <div class="lbl">Hectares protégés</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= number_format($stats['arbres'],0,',',' ') ?>+</div>
      <div class="lbl">Arbres plantés</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $stats['especes'] ?>+</div>
      <div class="lbl">Espèces documentées</div>
    </div>
    <div class="stat-card">
      <div class="num"><?= $stats['familles'] ?>+</div>
      <div class="lbl">Familles soutenues</div>
    </div>
  </div>

  <div class="section">
    <div class="section-head">
      <h2>📰 Dernières actualités (<?= count($news) ?>)</h2>
      <a href="news.php?action=add" class="btn-primary">+ Ajouter</a>
    </div>
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
  </div>

  <div class="section">
    <div class="section-head"><h2>⚡ Accès rapide</h2></div>
    <div class="shortcut">
      <a href="news.php?action=add"><span class="big">📰</span>Ajouter une actualité</a>
      <a href="stats.php"><span class="big">📊</span>Modifier les statistiques</a>
      <a href="../index.html" target="_blank"><span class="big">🌐</span>Voir le site public</a>
    </div>
  </div>
</div>
</body>
</html>
