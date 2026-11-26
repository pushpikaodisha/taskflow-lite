<?php
$db_file = __DIR__ . '/database.db';
$db_created = !file_exists($db_file);

try {
    $db = new PDO("sqlite:" . $db_file);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Initialize Schema if newly created
    if ($db_created || filesize($db_file) === 0) {
        $db->exec("CREATE TABLE IF NOT EXISTS tasks (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            status TEXT DEFAULT 'todo', -- todo, in_progress, review, done
            priority TEXT DEFAULT 'medium', -- high, medium, low
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // Seed some beautiful default items
        $default_tasks = [
            [
                "title" => "Design ShopSphere E-Commerce storefront Mockup",
                "description" => "Draft initial layout designs featuring dark glassmorphism, responsive navigation bars, and cart overlays.",
                "status" => "in_progress",
                "priority" => "high"
            ],
            [
                "title" => "Integrate SQLite zero-config databases for PHP",
                "description" => "Configure self-initializing database triggers inside php models to make XAMPP setups zero effort.",
                "status" => "done",
                "priority" => "high"
            ],
            [
                "title" => "Establish client-side hydration logging triggers",
                "description" => "Implement local storage variables handles to save data inputs across page reloads in frontend projects.",
                "status" => "todo",
                "priority" => "medium"
            ],
            [
                "title" => "Write project verification walkthrough guides",
                "description" => "Complete the global README.md with detailed descriptions of all 10 completed suites.",
                "status" => "todo",
                "priority" => "low"
            ]
        ];

        $stmt = $db->prepare("INSERT INTO tasks (title, description, status, priority) VALUES (?, ?, ?, ?)");
        foreach ($default_tasks as $t) {
            $stmt->execute([$t['title'], $t['description'], $t['status'], $t['priority']]);
        }
    }
} catch (PDOException $e) {
    die("SQLite Connection failed: " . $e->getMessage());
}
?>
