<?php
/** Ejemplo para el proyecto PostgreSQL. Guardar como database.php en local.
 *  En Render el Dockerfile lo copia automáticamente. No poner contraseñas aquí.
 */
final class Database
{
    public function connect(): PDO
    {
        $url = getenv('DATABASE_URL');
        if ($url === false || trim($url) === '') {
            throw new RuntimeException('Falta DATABASE_URL en el entorno (PostgreSQL).');
        }
        $config = parse_url($url);
        if (!$config || !in_array($config['scheme'] ?? '', ['postgres', 'postgresql'], true)) {
            throw new RuntimeException('DATABASE_URL debe ser de PostgreSQL.');
        }
        $db = ltrim($config['path'] ?? '', '/');
        if ($db === '' || empty($config['host']) || empty($config['user'])) {
            throw new RuntimeException('DATABASE_URL incompleta.');
        }
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s;sslmode=require',
            $config['host'], $config['port'] ?? 5432, rawurldecode($db));
        return new PDO($dsn, rawurldecode($config['user']), rawurldecode($config['pass'] ?? ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }
}
