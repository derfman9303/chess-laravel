#!/usr/bin/env sh

# if the application crashes after deploying to fly.io, and you see an error in the logs like 'r\sh directory not found',
# then most likely it's because this file has been modified somehow to use Windows line endings instead of Linux line endings.
# To fix:
# 1. Open Powershell and navigate to the .fly directory that this file is in
# 2. Run the following commands:
# Get-Content -Raw entrypoint.sh | Format-Hex
# (Get-Content -Raw entrypoint.sh) -replace "\r\n", "`n" | Set-Content entrypoint.sh
# 3. Deploy to fly.io

# Run user scripts, if they exist
for f in /var/www/html/.fly/scripts/*.sh; do
    # Bail out this loop if any script exits with non-zero status code
    bash "$f" || break
done
chown -R www-data:www-data /var/www/html

if [ $# -gt 0 ]; then
    # If we passed a command, run it as root
    exec "$@"
else
    exec supervisord -c /etc/supervisor/supervisord.conf
fi



