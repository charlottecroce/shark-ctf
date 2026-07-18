#!/usr/bin/env python3
"""

  Encrypt:  python3 encrypt_megalodon.py enc -k 'KEY' -i message.txt -o last_sighting.bin
  Decrypt:  python3 encrypt_megalodon.py dec -k 'KEY' -i last_sighting.bin

  Hint: the key is directly under the ASCII stingray in /* final.php */.

"""

import argparse, hashlib, sys

def keystream(key, n):
    out = bytearray(); i = 0
    while len(out) < n:
        out += hashlib.sha256(key + i.to_bytes(8, "big")).digest(); i += 1
    return bytes(out[:n])

def xor(data, key):
    return bytes(a ^ b for a, b in zip(data, keystream(key, len(data))))

def main():
    p = argparse.ArgumentParser()
    p.add_argument("mode", choices=["enc", "dec"])
    p.add_argument("-k", "--key", required=True)
    p.add_argument("-i", "--infile", required=True)
    p.add_argument("-o", "--outfile")
    a = p.parse_args()
    data = open(a.infile, "rb").read()
    res = xor(data, a.key.encode())
    if a.mode == "enc":
        out = a.outfile or (a.infile + ".enc")
        open(out, "wb").write(res); print("wrote", out)
    else:
        sys.stdout.buffer.write(res); sys.stdout.write("\n")

if __name__ == "__main__":
    main()