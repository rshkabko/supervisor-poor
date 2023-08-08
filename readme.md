## Supervisor for poor

This is a custom Laravel Artisan command that checks if specified commands are running and starts them if not.

```php
// Add to config/queue.php
'supervisor' => [
    'queue:listen --queue=demo' => 1,
]

// Add to cron
$schedule->command('flamix:supervisor')->everyFiveMinutes()->runInBackground();
```

## Mannually run command

```bash
php artisan flamix:supervisor
```
