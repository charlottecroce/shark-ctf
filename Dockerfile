# syntax=docker/dockerfile:1
# Charlotte's Shark Facts CTF — multi-stage so flag-bearing generators
# never ship in any layer of the final image.
#
# Build:  docker build -t shark-ctf .
# Run:    docker run -d --name shark-ctf -p 80:80 -p 21:21 -p 30000-30009:30000-30009 shark-ctf

# ─────────────────────────────────────────────────────────────────────────────
# STAGE 1 — builder.  Runs every generator (each carries flags in plaintext).
# Nothing from this stage ships except explicitly-copied OUTPUTS.
# ─────────────────────────────────────────────────────────────────────────────
FROM debian:bookworm-slim AS builder
ENV DEBIAN_FRONTEND=noninteractive

# php-cli runs the generators; python3 is needed for gen_mega's self-test
# and for building the FTP zip.
RUN apt-get update && apt-get install -y --no-install-recommends \
        php-cli python3 ca-certificates \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /build
COPY build/gen_flag8.php        /build/gen_flag8.php
COPY build/gen_mega.php         /build/gen_mega.php
COPY build/deep_template.php    /build/deep_template.php
COPY build/encrypt_megalodon.py /build/encrypt_megalodon.py
COPY build/make_ftp_assets.py   /build/make_ftp_assets.py
COPY build/hammerhead.jpg       /build/hammerhead.jpg

# Flag 8  -> /var/www/secret/flag8.enc.php   (ciphertext only; safe to ship)
RUN php /build/gen_flag8.php

# Megalodon -> /opt/megalodon/*  and  /var/www/html/deep/<slug>/index.php
# (gen_mega runs its own python3 round-trip self-test and exits non-zero on fail)
RUN php /build/gen_mega.php

# Flags 2 & 3 -> /staging/diver/flag.abc  (ZIP: flag2 binary + hammerhead image)
RUN mkdir -p /staging/diver \
    && python3 /build/make_ftp_assets.py /staging/diver

# ─────────────────────────────────────────────────────────────────────────────
# STAGE 2 — runtime.  Fresh base; pulls in only the generated artifacts.
# The generators (gen_*.php, make_ftp_assets.py) do not exist in this image.
# ─────────────────────────────────────────────────────────────────────────────
FROM debian:bookworm-slim
ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
        apache2 \
        libapache2-mod-php \
        php-cli \
        vsftpd \
        python3 \
        ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Web root (Flags 1, 4, 6, 7, 8)
COPY web/ /var/www/html/
RUN for m in $(ls /etc/apache2/mods-available/php*.load 2>/dev/null | xargs -n1 basename 2>/dev/null | sed 's/\.load$//'); do a2enmod "$m"; done; \
    a2dismod -f autoindex 2>/dev/null; \
    rm -f /var/www/html/index.debian.html \
    && chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -exec chmod 644 {} \; \
    && find /var/www/html -type d -exec chmod 755 {} \;

# Flag 8 — encrypted reward (key never on disk). Ciphertext only.
COPY --from=builder /var/www/secret /var/www/secret
RUN chown -R www-data:www-data /var/www/secret \
    && chmod 755 /var/www/secret \
    && chmod 644 /var/www/secret/flag8.enc.php

# Bonus — megalodon.  encrypt_megalodon.py stays (intended discovery, useless
# without the key); .last_sighting is root-only so it forces the SUID base64 read.
COPY --from=builder /opt/megalodon /opt/megalodon
RUN chmod 755 /opt/megalodon \
    && chmod 644 /opt/megalodon/fossils.txt /opt/megalodon/encrypt_megalodon.py \
    && chown root:root /opt/megalodon/.last_sighting \
    && chmod 600 /opt/megalodon/.last_sighting

# deep/<slug>/ — parent is root-owned + 711 so `ls deep/` is denied to www-data,
# but a known slug path is still traversable + served by Apache.
COPY --from=builder /var/www/html/deep /var/www/html/deep
RUN find /var/www/html/deep -type d -exec chmod 755 {} \; \
    && find /var/www/html/deep -type f -exec chmod 644 {} \; \
    && chown -R www-data:www-data /var/www/html/deep/*/ \
    && chown root:root /var/www/html/deep \
    && chmod 711 /var/www/html/deep

# Flag 5 — root-owned flag, reachable only via the SUID base64 privesc
RUN printf '%s\n' \
'LCCTF{B4Sk1Ng1500Kg} Basking sharks can weigh around 1,500 kg' \
      > /root/flag5.txt \
    && chown root:root /root/flag5.txt \
    && chmod 600 /root/flag5.txt \
    && chmod 700 /root \
    && chmod u+s /usr/bin/base64

# Flags 2 & 3 — read-only FTP file server.  User first, then drop the asset in.
COPY config/vsftpd.conf /etc/vsftpd.conf
RUN useradd -m -d /home/diver -s /usr/sbin/nologin diver \
    && echo 'diver:iLoV35h4rks' | chpasswd \
    && echo '/usr/sbin/nologin' >> /etc/shells \
    && echo 'diver' > /etc/vsftpd.userlist \
    && mkdir -p /var/run/vsftpd/empty
COPY --from=builder /staging/diver/flag.abc /home/diver/flag.abc
RUN chown root:root /home/diver /home/diver/flag.abc \
    && chmod 555 /home/diver \
    && chmod 444 /home/diver/flag.abc

# Entrypoint (starts vsftpd + apache; re-asserts SUID base64)
COPY config/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80 21 30000-30009
ENTRYPOINT ["/entrypoint.sh"]