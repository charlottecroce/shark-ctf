<?php
/**
 * deep_template.php -> /var/www/html/deep/<slug>/index.php  (slug from Flag 8)
 * Bonus "megalodon": command injection (Flag-4/5)
 */
$q = isset($_GET['q']) ? $_GET['q'] : '';
$out = null;

if ($q !== '') {
    $cmd = 'timeout 3 grep -i ' . $q . ' /opt/megalodon/fossils.txt 2>&1 | head -c 4000';
    $out = shell_exec($cmd);
    if ($out === null || $out === '') { $out = '(silence)'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>the depths</title>
  <!-- fossil index compiled from the sealed record in /opt/megalodon/ -->
  <style>
    :root{--void:#02060a;--glow:#7fd7ff;--bone:#8fb3c7;--rust:#c65b3a;}
    *{box-sizing:border-box}
    body{margin:0;min-height:100vh;background:var(--void);color:var(--bone);
      font-family:"Courier New",ui-monospace,monospace;
      background-image:radial-gradient(circle at 50% -10%,#0a2233 0%,#02060a 60%);
      display:flex;align-items:flex-start;justify-content:center;}
    .abyss{max-width:660px;width:100%;padding:4rem 1.5rem 6rem;}
    .eyebrow{text-transform:uppercase;letter-spacing:.42em;font-size:.6rem;
      color:#31586d;margin:0 0 1rem}
    h1{font-size:clamp(2rem,7vw,3.4rem);margin:0 0 .4rem;font-weight:800;
      letter-spacing:-.02em;color:#dbeef7;text-shadow:0 0 26px #0a3a52}
    .sub{color:#4d7185;margin:0 0 2.2rem;line-height:1.6}
    .sub em{color:var(--rust);font-style:normal}
    label{display:block;font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;
      color:#3d6478;margin:0 0 .5rem}
    input{width:100%;padding:.85rem 1rem;background:#040d14;color:var(--glow);
      border:1px solid #123243;border-radius:8px;font-family:inherit;font-size:1rem;outline:none}
    input:focus{border-color:#1d6f96;box-shadow:0 0 0 3px rgba(29,111,150,.18)}
    button{margin-top:1rem;background:transparent;color:var(--glow);border:1px solid #1d6f96;
      border-radius:8px;padding:.7rem 1.6rem;font-family:inherit;font-size:.9rem;
      letter-spacing:.12em;text-transform:uppercase;cursor:pointer;transition:all .15s}
    button:hover{background:#0b3346;color:#c9f5ff;text-shadow:0 0 10px var(--glow)}
    pre{margin:2rem 0 0;padding:1.1rem 1.2rem;background:#040d14;border:1px solid #10293a;
      border-radius:10px;color:#9fd0b0;white-space:pre-wrap;word-break:break-word;
      font-size:.82rem;box-shadow:inset 0 0 40px -18px #000}
    .back{display:inline-block;margin-top:2.5rem;color:#2f5266;font-size:.8rem;
      letter-spacing:.1em;text-decoration:none}
    .back:hover{color:#5f8095}
  </style>
</head>
<body>
  <div class="abyss">
    <p class="eyebrow">// pressure: 1000 atm &middot; light: none</p>
    <h1>the depths</h1>
    <p class="sub">Search the fossil index to find the remains of the <em>megalodon</em></p>


    <label for="q">query the record</label>
    <input type="text" id="q" name="q" placeholder="megalodon"
           value="<?= htmlspecialchars($q, ENT_QUOTES) ?>"
           onkeydown="if(event.key==='Enter')go()">
    <button onclick="go()">search</button>

    <?php if ($out !== null): ?>
      <pre><?= htmlspecialchars($out) ?></pre>
    <?php endif; ?>

    <a class="back" href="/final.php">&larr; surface</a>
  </div>
  <script>
    function go(){
      window.location = '?q=' + encodeURIComponent(document.getElementById('q').value);
    }
  </script>
</body>
</html>