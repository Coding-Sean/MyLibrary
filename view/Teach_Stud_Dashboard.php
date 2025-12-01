<?php
// filepath: c:\xampp\htdocs\MyLibrary\view\Teach_Stud_Dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['Teacher','Student'])) {
    header('Location: Log_In.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/LibrarianModel.php';
require_once __DIR__ . '/../model/StudentTeacherModel.php';
require_once __DIR__ . '/../includes/messages.php';

$db = (new Database())->getConnection();
$librarianModel = new LibrarianModel($db);
$studentTeacherModel = new StudentTeacherModel($db);

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// ✅ FIX: Define missing variables
$isStudent = ($user_role === 'Student');
$borrowLimit = 3; // Student limit
$borrowedCount = $studentTeacherModel->getUserBorrowedCount($user_id);
$currentBorrowed = $borrowedCount; // Current borrowed count

// Get data
$books = $librarianModel->getAllBooks();
$borrowedBooks = $studentTeacherModel->getUserBorrowedBooks($user_id);
$reservations = $studentTeacherModel->getUserReservations($user_id);
$penalties = $studentTeacherModel->getUserPenalties($user_id);
$totalUnpaid = $studentTeacherModel->getTotalUnpaidPenalties($user_id);
$totalUnpaidPenalties = $totalUnpaid; // ✅ FIX: Add this variable
$clearanceStatus = $studentTeacherModel->getUserClearanceStatus($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLibrary - <?= $_SESSION['user_role'] ?> Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/stud_teacher.css">
</head>
<body>
    <?php echo displayMessage(); ?>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <h1 class="header-title">MyLibrary - <?= $_SESSION['user_role'] ?> Dashboard</h1>
            <div class="header-right">
                <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="../controller/LogoutController.php" class="btn-logout">LOG OUT</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <?php if ($isStudent): ?>
            <div class="alert-warning">
                <strong>Student Borrowing Rules:</strong> You can borrow up to 3 books per semester. You must return all books to be cleared, otherwise you must pay the book price.
            </div>
        <?php else: ?>
            <div class="alert-info">
                <strong>Teacher Borrowing Rules:</strong> You can borrow unlimited books. You must return all books at semester end for clearance.
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- MyLibrary Status Card -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h2 class="card-title">MyLibrary Status</h2>
                    <div class="card-content">
                        <div class="status-info">
                            <div class="status-item">
                                <span class="status-label">Role:</span>
                                <span class="status-value"><?= htmlspecialchars($_SESSION['user_role']) ?></span>
                            </div>
                            <div class="status-item">
                                <span class="status-label">Books Borrowed:</span>
                                <span class="status-value"><?= $currentBorrowed ?> / <?= $isStudent ? $borrowLimit : 'Unlimited' ?></span>
                            </div>
                            <?php if ($isStudent): ?>
                                <div class="status-item">
                                    <span class="status-label">Available Slots:</span>
                                    <span class="status-value"><?= $borrowLimit - $currentBorrowed ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($totalUnpaidPenalties > 0): ?>
                                <div class="status-item">
                                    <span class="status-label">Unpaid Penalties:</span>
                                    <span class="status-value text-danger">₱<?= number_format($totalUnpaidPenalties, 2) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if ($clearanceStatus): ?>
                                <div class="status-item">
                                    <span class="status-label">Clearance Status:</span>
                                    <span class="status-value <?= $clearanceStatus['clearanceStatus'] === 'Cleared' ? 'text-success' : 'text-warning' ?>">
                                        <?= htmlspecialchars($clearanceStatus['clearanceStatus']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Currently Borrowed Books Card -->
            <div class="col-md-6">
                <div class="dashboard-card">
                    <h2 class="card-title">Currently Borrowed Books</h2>
                    <div class="card-content">
                        <?php if (empty($borrowedBooks)): ?>
                            <p class="text-muted">No books currently borrowed.</p>
                        <?php else: ?>
                            <div class="borrowed-list">
                                <?php foreach ($borrowedBooks as $borrowed): ?>
                                    <div class="borrowed-item">
                                        <div class="borrowed-title"><?= htmlspecialchars($borrowed['title']) ?> by <?= htmlspecialchars($borrowed['author']) ?></div>
                                        <div class="borrowed-date">
                                            Borrowed: <?= date('M d, Y', strtotime($borrowed['borrowDate'])) ?><br>
                                            Due: <?= date('M d, Y', strtotime($borrowed['dueDate'])) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Reservations Card -->
        <?php if (!empty($reservations)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">My Reservations</h2>
                    <div class="card-content">
                        <div class="borrowed-list">
                            <?php foreach ($reservations as $reservation): ?>
                                <div class="borrowed-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="borrowed-title"><?= htmlspecialchars($reservation['title']) ?> by <?= htmlspecialchars($reservation['author']) ?></div>
                                            <div class="borrowed-date">
                                                Reserved: <?= date('M d, Y', strtotime($reservation['reservationDate'])) ?><br>
                                                Status: <span class="badge <?= $reservation['status'] === 'Approved' ? 'bg-success' : ($reservation['status'] === 'Rejected' ? 'bg-danger' : 'bg-warning') ?>">
                                                    <?= htmlspecialchars($reservation['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if ($reservation['status'] === 'Pending'): ?>
                                            <button class="btn btn-sm btn-danger" onclick="cancelReservation(<?= $reservation['reserve_id'] ?>)">Cancel</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Penalties Card -->
        <?php if (!empty($penalties)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Penalties</h2>
                    <div class="card-content">
                        <div class="borrowed-list">
                            <?php foreach ($penalties as $penalty): ?>
                                <div class="borrowed-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="borrowed-title"><?= htmlspecialchars($penalty['title']) ?></div>
                                            <div class="borrowed-date">
                                                Amount: ₱<?= number_format($penalty['amount'], 2) ?><br>
                                                Issued: <?= date('M d, Y', strtotime($penalty['issueDate'])) ?><br>
                                                Status: <span class="badge <?= $penalty['status'] === 'Paid' ? 'bg-success' : ($penalty['status'] === 'Waived' ? 'bg-info' : 'bg-danger') ?>">
                                                    <?= htmlspecialchars($penalty['status']) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search and Reserve Books Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="search-section">
                    <h2 class="search-header">Search and Reserve Books</h2>
                    
                    <!-- Search Bar -->
                    <div class="search-bar">
                        <input type="text" class="search-input" id="searchInput" placeholder="Search books by title, author, or category...">
                        <button class="btn-search" onclick="searchBooks()">SEARCH</button>
                    </div>

                    <!-- Book List -->
                    <div class="book-list" id="bookList">
                        <?php if (empty($books)): ?>
                            <p class="text-muted text-center py-4">No books available at the moment.</p>
                        <?php else: ?>
                            <?php foreach ($books as $book): ?>
                                <div class="book-item" data-title="<?= htmlspecialchars($book['title']) ?>" data-author="<?= htmlspecialchars($book['author']) ?>">
                                    <span class="book-title">
                                        <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?>
                                        <?php if (!empty($book['category'])): ?>
                                            <small class="text-white-50">(<?= htmlspecialchars($book['category']) ?>)</small>
                                        <?php endif; ?>
                                    </span>
                                    <div class="book-actions">
                                        <?php if ($book['status'] === 'Available' && $book['copies'] > 0): ?>
                                            <span class="badge-available">Available (<?= $book['copies'] ?> copies)</span>
                                            <?php 
                                            $canReserve = true;
                                            if ($isStudent && $currentBorrowed >= $borrowLimit) {
                                                $canReserve = false;
                                            }
                                            ?>
                                            <button class="btn-reserve" 
                                                    onclick="reserveBook(<?= $book['book_id'] ?>)"
                                                    <?= !$canReserve ? 'disabled' : '' ?>>
                                                RESERVE
                                            </button>
                                        <?php else: ?>
                                            <span class="badge-unavailable">Unavailable</span>
                                            <button class="btn-reserve" disabled>RESERVE</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function searchBooks() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const bookItems = document.querySelectorAll('.book-item');

            bookItems.forEach(item => {
                const title = item.getAttribute('data-title').toLowerCase();
                const author = item.getAttribute('data-author').toLowerCase();
                
                if (title.includes(searchTerm) || author.includes(searchTerm)) {
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

        function reserveBook(bookId) {
            if (confirm('Are you sure you want to reserve this book? Staff will review your reservation.')) {
                location.href = '../controller/ReservationController.php?action=reserve&book_id=' + bookId;
            }
        }

        function cancelReservation(reserveId) {
            if (confirm('Are you sure you want to cancel this reservation?')) {
                location.href = '../controller/ReservationController.php?action=cancel&reserve_id=' + reserveId;
            }
        }
    </script>
</body>
</html>