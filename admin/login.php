<?php
session_start();
if (!empty($_SESSION['ibce_admin'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'auth.php';
    if ($_POST['username'] === ADMIN_USER && $_POST['password'] === ADMIN_PASS) {
        $_SESSION['ibce_admin'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Identifiant ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>IBCE-RC — Administration</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Segoe UI',sans-serif; background:linear-gradient(135deg,#1b4332,#40916c); min-height:100vh; display:flex; align-items:center; justify-content:center; }
  .card { background:#fff; border-radius:16px; padding:2.5rem; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,.3); }
  .logo { text-align:center; margin-bottom:2rem; }
  .logo img { width:90px; height:90px; border-radius:50%; object-fit:contain; }
  .logo h1 { font-size:1.4rem; color:#1b4332; margin-top:.75rem; font-weight:700; }
  .logo p { font-size:.78rem; color:#6b7280; margin-top:.2rem; }
  label { display:block; font-size:.85rem; font-weight:600; color:#374151; margin-bottom:.4rem; }
  input { width:100%; padding:.75rem 1rem; border:2px solid #e5e7eb; border-radius:8px; font-size:.95rem; outline:none; transition:border .2s; margin-bottom:1.2rem; }
  input:focus { border-color:#40916c; }
  .btn { width:100%; padding:.85rem; background:#2d6a4f; color:#fff; border:none; border-radius:8px; font-size:1rem; font-weight:600; cursor:pointer; transition:background .2s; }
  .btn:hover { background:#1b4332; }
  .error { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; border-radius:8px; padding:.75rem 1rem; font-size:.88rem; margin-bottom:1.2rem; }
  .back { text-align:center; margin-top:1.5rem; font-size:.82rem; color:#6b7280; }
  .back a { color:#40916c; font-weight:600; text-decoration:none; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <img src="../images/l.png" alt="IBCE-RC" onerror="this.style.display='none'">
    <h1>IBCE-RC</h1>
    <p>Panneau d'administration</p>
  </div>
  <?php if ($error): ?>
    <div class="error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <form method="POST">
    <label>Nom d'utilisateur</label>
    <input type="text" name="username" placeholder="admin" required autofocus>
    <label>Mot de passe</label>
    <input type="password" name="password" placeholder="••••••••" required>
    <button class="btn" type="submit">Se connecter →</button>
  </form>
  <div class="back"><a href="../index.html">← Retour au site</a></div>
</div>
</body>
</html>
