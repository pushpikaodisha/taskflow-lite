<?php
require_once __DIR__ . '/../db.php';
$count = (int)$db->query("SELECT COUNT(*) FROM tasks")->fetchColumn();
assert($count >= 1, "Expected seeded tasks in taskflow-lite");
echo "taskflow-lite smoke test PASSED (tasks={$count})\n";
