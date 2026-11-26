<?php
require_once __DIR__ . '/db.php';

// Fetch all tasks
try {
    $stmt = $db->query("SELECT * FROM tasks ORDER BY id DESC");
    $all_tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database query error: " . $e->getMessage());
}

// Group tasks by column status
$columns = [
    'todo' => [],
    'in_progress' => [],
    'review' => [],
    'done' => []
];

foreach ($all_tasks as $task) {
    $status = $task['status'];
    if (array_key_exists($status, $columns)) {
        $columns[$status][] = $task;
    } else {
        $columns['todo'][] = $task; // fallback
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskFlow - Premium Kanban Project Tracker</title>
    <!-- Google Fonts: Inter & Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="task-flow-theme">

    <!-- Top Navigation Sticky -->
    <nav class="navbar navbar-dark pt-3 pb-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <span class="logo-accent-flow"><i class="fa-solid fa-clipboard-list me-2"></i>TASK</span><span class="logo-light-flow">FLOW</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-primary-flow btn-sm" data-bs-toggle="modal" data-bs-target="#createTaskModal"><i class="fa-solid fa-plus me-1"></i> New Task</button>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container-fluid px-md-5 my-5">
        
        <!-- Header -->
        <div class="row mb-5">
            <div class="col-md-12">
                <h1 class="welcome-text-flow">Streamline Workflows</h1>
                <p class="subtitle-text-flow">Plan tasks, drag or shift statuses instantly, and organize priority tracks on a high-integrity SQLite digital Kanban board.</p>
            </div>
        </div>

        <!-- Feedback banners -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert success-banner-flow p-3 mb-4 d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-check fs-5 me-3"></i>
                <div>
                    <?php 
                        if($_GET['success'] === 'created') echo "Awesome! Your new task card has been successfully added to the board.";
                        elseif($_GET['success'] === 'moved') echo "Status updated. The task card has shifted columns successfully.";
                        elseif($_GET['success'] === 'updated') echo "Success! The task information details were fully updated.";
                        elseif($_GET['success'] === 'deleted') echo "The task card has been deleted from the SQLite board.";
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-20 text-danger p-3 mb-4 d-flex align-items-center" role="alert">
                <i class="fa-solid fa-circle-exclamation fs-5 me-3"></i>
                <div>
                    <?php 
                        if($_GET['error'] === 'empty_title') echo "Validation Error: You must supply a valid title for the task.";
                        elseif($_GET['error'] === 'invalid_parameters') echo "Error: Missing parameters or task index. Please try again.";
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Kanban Board Row -->
        <div class="kanban-board-row">
            
            <!-- Column 1: TO DO -->
            <div class="kanban-column column-header-todo">
                <div class="column-title-row">
                    <span class="column-name">To Do</span>
                    <span class="column-count-badge" data-count-for="todo"><?php echo count($columns['todo']); ?></span>
                </div>
                <div class="kanban-cards-block" data-column-status="todo">
                    <?php renderColumnCards($columns['todo'], 'todo'); ?>
                </div>
            </div>

            <!-- Column 2: IN PROGRESS -->
            <div class="kanban-column column-header-progress">
                <div class="column-title-row">
                    <span class="column-name">In Progress</span>
                    <span class="column-count-badge" data-count-for="in_progress"><?php echo count($columns['in_progress']); ?></span>
                </div>
                <div class="kanban-cards-block" data-column-status="in_progress">
                    <?php renderColumnCards($columns['in_progress'], 'in_progress'); ?>
                </div>
            </div>

            <!-- Column 3: UNDER REVIEW -->
            <div class="kanban-column column-header-review">
                <div class="column-title-row">
                    <span class="column-name">In Review</span>
                    <span class="column-count-badge" data-count-for="review"><?php echo count($columns['review']); ?></span>
                </div>
                <div class="kanban-cards-block" data-column-status="review">
                    <?php renderColumnCards($columns['review'], 'review'); ?>
                </div>
            </div>

            <!-- Column 4: DONE -->
            <div class="kanban-column column-header-done">
                <div class="column-title-row">
                    <span class="column-name">Completed</span>
                    <span class="column-count-badge" data-count-for="done"><?php echo count($columns['done']); ?></span>
                </div>
                <div class="kanban-cards-block" data-column-status="done">
                    <?php renderColumnCards($columns['done'], 'done'); ?>
                </div>
            </div>

        </div>

    </div>

    <!-- CREATE TASK MODAL POPUP -->
    <div class="modal fade" id="createTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-flow">
                <div class="modal-header border-bottom-flow">
                    <h5 class="modal-title font-outfit text-white fw-bold"><i class="fa-solid fa-circle-plus text-indigo-flow me-2"></i>Add New Task Card</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions.php?action=create" method="POST">
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label-flow text-muted-flow small">Task Title</label>
                            <input type="text" name="title" class="form-control-poll" placeholder="Enter task name (e.g. Set up API endpoints)" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-flow text-muted-flow small">Task Description</label>
                            <textarea name="description" class="form-control-poll" rows="3" placeholder="Provide description details for the routine..."></textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label-flow text-muted-flow small">Status Column</label>
                                <select name="status" class="form-select-flow w-100">
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">In Review</option>
                                    <option value="done">Completed</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label-flow text-muted-flow small">Priority Rank</label>
                                <select name="priority" class="form-select-flow w-100">
                                    <option value="high">High Priority</option>
                                    <option value="medium" selected>Medium Priority</option>
                                    <option value="low">Low Priority</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-flow btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-flow btn-sm">Create Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EDIT TASK MODAL POPUP (Hydrated via JavaScript) -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-flow">
                <div class="modal-header border-bottom-flow">
                    <h5 class="modal-title font-outfit text-white fw-bold"><i class="fa-solid fa-pencil text-indigo-flow me-2"></i>Modify Task Card</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions.php?action=edit" method="POST">
                    <input type="hidden" name="id" id="editTaskId">
                    <div class="modal-body text-start">
                        <div class="mb-3">
                            <label class="form-label-flow text-muted-flow small">Task Title</label>
                            <input type="text" name="title" id="editTaskTitle" class="form-control-poll" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label-flow text-muted-flow small">Task Description</label>
                            <textarea name="description" id="editTaskDescription" class="form-control-poll" rows="3"></textarea>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label-flow text-muted-flow small">Status Column</label>
                                <select name="status" id="editTaskStatus" class="form-select-flow w-100">
                                    <option value="todo">To Do</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="review">In Review</option>
                                    <option value="done">Completed</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label-flow text-muted-flow small">Priority Rank</label>
                                <select name="priority" id="editTaskPriority" class="form-select-flow w-100">
                                    <option value="high">High Priority</option>
                                    <option value="medium">Medium Priority</option>
                                    <option value="low">Low Priority</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-outline-flow btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary-flow btn-sm">Update Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="text-center py-4 text-muted-flow border-top-flow">
        <p class="mb-0 small">TaskFlow • Digital SQLite Kanban System</p>
    </footer>

    <!-- Bootstrap & Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Hydrate & open edit task modal
        function openEditModal(id, title, desc, status, priority) {
            document.getElementById('editTaskId').value = id;
            document.getElementById('editTaskTitle').value = title;
            document.getElementById('editTaskDescription').value = desc;
            document.getElementById('editTaskStatus').value = status;
            document.getElementById('editTaskPriority').value = priority;

            const modal = new bootstrap.Modal(document.getElementById('editTaskModal'));
            modal.show();
        }

        function confirmDelete(id) {
            window.location.href = `actions.php?action=delete&id=${id}`;
        }

        // HTML5 Drag-and-Drop Kanban movement
        let draggedCard = null;
        document.querySelectorAll('.task-card-item[draggable="true"]').forEach(card => {
            card.addEventListener('dragstart', (e) => {
                draggedCard = card;
                card.classList.add('dragging');
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', card.dataset.taskId || '');
            });
            card.addEventListener('dragend', () => {
                card.classList.remove('dragging');
                document.querySelectorAll('.kanban-cards-block').forEach(col => col.classList.remove('drag-over'));
                draggedCard = null;
            });
        });

        function refreshColumnCounts() {
            document.querySelectorAll('.kanban-cards-block[data-column-status]').forEach(col => {
                const status = col.dataset.columnStatus;
                const cards = col.querySelectorAll('.task-card-item');
                const badge = document.querySelector(`.column-count-badge[data-count-for="${status}"]`);
                if (badge) badge.textContent = cards.length;
                const emptyBox = col.querySelector('.kanban-empty-placeholder');
                if (cards.length === 0 && !emptyBox) {
                    const div = document.createElement('div');
                    div.className = 'kanban-empty-placeholder text-center text-secondary py-5 small italic';
                    div.style.cssText = 'border: 1px dashed rgba(255,255,255,0.03); border-radius:12px;';
                    div.textContent = 'No cards in column';
                    col.appendChild(div);
                } else if (cards.length > 0 && emptyBox) {
                    emptyBox.remove();
                }
            });
        }

        document.querySelectorAll('.kanban-cards-block[data-column-status]').forEach(col => {
            col.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'move';
                col.classList.add('drag-over');
            });
            col.addEventListener('dragleave', () => {
                col.classList.remove('drag-over');
            });
            col.addEventListener('drop', async (e) => {
                e.preventDefault();
                col.classList.remove('drag-over');
                if (!draggedCard) return;

                const targetStatus = col.dataset.columnStatus;
                const prevCol = draggedCard.parentElement;
                const taskId = draggedCard.dataset.taskId;
                if (!taskId || draggedCard.dataset.status === targetStatus) return;

                col.appendChild(draggedCard);
                draggedCard.dataset.status = targetStatus;
                refreshColumnCounts();

                try {
                    const body = new URLSearchParams({ id: taskId, status: targetStatus });
                    const res = await fetch('actions.php?action=move_status&format=json', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString()
                    });
                    if (!res.ok) throw new Error('Move failed');
                } catch (err) {
                    if (prevCol) prevCol.appendChild(draggedCard);
                    draggedCard.dataset.status = prevCol?.dataset.columnStatus || draggedCard.dataset.status;
                    refreshColumnCounts();
                }
            });
        });
    </script>
</body>
</html>

<?php
// PHP functional template helper to render column card components
function renderColumnCards($tasks, $status) {
    if (empty($tasks)) {
        echo '<div class="kanban-empty-placeholder text-center text-secondary py-5 small italic" style="border: 1px dashed rgba(255,255,255,0.03); border-radius:12px;">No cards in column</div>';
        return;
    }

    foreach ($tasks as $t) {
        $escapedTitle = htmlspecialchars($t['title']);
        $escapedDesc = htmlspecialchars($t['description']);
        $jsTitle = addslashes($t['title']);
        $jsDesc = addslashes($t['description']);
        
        echo "
        <div class='task-card-item' draggable='true' data-task-id='{$t['id']}' data-status='{$t['status']}'>
            <div>
                <div class='task-card-header'>
                    <div class='task-card-title'>{$escapedTitle}</div>
                    
                    <!-- dropdown edit/delete button triggers -->
                    <div class='dropdown'>
                        <button class='task-actions-dropdown' type='button' data-bs-toggle='dropdown'>
                            <i class='fa-solid fa-ellipsis-vertical'></i>
                        </button>
                        <ul class='dropdown-menu dropdown-menu-end dropdown-menu-dark bg-dark border-secondary'>
                            <li><button class='dropdown-item' onclick=\"openEditModal({$t['id']}, '{$jsTitle}', '{$jsDesc}', '{$t['status']}', '{$t['priority']}')\"><i class='fa-regular fa-pen-to-square me-2 text-warning'></i> Edit Details</button></li>
                            <li><button class='dropdown-item text-danger' onclick='confirmDelete({$t['id']})'><i class='fa-solid fa-trash-can me-2'></i> Delete Task</button></li>
                        </ul>
                    </div>
                </div>
                <p class='task-card-desc'>{$escapedDesc}</p>
            </div>
            
            <div class='task-card-footer'>
                <span class='priority-badge-flow priority-{$t['priority']}'>{$t['priority']}</span>
                
                <!-- Quick moves shift arrows -->
                <div class='quick-shift-buttons'>";
                
                if ($status !== 'todo') {
                    echo "<a href='actions.php?action=move&dir=left&id={$t['id']}' class='btn-shift' title='Move Left'><i class='fa-solid fa-chevron-left'></i></a>";
                }
                if ($status !== 'done') {
                    echo "<a href='actions.php?action=move&dir=right&id={$t['id']}' class='btn-shift' title='Move Right'><i class='fa-solid fa-chevron-right'></i></a>";
                }
                
                echo "
                </div>
            </div>
        </div>";
    }
}
?>
