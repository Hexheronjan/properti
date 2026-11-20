<?php
shell_exec('php artisan key:generate');
shell_exec('php artisan migrate --force');
shell_exec('php artisan storage:link');
shell_exec('php artisan config:cache');
shell_exec('php artisan route:cache');
shell_exec('php artisan view:cache');
echo "Artisan commands executed!";
?>