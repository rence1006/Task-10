<?php
require_once 'connection.php';

$message = '';
$editEntry = null;

if (isset($_POST['add_entry'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("INSERT INTO journal (title, content) VALUES (?, ?)");
        if ($stmt->execute([$title, $content])) {
            $message = '<div class="alert alert-success">Entry added successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error adding entry.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Please fill in all fields.</div>';
    }
}

if (isset($_POST['update_entry'])) {
    $id = $_POST['id'];
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
    if (!empty($title) && !empty($content)) {
        $stmt = $pdo->prepare("UPDATE journal SET title = ?, content = ? WHERE id = ?");
        if ($stmt->execute([$title, $content, $id])) {
            $message = '<div class="alert alert-success">Entry updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating entry.</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Please fill in all fields.</div>';
    }
}

// DELETE - Delete entry
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM journal WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = '<div class="alert alert-success">Entry deleted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Error deleting entry.</div>';
    }
}

// GET entry for editing
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM journal WHERE id = ?");
    $stmt->execute([$id]);
    $editEntry = $stmt->fetch();
}

// READ - Retrieve all entries
$stmt = $pdo->query("SELECT * FROM journal ORDER BY entry_date DESC");
$entries = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header class="my-5 text-center">
            <h1 class="display-4">📔 My Personal Journal</h1>
            <p class="text-muted">Keep track of your thoughts and experiences</p>
        </header>

        <?php echo $message; ?>

        <!-- Add/Edit Entry Form -->
        <div class="card mb-5 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3><?php echo $editEntry ? '✏️ Edit Entry' : '➕ New Entry'; ?></h3>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php if ($editEntry): ?>
                        <input type="hidden" name="id" value="<?php echo $editEntry['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="title" 
                            name="title" 
                            placeholder="Enter a title for your entry"
                            value="<?php echo $editEntry ? htmlspecialchars($editEntry['title']) : ''; ?>"
                            required
                        >
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea 
                            class="form-control" 
                            id="content" 
                            name="content" 
                            rows="5" 
                            placeholder="Write your thoughts here..."
                            required
                        ><?php echo $editEntry ? htmlspecialchars($editEntry['content']) : ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <?php if ($editEntry): ?>
                            <button type="submit" name="update_entry" class="btn btn-success">Update Entry</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        <?php else: ?>
                            <button type="submit" name="add_entry" class="btn btn-primary">Add Entry</button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Display All Entries -->
        <div class="entries-section">
            <h2 class="mb-4">📚 All Entries (<?php echo count($entries); ?>)</h2>
            
            <?php if (empty($entries)): ?>
                <div class="alert alert-info">
                    No entries yet. Start writing your first journal entry above!
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($entries as $entry): ?>
                        <div class="col-md-6 mb-4">
                            <div class="card h-100 shadow-sm entry-card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0"><?php echo htmlspecialchars($entry['title']); ?></h5>
                                    <small class="text-muted">
                                        📅 <?php echo date('F j, Y - g:i A', strtotime($entry['entry_date'])); ?>
                                    </small>
                                </div>
                                <div class="card-body">
                                    <p class="card-text"><?php echo nl2br(htmlspecialchars($entry['content'])); ?></p>
                                </div>
                                <div class="card-footer bg-white border-top-0">
                                    <div class="d-flex gap-2">
                                        <a href="?edit=<?php echo $entry['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            ✏️ Edit
                                        </a>
                                        <a 
                                            href="?delete=<?php echo $entry['id']; ?>" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this entry?');"
                                        >
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="text-center my-5 text-muted">
        <p>&copy; 2025 My Journal App</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
