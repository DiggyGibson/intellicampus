<?php
$content = file_get_contents('intellicampus_schema.sql');
if (substr($content, 0, 3) == "\xEF\xBB\xBF") {
    $content = substr($content, 3);
}
file_put_contents('clean.sql', $content);
echo shell_exec('PGPASSWORD=${DB_PASSWORD} psql -h ${DB_HOST} -U ${DB_USERNAME} -d ${DB_DATABASE} -p ${DB_PORT} -f clean.sql 2>&1');
