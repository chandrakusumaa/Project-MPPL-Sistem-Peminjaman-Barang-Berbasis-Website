#!/usr/bin/env python3
"""Flatten only a newly built local PHP image on Docker's space-heavy vfs driver.
The filesystem comes from the checksum-verified image, without application mounts.
No source files or database volumes are removed.
"""
import json
import os
from pathlib import Path
import subprocess
import tempfile

def run(*args, **kwargs):
    return subprocess.run(["docker", *args], check=True, **kwargs)

driver = run("info", "--format", "{{.Driver}}", capture_output=True, text=True).stdout.strip()
image = json.loads(run("image", "inspect", "orginventory-php", capture_output=True, text=True).stdout)[0]
if driver != "vfs" or len(image["RootFS"]["Layers"]) <= 1:
    raise SystemExit(0)
# Never remove an image used by an existing application.
using = run("ps", "-aq", "--filter", "ancestor=orginventory-php", capture_output=True, text=True).stdout.strip()
if using:
    raise SystemExit("Runtime is already in use; preserve it and skip rebuilding before restarting services.")
container = run("create", "orginventory-php", capture_output=True, text=True).stdout.strip()
with tempfile.TemporaryDirectory(prefix="orginventory-runtime-", dir=os.environ.get("TMPDIR", "/tmp")) as directory:
    archive = str(Path(directory) / "runtime.tar")
    try:
        run("export", container, "-o", archive)
    finally:
        run("rm", container)
    run("image", "rm", "orginventory-php")
    # This is the exact public base used by docker/Dockerfile, not a user image.
    subprocess.run(["docker", "image", "rm", "webdevops/php:8.4"], check=False)
    run("builder", "prune", "-af")
    changes = ["--change", "WORKDIR /app", "--change", "ENTRYPOINT []",
               "--change", "CMD " + json.dumps(image["Config"]["Cmd"])]
    for pair in image["Config"].get("Env", []):
        key, value = pair.split("=", 1)
        changes += ["--change", "ENV " + key + "=" + json.dumps(value)]
    run("import", *changes, archive, "orginventory-php")
