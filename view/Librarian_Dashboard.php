<?php
// filepath: c:\xampp\htdocs\MyLibrary\view\Librarian_Dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Librarian') {
    header('Location: Log_In.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/LibrarianModel.php';
require_once __DIR__ . '/../includes/messages.php';
require_once __DIR__ . '/../includes/confirm_modal.php';

$db = (new Database())->getConnection();
$librarianModel = new LibrarianModel($db);
$books = $librarianModel->getAllBooks();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLibrary - Librarian Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/librarian.css">
</head>
<body>
    <?php echo displayMessage(); ?>
    <?php echo getConfirmModal(); ?>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <h1 class="header-title">MyLibrary - Librarian Dashboard</h1>
            <div class="header-right">
                <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="../controller/LogoutController.php" class="btn-logout">LOG OUT</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <div class="row g-4">
            <!-- MyLibrary Inventory Card -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h2 class="card-title">MyLibrary Inventory</h2>
                    <div class="card-content">
                        <h3 class="text-center" style="font-size: 2.5rem; color: #2c3e50;">
                            <?= count($books) ?> Books
                        </h3>
                        <p class="text-center text-muted">Total books in library</p>
                    </div>
                </div>
            </div>

            <!-- Currently Borrowed Books Card -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h2 class="card-title">Currently Borrowed Books</h2>
                    <div class="card-content">
                        <p class="text-muted">No books currently borrowed.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Book Management Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card book-management-card">
                    <h2 class="card-title">Book Management</h2>
                    <div class="card-content">
                        <!-- Search Bar -->
                        <div class="search-container">
                            <input type="text" class="search-input" id="searchInput" placeholder="Search books...">
                            <button class="btn-search" onclick="searchBooks()">SEARCH</button>
                            <a href="Librarian_Functions/Add_Book.php" class="btn-add">ADD</a>
                        </div>

                        <!-- Book List -->
                        <div class="book-list mt-3" id="bookList">
                            <?php if (empty($books)): ?>
                                <p class="text-muted text-center py-4">No books available. Click ADD to add a new book.</p>
                            <?php else: ?>
                                <?php foreach ($books as $book): ?>
                                    <div class="book-item">
                                        <span class="book-title">
                                            <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?>
                                        </span>
                                        <div class="book-actions">
                                            <span class="badge-available"><?= htmlspecialchars($book['status']) ?></span>
                                            <button class="btn-edit" onclick="location.href='Librarian_Functions/Edit_Book.php?id=<?= $book['book_id'] ?>'">Edit</button>
                                            <button class="btn-archive" onclick="archiveBook(<?= $book['book_id'] ?>)">Archive</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function archiveBook(bookId) {
            customConfirm('Are you sure you want to archive this book?', function() {
                window.location.href = '../controller/LibrarianController.php?action=delete&id=' + bookId;
            });
        }

        function searchBooks() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const bookItems = document.querySelectorAll('.book-item');

            bookItems.forEach(item => {
                const title = item.querySelector('.book-title').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Allow search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBooks();
            }
        });
    </script>
</body>
</html>