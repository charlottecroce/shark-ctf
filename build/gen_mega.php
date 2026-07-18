<?php
/**
 * gen_mega.php. runs ONCE at docker build. Bonus megalodon flag.
 *
 * Player flow:
 *   /opt/megalodon/.last_sighting = RAW cipher(flag, key=Flag8), root-only
 *   1) reach the deep page (gated by Flag 8), find the file via the injection
 *   2) plain cat is denied -> read it with the SUID base64 (Flag 5 exploit),
 *      which prints ONE base64 layer of the raw bytes
 *   3) base64 -d -> raw sealed bytes -> decrypt with encrypt_megalodon.py,
 *      key = Flag 8 -> the flag
 *
 * Flag 8 never exists in plaintext on disk, so even with the bytes and the
 * cipher tool in hand there's no decrypt without solving through Flag 8.
 */
$flag8tok = 'LCCTF{3L45M0BR4NCH11}';
$mega     = 'LCCTF{0T0D5M3g4L0D0N} Otodus megalodon - the largest shark that ever lived';

// cipher: keystream = SHA-256(key . pack('J', i)); XOR. Byte-identical to the .py tool.
function mega_ks(string $key, int $n): string {
    $out = ''; $i = 0;
    while (strlen($out) < $n) { $out .= hash('sha256', $key . pack('J', $i), true); $i++; }
    return substr($out, 0, $n);
}
$cipherBytes = $mega ^ mega_ks($flag8tok, strlen($mega));   // raw ciphertext bytes

@mkdir('/opt/megalodon', 0755, true);

// the cipher tool - discoverable via the injection (but useless without the key)
copy(is_file('/build/encrypt_megalodon.py') ? '/build/encrypt_megalodon.py'
                                             : __DIR__ . '/encrypt_megalodon.py',
     '/opt/megalodon/encrypt_megalodon.py');

// decoy data the lookup actually greps
file_put_contents('/opt/megalodon/fossils.txt',
    "megalodon           Otodus megalodon          extinct ~3.6 Ma, est. 15-18 m\n" .
    "great white         Carcharodon carcharias    extant, up to 6 m\n" .
    "basking             Cetorhinus maximus        extant, filter feeder, up to 12 m\n" .
    "whale shark         Rhincodon typus           extant, largest living fish, up to 18 m\n" .
    "shortfin mako       Isurus oxyrinchus         extant, fastest shark, ~74 km/h\n" .
    "great hammerhead    Sphyrna mokarran          extant, up to 6 m\n" .
    "tiger               Galeocerdo cuvier         extant, up to 5 m\n" .
    "bull                Carcharhinus leucas       extant, tolerates fresh water\n" .
    "greenland           Somniosus microcephalus   extant, lifespan 250-500 yr\n" .
    "goblin              Mitsukurina owstoni       extant, deep-sea, living fossil\n" .
    "frilled             Chlamydoselachus anguineus extant, deep-sea, eel-like\n" .
    "thresher            Alopias vulpinus          extant, tail up to half its length\n" .
    "nurse               Ginglymostoma cirratum    extant, bottom-dweller\n" .
    "lemon               Negaprion brevirostris    extant, coastal\n" .
    "sand tiger          Carcharias taurus         extant, ragged-tooth\n" .
    "cookiecutter        Isistius brasiliensis     extant, deep-sea, small\n" .
    "megamouth           Megachasma pelagios       extant, rare, filter feeder\n" .
    "angelshark          Squatina squatina         critically endangered, flat-bodied\n" .
    "Portuguese dogfish  Centroscymnus coelolepis  extant, deepest-living shark\n" .
    "helicoprion         Helicoprion bessonovi     extinct ~250 Ma, tooth-whorl jaw\n" .
    "Cretoxyrhina        Cretoxyrhina mantelli     extinct ~100 Ma, 'Ginsu shark'\n" .
    "Otodus obliquus     Otodus obliquus           extinct ~55 Ma, megalodon ancestor\n" .
    "Ptychodus           Ptychodus mortoni         extinct ~85 Ma, shell-crusher\n" .
    "Stethacanthus       Stethacanthus altonensis  extinct ~320 Ma, 'anvil' dorsal\n");

// A plain cat as www-data is denied; the SUID base64 (Flag 5) reads it and prints one clean base64 layer
file_put_contents('/opt/megalodon/.last_sighting', $cipherBytes);

chmod('/opt/megalodon', 0755);
foreach (['fossils.txt','encrypt_megalodon.py'] as $f) {
    chmod('/opt/megalodon/' . $f, 0644);        // www-data can read these
}
// root-only -> forces the SUID base64 read, like Flag 5
chown('/opt/megalodon/.last_sighting', 'root');
chgrp('/opt/megalodon/.last_sighting', 'root');
chmod('/opt/megalodon/.last_sighting', 0600);

// endpoint slug derived from Flag 8 -> URL only appears in final.php's win branch
$slug = substr(hash('sha256', 'megalodon|' . $flag8tok), 0, 24);
$dir  = '/var/www/html/deep/' . $slug;
@mkdir($dir, 0755, true);
copy(is_file('/build/deep_template.php') ? '/build/deep_template.php'
                                          : __DIR__ . '/deep_template.php',
     $dir . '/index.php');

// self-test: seal to a temp file and prove the shipped tool decrypts it
$tmp = '/tmp/mega_sealed.bin';
file_put_contents($tmp, $cipherBytes);
$rt = shell_exec('python3 /opt/megalodon/encrypt_megalodon.py dec -k '
    . escapeshellarg($flag8tok) . ' -i ' . escapeshellarg($tmp) . ' 2>&1');
@unlink($tmp);
if (trim((string)$rt) !== $mega) {
    fwrite(STDERR, "FATAL: megalodon round-trip failed: $rt\n"); exit(1);
}
echo "megalodon: endpoint /deep/$slug/  (sealed + verified)\n";