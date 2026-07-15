# 🦈 Charlotte's Shark Facts — A Beginner CTF

A self-contained, intentionally-vulnerable Capture-The-Flag box packed into a single Docker image. A friendly shark-facts website hides **9 flags** across web source, a file server, a command-injection bug, a SUID privilege escalation, a `robots.txt` breadcrumb, and layered ciphers.

Every flag is in the `LCCTF{...}` format and spells out a real shark fact.

## Scoring
||||
|-|-|-|
|Flag|Points(ratio)|Why|
|1|1|view source + one base64 decode; everyone should get this|
|2|2|using FTP, unzip, binary decode. first real "do a thing"|
|3|2|stego in the same zip; same tier as 2|
|4|3|first exploitation (command injection)|
|5|5|SUID privesc; the hardest of the core chain|
|6|2|robots.txt + hex decode; easy|
|8|4|three-layer cipher peel|
|8|4|no exploit required, just extra points for getting all previous flags|
|9|12|bonus: injection + SUID + base64 + "key is Flag 8" + python and CLI tooling required|


## Build & run

```bash
# with docker compose (easiest)
docker compose up -d --build

# or plain docker
docker build -t shark-ctf .
docker run -d --name shark-ctf \
  -p 80:80 -p 21:21 -p 30000-30009:30000-30009 \
  shark-ctf
```

Then visit `http://<server-ip>/`.

---

A full step-by-step is in [`SOLUTIONS.md`](SOLUTIONS.md).

---
