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
body{margin:0;min-height:100vh;background:#02060a;color:var(--bone);
font-family:"Courier New",ui-monospace,monospace;}
.abyss{max-width:660px;margin:0 auto;padding:2rem 1.5rem 4rem;}
.eyebrow{text-transform:uppercase;letter-spacing:.2em;font-size:10px;
color:#31586d;margin:0 0 1rem}
h1{font-size:2.2rem;margin:0 0 .4rem;font-weight:bold;
letter-spacing:normal;color:#dbeef7}
.sub{color:#4d7185;margin:0 0 1.6rem;line-height:1.5}
.sub em{color:var(--rust);font-style:normal}
label{display:block;font-size:10px;letter-spacing:.1em;text-transform:uppercase;
color:#3d6478;margin:0 0 .4rem}
input{width:100%;padding:.5rem .6rem;background:#040d14;color:var(--glow);
border:1px solid #123243;font-family:inherit;font-size:13px;outline:none}
input:focus{border-color:#1d6f96}
button{margin-top:.8rem;background:#0b3346;color:var(--glow);border:1px solid #1d6f96;
padding:.4rem 1.2rem;font-family:inherit;font-size:12px;
letter-spacing:.06em;text-transform:uppercase;cursor:pointer}
button:hover{background:#12455e;color:#c9f5ff}
pre{margin:1.5rem 0 0;padding:.9rem 1rem;background:#040d14;border:1px solid #10293a;
color:#9fd0b0;white-space:pre-wrap;word-break:break-word;font-size:12px}
.back{display:inline-block;margin-top:1.6rem;color:#2f5266;font-size:11px;
letter-spacing:.06em;text-decoration:underline}
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