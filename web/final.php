<?php
/**
 * 
 * final.php. Enter all 7 flags to reveal Flag 8.
 *
 * Security model:
 *   - Validation is 100% server-side.
 *   - Flag 8 is AES-256-CBC ciphertext whose key is derived from the 7 flags.
 *   - If any flag is wrong, decryption fails and the result is rejected by the LCCTF{...} format check.
 *   - Flag 8 is REQUIRED to get Flag 9. No workarounds possible
 */

$cfg = @include '/var/www/secret/flag8.enc.php';

$reward = null;
$error  = null;
$submitted = ($_SERVER['REQUEST_METHOD'] === 'POST');

if ($submitted) {
    $flags = [];
    for ($i = 1; $i <= 7; $i++) {
        $flags[] = isset($_POST["f$i"]) ? trim($_POST["f$i"]) : '';
    }

    if (in_array('', $flags, true)) {
        $error = 'Please fill in all seven flags.';
    } elseif (!is_array($cfg) || empty($cfg['ct']) || empty($cfg['iv'])) {
        $error = 'Server misconfiguration: flag store missing.';
    } else {
        $key = hash('sha256', implode('', $flags), true);
        $pt  = openssl_decrypt(
            base64_decode($cfg['ct']),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            base64_decode($cfg['iv'])
        );

        if ($pt !== false && preg_match('/^LCCTF\{.+\}/', $pt)) {
            $reward = $pt; // all seven correct
        } else {
            $error = 'At least one flag is incorrect.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Final flag</title>
  <link rel="stylesheet" href="/style.css">
  <style>
    .final{max-width:560px;margin:1.5rem auto;padding:0 1.5rem}
    .box{background:#fff;border:1px solid #9aa7b0;padding:1.2rem}
    label{display:block;font-size:11px;font-weight:bold;color:var(--surf);
          margin:.7rem 0 .2rem}
    input[type=text]{width:100%;padding:.4rem .5rem;border:1px solid #7f8c94;
          font-size:13px;font-family:monospace}
    .win{margin-top:1.2rem;padding:1rem;background:#0d3b1e;
         color:#c9ffd8;text-align:center;border:1px solid #062915}
    .win code{font-size:13px;word-break:break-word}
    .err{margin-top:1rem;padding:.6rem .8rem;
         background:#fdeceb;color:#8a2b20;border:1px solid #d9a29c}
    .submit{margin-top:1.2rem}
    .depths{margin-top:1.2rem;padding:1.2rem;
         background:#02060a;color:#5f8095;text-align:center;
         border:1px solid #0d2635;font-family:"Courier New",monospace}
    .depths .eyebrow{text-transform:uppercase;letter-spacing:.12em;font-size:10px;
         color:#2f5266;margin:0 0 .5rem}
    .depths p{margin:.2rem 0}
    .depths em{color:#c65b3a;font-style:normal}
    .depths a{display:inline-block;margin-top:.9rem;color:#7fd7ff;font-weight:bold;
         text-decoration:underline;letter-spacing:.02em}
    .depths a:hover{color:#c9f5ff}
  </style>
</head>
<body>
  <div class="final">
    <div class="box">
      <h1 style="margin-top:0">Final flag</h1>
      <p>Enter all seven flags to unlock the eighth.</p>

      <?php if ($reward !== null): ?>
        <div class="win">
          <p style="margin:0 0 .4rem">Congratulations! All seven flags are correct. Here's your final flag:</p>
      <!--
                       (\.-./)
                       /     \
                     .'   :   '.
                _.-'`     '     `'-._
             .-'          :          '-.
           ,'_.._         .         _.._',
           '`    `'-.     '     .-'`    `'
                     '.   :   .'
                       \_. ._/
                 \       |^|
                  |      | ;
                  \'.___.' /
                   '-....-'  
      -->
          <code><?= htmlspecialchars($reward) ?></code>
          <p style="margin:.8rem 0 0">Thanks for playing!</p>
        </div>
        <?php
          // Recompute the megalodon endpoint from Flag 8's token. This only runs
          // on success, so the slug never appears in served source and can't be
          // read from final.php via the RCE.
          preg_match('/^(LCCTF\{[^}]+\})/', $reward, $m);
          $megaSlug = substr(hash('sha256', 'megalodon|' . $m[1]), 0, 24);
        ?>
        <div class="depths">
          <p class="eyebrow">// challenging waters below</p>
          <a href="/deep/<?= htmlspecialchars($megaSlug) ?>/">Can you find the megalodon? &darr;</a>
        </div>
      <?php else: ?>
        <?php if ($error !== null): ?>
          <div class="err"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
<form method="post" autocomplete="off">
  <label for="f1">Flag 1 <span style="font-weight:400;color:#8a9199">(whale shark)</span></label>
  <input type="text" id="f1" name="f1" placeholder="LCCTF{...}"
         value="<?= isset($_POST['f1']) ? htmlspecialchars($_POST['f1']) : '' ?>">

  <label for="f2">Flag 2 <span style="font-weight:400;color:#8a9199">(greenland shark)</span></label>
  <input type="text" id="f2" name="f2" placeholder="LCCTF{...}"
         value="<?= isset($_POST['f2']) ? htmlspecialchars($_POST['f2']) : '' ?>">

  <label for="f3">Flag 3 <span style="font-weight:400;color:#8a9199">(hammerhead)</span></label>
  <input type="text" id="f3" name="f3" placeholder="LCCTF{...}"
         value="<?= isset($_POST['f3']) ? htmlspecialchars($_POST['f3']) : '' ?>">

  <label for="f4">Flag 4 <span style="font-weight:400;color:#8a9199">(great white)</span></label>
  <input type="text" id="f4" name="f4" placeholder="LCCTF{...}"
         value="<?= isset($_POST['f4']) ? htmlspecialchars($_POST['f4']) : '' ?>">

  <label for="f5">Flag 5 <span style="font-weight:400;color:#8a9199">(basking shark)</span></label>
  <input type="text" id="f5" name="f5" placeholder="LCCTF{...}"
         value="<?= isset($_POST['f5']) ? htmlspecialchars($_POST['f5']) : '' ?>">

  <label for="f6">Flag 6 <span style="font-weight:400;color:#8a9199">(bull shark)</span></label>
  <input type="text" id="f6" name="f6" placeholder="LCCTF{...}"
         value="<?= isset($_POST['f6']) ? htmlspecialchars($_POST['f6']) : '' ?>">

  <label for="f7">Flag 7 <span style="font-weight:400;color:#8a9199">(mako)</span></label>
  <input type="text" id="f7" name="f7" placeholder="LCCTF{...}"
         value="<?= isset($_POST['f7']) ? htmlspecialchars($_POST['f7']) : '' ?>">

  <button class="btn submit" type="submit">Submit all seven</button>
</form>
      <?php endif; ?>

      <p style="margin-top:1.5rem"><a href="/">&larr; back to the facts</a></p>
    </div>
  </div>
</body>
</html>
