<?php
// Wspólny wygląd stron panelu.

function admin_head(string $title, ?string $admin = null): void
{
    header('X-Frame-Options: DENY');
    header('Cache-Control: no-store');
    ?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · panel</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo:wght@700;800;900&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
<style>
  :root{--bg:#F4F2EC;--card:#fff;--ink:#14213D;--ink-2:#4B5568;--ink-3:#8A93A3;--line:rgba(20,33,61,.12);
    --accent:#1F3A8A;--hi:#FF8A3D;--ok:#067647;--ok-bg:#ECFDF3;--err:#B42318;--err-bg:#FEF3F2}
  *{box-sizing:border-box;margin:0;padding:0}
  body{background:var(--bg);color:var(--ink);font-family:'Source Sans 3',system-ui,sans-serif;font-size:16px;line-height:1.5}
  a{color:var(--accent)}
  h1,h2,h3{font-family:'Archivo',sans-serif;letter-spacing:-.02em}
  .top{background:var(--accent);color:#fff;position:sticky;top:0;z-index:20}
  .top-in{max-width:1040px;margin:0 auto;padding:12px 20px;display:flex;align-items:center;gap:16px;flex-wrap:wrap}
  .top .logo{font-family:'Archivo',sans-serif;font-weight:900;font-size:18px}
  .top .logo span{color:var(--hi)}
  .top nav{display:flex;gap:4px;flex-wrap:wrap}
  .top nav a{color:#fff;text-decoration:none;font-weight:700;font-size:14px;padding:6px 12px;border-radius:6px}
  .top nav a.on,.top nav a:hover{background:rgba(255,255,255,.14)}
  .top .who{margin-left:auto;font-size:13px;opacity:.85;display:flex;gap:12px;align-items:center}
  .top .who a{color:#fff}
  .wrap{max-width:1040px;margin:0 auto;padding:28px 20px 80px}
  .card{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:24px;margin-bottom:18px}
  .card h2{font-size:20px;margin-bottom:4px}
  .card .hint{font-size:14px;color:var(--ink-3);margin-bottom:16px}
  .field{margin-top:14px}
  .field > label,.field > .lbl{display:block;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:var(--ink-2);margin-bottom:6px}
  input[type=text],input[type=email],input[type=url],textarea,select{width:100%;font:inherit;font-size:15px;padding:10px 12px;border:1.5px solid var(--line);border-radius:8px;background:#fff;color:var(--ink)}
  input:focus,textarea:focus,select:focus{outline:none;border-color:var(--accent)}
  textarea{min-height:76px;resize:vertical}
  .grid2{display:grid;grid-template-columns:1fr;gap:0 16px}
  @media (min-width:720px){.grid2{grid-template-columns:1fr 1fr}}
  .btn{display:inline-flex;align-items:center;gap:8px;font-family:'Archivo',sans-serif;font-weight:800;font-size:14px;padding:11px 18px;border-radius:8px;border:0;cursor:pointer;text-decoration:none;background:var(--accent);color:#fff}
  .btn:hover{opacity:.9}
  .btn.ghost{background:#fff;color:var(--ink);border:1.5px solid var(--line)}
  .btn.small{padding:6px 10px;font-size:12.5px}
  .btn.danger{background:#fff;color:var(--err);border:1.5px solid #FECDCA}
  .btn.google{background:#fff;color:var(--ink);border:1.5px solid var(--line);width:100%;justify-content:center}
  .msg{padding:12px 14px;border-radius:10px;margin-bottom:18px;font-size:15px}
  .msg.ok{background:var(--ok-bg);color:var(--ok);border:1px solid #ABEFC6}
  .msg.err{background:var(--err-bg);color:var(--err);border:1px solid #FECDCA}
  .list-item{border:1px dashed var(--line);border-radius:10px;padding:12px 14px 14px;margin-top:12px;background:#FCFBF8}
  .list-item .row-tools{display:flex;gap:6px;justify-content:flex-end;margin-bottom:-4px}
  .list-item .field:first-of-type{margin-top:0}
  .str-row{display:flex;gap:6px;margin-top:8px;align-items:center}
  .str-row input{flex:1}
  .add{margin-top:12px}
  .img-field{display:flex;gap:14px;align-items:center;flex-wrap:wrap}
  .img-field img{width:84px;height:84px;object-fit:cover;border-radius:10px;border:1px solid var(--line);background:#eee}
  .check{display:flex;gap:10px;align-items:center;font-weight:600;margin-top:6px}
  .check input{width:18px;height:18px;accent-color:var(--accent)}
  .savebar{position:sticky;bottom:0;background:rgba(244,242,236,.94);backdrop-filter:blur(6px);border-top:1px solid var(--line);padding:12px 0;margin-top:10px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;z-index:10}
  .toc{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:18px}
  .toc a{font-size:13px;font-weight:700;text-decoration:none;background:#fff;border:1px solid var(--line);border-radius:20px;padding:5px 12px;color:var(--ink-2)}
  .toc a:hover{color:var(--accent);border-color:var(--accent)}
  table{width:100%;border-collapse:collapse;font-size:14.5px}
  th,td{text-align:left;padding:10px 8px;border-bottom:1px solid var(--line);vertical-align:top}
  th{font-size:12px;text-transform:uppercase;letter-spacing:.04em;color:var(--ink-3)}
  td.msgcol{white-space:pre-wrap;max-width:360px}
  .table-wrap{overflow-x:auto}
  .tag{display:inline-block;font-size:11.5px;font-weight:700;padding:2px 8px;border-radius:10px;background:#EEF2FF;color:var(--accent)}
  .tag.warn{background:var(--err-bg);color:var(--err)}
  .login{max-width:420px;margin:8vh auto 0;padding:0 20px}
  .login .card{padding:30px}
  .login h1{font-size:26px;margin-bottom:6px}
  .or{display:flex;align-items:center;gap:10px;color:var(--ink-3);font-size:13px;margin:18px 0}
  .or::before,.or::after{content:"";flex:1;height:1px;background:var(--line)}
  .muted{color:var(--ink-3);font-size:14px}
</style>
</head>
<body>
<?php if ($admin): ?>
<div class="top"><div class="top-in">
  <div class="logo">zacznij<span>angielski</span> · panel</div>
  <nav>
    <a href="panel.php" class="<?= ($_GET['tab'] ?? 'tresc') === 'tresc' ? 'on' : '' ?>">Treść strony</a>
    <a href="panel.php?tab=zgloszenia" class="<?= ($_GET['tab'] ?? '') === 'zgloszenia' ? 'on' : '' ?>">Zgłoszenia</a>
    <a href="../" target="_blank" rel="noopener">Zobacz stronę ↗</a>
  </nav>
  <div class="who"><span><?= e($admin) ?></span>
    <form method="post" action="index.php" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="logout"><button class="btn small ghost" type="submit">Wyloguj</button></form>
  </div>
</div></div>
<?php endif; ?>
<?php
}

function admin_foot(): void
{
    echo "\n</body>\n</html>\n";
}
