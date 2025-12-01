<?php
// filepath: c:\xampp\htdocs\MyLibrary\view\Staff_Dashboard.php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Staff') {
    header('Location: Log_In.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../model/LibrarianModel.php';
require_once __DIR__ . '/../model/StaffModel.php';
require_once __DIR__ . '/../includes/messages.php';
require_once __DIR__ . '/../includes/confirm_modal.php';

$db = (new Database())->getConnection();
$librarianModel = new LibrarianModel($db);
$staffModel = new StaffModel($db);

$books = $librarianModel->getAllBooks();
$borrowers = $staffModel->getAllBorrowers();
$borrowedBooks = $staffModel->getAllBorrowedBooks();
$pendingReservations = $staffModel->getPendingReservations();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MyLibrary - Staff Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/staff.css">
</head>
<body>
    <?php echo displayMessage(); ?>
    <?php echo getConfirmModal(); ?>

    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-content">
            <h1 class="header-title">MyLibrary - Staff Dashboard</h1>
            <div class="header-right">
                <span class="welcome-text">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="../controller/LogoutController.php" class="btn-logout">LOG OUT</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-container">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

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
                        <h3 class="text-center" style="font-size: 2.5rem; color: #2c3e50;">
                            <?= count($borrowedBooks) ?>
                        </h3>
                        <p class="text-center text-muted">Books currently borrowed</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Reservations Card -->
        <?php if (!empty($pendingReservations)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Pending Reservations (<?= count($pendingReservations) ?>)</h2>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Book</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReservations as $reservation): ?>
                                        <tr>
                                            <td>
                                                <strong><?= htmlspecialchars($reservation['name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($reservation['role']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($reservation['title']) ?><br>
                                                <small class="text-muted">by <?= htmlspecialchars($reservation['author']) ?></small>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($reservation['reservationDate'])) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="approveReservation(<?= $reservation['reserve_id'] ?>, <?= $reservation['user_id'] ?>, <?= $reservation['book_id'] ?>)">
                                                    Approve
                                                </button>
                                                <button class="btn btn-sm btn-danger" 
                                                        onclick="rejectReservation(<?= $reservation['reserve_id'] ?>)">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Borrower Management Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card borrower-management-card">
                    <h2 class="card-title">Borrower Management</h2>
                    <div class="card-content">
                        <!-- Search Bar -->
                        <div class="search-container">
                            <input type="text" class="search-input" id="searchInput" placeholder="Search borrowers by name or email...">
                            <button class="btn-search" onclick="searchBorrowers()">SEARCH</button>
                        </div>

                        <!-- Borrower List -->
                        <div class="borrower-list mt-3" id="borrowerList">
                            <?php if (empty($borrowers)): ?>
                                <p class="text-muted text-center py-4">No registered borrowers at the moment.</p>
                            <?php else: ?>
                                <?php foreach ($borrowers as $borrower): ?>
                                    <div class="borrower-item" data-name="<?= htmlspecialchars($borrower['name']) ?>" data-email="<?= htmlspecialchars($borrower['email']) ?>">
                                        <div class="borrower-info-container">
                                            <span class="borrower-info">
                                                <?= htmlspecialchars($borrower['name']) ?> (<?= htmlspecialchars($borrower['role']) ?>)
                                                <?php if ($borrower['unpaid_penalties'] > 0): ?>
                                                    <span class="badge bg-danger ms-2">₱<?= number_format($borrower['unpaid_penalties'], 2) ?> penalty</span>
                                                <?php endif; ?>
                                                <?php if ($borrower['borrowed_count'] > 0): ?>
                                                    <span class="badge bg-info ms-2"><?= $borrower['borrowed_count'] ?> borrowed</span>
                                                <?php endif; ?>
                                            </span>
                                            <small class="text-white-50 d-block"><?= htmlspecialchars($borrower['email']) ?></small>
                                        </div>
                                        <div class="borrower-actions">
                                            <button class="btn-borrow" onclick="handleBorrow(<?= $borrower['user_id'] ?>, '<?= htmlspecialchars($borrower['name']) ?>', '<?= htmlspecialchars($borrower['role']) ?>')">borrow</button>
                                            <button class="btn-return" onclick="handleReturn(<?= $borrower['user_id'] ?>, '<?= htmlspecialchars($borrower['name']) ?>')">return</button>
                                            <button class="btn-penalty" onclick="handlePenalty(<?= $borrower['user_id'] ?>, '<?= htmlspecialchars($borrower['name']) ?>')">penalty</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Currently Borrowed Books Details -->
        <?php if (!empty($borrowedBooks)): ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <h2 class="card-title">Currently Borrowed Books Details</h2>
                    <div class="card-content">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Borrower</th>
                                        <th>Book</th>
                                        <th>Borrow Date</th>
                                        <th>Due Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($borrowedBooks as $borrowed): ?>
                                        <?php 
                                        $isOverdue = strtotime($borrowed['dueDate']) < strtotime(date('Y-m-d'));
                                        ?>
                                        <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
                                            <td>
                                                <strong><?= htmlspecialchars($borrowed['borrower_name']) ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($borrowed['role']) ?></small>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($borrowed['title']) ?><br>
                                                <small class="text-muted">by <?= htmlspecialchars($borrowed['author']) ?></small>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($borrowed['borrowDate'])) ?></td>
                                            <td>
                                                <?= date('M d, Y', strtotime($borrowed['dueDate'])) ?>
                                                <?php if ($isOverdue): ?>
                                                    <br><span class="badge bg-danger">OVERDUE</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning"><?= htmlspecialchars($borrowed['status']) ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="processReturn(<?= $borrowed['borrow_id'] ?>)">
                                                    Return
                                                </button>
                                                <?php if ($isOverdue): ?>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="addPenalty(<?= $borrowed['borrow_id'] ?>)">
                                                        Add Penalty
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Borrow Modal -->
    <div class="modal fade" id="borrowModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Borrow Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="userInfo"></div>
                    <form id="borrowForm" method="POST" action="../controller/StaffController.php?action=borrow">
                        <input type="hidden" name="user_id" id="borrow_user_id">
                        <input type="hidden" name="reserve_id" value="0">
                        
                        <div class="mb-3">
                            <label for="book_id" class="form-label">Select Book</label>
                            <select class="form-control" name="book_id" id="book_id" required>
                                <option value="">-- Select a book --</option>
                                <?php foreach ($books as $book): ?>
                                    <?php if ($book['status'] === 'Available' && $book['copies'] > 0): ?>
                                        <option value="<?= $book['book_id'] ?>">
                                            <?= htmlspecialchars($book['title']) ?> by <?= htmlspecialchars($book['author']) ?> (<?= $book['copies'] ?> available)
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Confirm Borrow</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Return Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="returnUserInfo"></div>
                    <div id="returnBooksList"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Penalty Modal -->
    <div class="modal fade" id="penaltyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Penalties</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="penaltyUserInfo"></div>
                    <div id="penaltyList"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function searchBorrowers() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const borrowerItems = document.querySelectorAll('.borrower-item');

            borrowerItems.forEach(item => {
                const name = item.getAttribute('data-name').toLowerCase();
                const email = item.getAttribute('data-email').toLowerCase();
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        }

        document.getElementById('searchInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchBorrowers();
            }
        });

        function handleBorrow(userId, userName, userRole) {
            fetch(`../controller/StaffController.php?action=get_user_details&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    let userInfoHtml = `<div class="alert alert-info">
                        <strong>${userName}</strong> (${userRole})<br>
                        Books Borrowed: ${data.borrowed_books.length}`;
                    
                    if (!data.can_borrow) {
                        userInfoHtml += `<br><span class="text-danger"><strong>Cannot Borrow:</strong> ${data.borrow_reason}</span>`;
                        document.getElementById('borrowForm').querySelector('button[type="submit"]').disabled = true;
                    } else {
                        userInfoHtml += `<br><span class="text-success">Eligible to borrow</span>`;
                        document.getElementById('borrowForm').querySelector('button[type="submit"]').disabled = false;
                    }
                    
                    userInfoHtml += `</div>`;
                    
                    document.getElementById('userInfo').innerHTML = userInfoHtml;
                    document.getElementById('borrow_user_id').value = userId;
                    
                    const modal = new bootstrap.Modal(document.getElementById('borrowModal'));
                    modal.show();
                })
                .catch(error => {
                    alert('Error loading user details');
                    console.error(error);
                });
        }

        function handleReturn(userId, userName) {
            fetch(`../controller/StaffController.php?action=get_user_details&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    let returnUserInfo = `<div class="alert alert-info">
                        <strong>${userName}</strong><br>
                        Currently Borrowed: ${data.borrowed_books.length} book(s)
                    </div>`;
                    
                    let booksList = '';
                    if (data.borrowed_books.length === 0) {
                        booksList = '<p class="text-muted">No borrowed books to return.</p>';
                    } else {
                        booksList = '<div class="list-group">';
                        data.borrowed_books.forEach(book => {
                            const isOverdue = new Date(book.dueDate) < new Date();
                            booksList += `
                                <div class="list-group-item ${isOverdue ? 'list-group-item-danger' : ''}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong>${book.title}</strong><br>
                                            <small>Due: ${new Date(book.dueDate).toLocaleDateString()}</small>
                                            ${isOverdue ? '<br><span class="badge bg-danger">OVERDUE</span>' : ''}
                                        </div>
                                        <button class="btn btn-sm btn-success" onclick="processReturn(${book.borrow_id})">Return</button>
                                    </div>
                                </div>
                            `;
                        });
                        booksList += '</div>';
                    }
                    
                    document.getElementById('returnUserInfo').innerHTML = returnUserInfo;
                    document.getElementById('returnBooksList').innerHTML = booksList;
                    
                    const modal = new bootstrap.Modal(document.getElementById('returnModal'));
                    modal.show();
                })
                .catch(error => {
                    alert('Error loading borrowed books');
                    console.error(error);
                });
        }

        function handlePenalty(userId, userName) {
            fetch(`../controller/StaffController.php?action=get_user_details&user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    let penaltyUserInfo = `<div class="alert alert-info">
                        <strong>${userName}</strong><br>
                        Total Penalties: ${data.penalties.length}
                    </div>`;
                    
                    let penaltyList = '';
                    if (data.penalties.length === 0) {
                        penaltyList = '<p class="text-muted">No penalties.</p>';
                    } else {
                        const totalUnpaid = data.penalties
                            .filter(p => p.status === 'Unpaid')
                            .reduce((sum, p) => sum + parseFloat(p.amount), 0);
                        
                        if (totalUnpaid > 0) {
                            penaltyList += `<div class="alert alert-danger">Total Unpaid: ₱${totalUnpaid.toFixed(2)}</div>`;
                        }
                        
                        penaltyList += '<div class="list-group">';
                        data.penalties.forEach(penalty => {
                            let statusBadge = '';
                            let actionButtons = '';
                            
                            if (penalty.status === 'Unpaid') {
                                statusBadge = '<span class="badge bg-danger">Unpaid</span>';
                                actionButtons = `
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-success" onclick="markAsPaid(${penalty.penalty_id})">Mark as Paid</button>
                                        <button class="btn btn-sm btn-warning" onclick="waivePenalty(${penalty.penalty_id})">Waive Penalty</button>
                                    </div>
                                `;
                            } else if (penalty.status === 'Paid') {
                                statusBadge = '<span class="badge bg-success">Paid</span>';
                            } else if (penalty.status === 'Waived') {
                                statusBadge = '<span class="badge bg-info">Waived</span>';
                            }
                            
                            penaltyList += `
                                <div class="list-group-item">
                                    <div>
                                        <strong>${penalty.title}</strong><br>
                                        <small>Amount: ₱${parseFloat(penalty.amount).toFixed(2)}</small><br>
                                        <small>Date: ${new Date(penalty.issueDate).toLocaleDateString()}</small><br>
                                        ${statusBadge}
                                        ${actionButtons}
                                    </div>
                                </div>
                            `;
                        });
                        penaltyList += '</div>';
                    }
                    
                    document.getElementById('penaltyUserInfo').innerHTML = penaltyUserInfo;
                    document.getElementById('penaltyList').innerHTML = penaltyList;
                    
                    const modal = new bootstrap.Modal(document.getElementById('penaltyModal'));
                    modal.show();
                })
                .catch(error => {
                    alert('Error loading penalties');
                    console.error(error);
                });
        }

        function processReturn(borrowId) {
            customConfirm('Confirm book return?', function() {
                window.location.href = `../controller/StaffController.php?action=return&borrow_id=${borrowId}`;
            });
        }

        function addPenalty(borrowId) {
            customConfirm('Add penalty for this unreturned book?', function() {
                window.location.href = `../controller/StaffController.php?action=add_penalty&borrow_id=${borrowId}`;
            });
        }

        function approveReservation(reserveId, userId, bookId) {
            customConfirm('Approve this reservation and issue the book?', function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../controller/StaffController.php?action=borrow';
                
                const userIdInput = document.createElement('input');
                userIdInput.type = 'hidden';
                userIdInput.name = 'user_id';
                userIdInput.value = userId;
                
                const bookIdInput = document.createElement('input');
                bookIdInput.type = 'hidden';
                bookIdInput.name = 'book_id';
                bookIdInput.value = bookId;
                
                const reserveIdInput = document.createElement('input');
                reserveIdInput.type = 'hidden';
                reserveIdInput.name = 'reserve_id';
                reserveIdInput.value = reserveId;
                
                form.appendChild(userIdInput);
                form.appendChild(bookIdInput);
                form.appendChild(reserveIdInput);
                document.body.appendChild(form);
                form.submit();
            });
        }

        function rejectReservation(reserveId) {
            customConfirm('Reject this reservation?', function() {
                window.location.href = `../controller/StaffController.php?action=reject_reservation&reserve_id=${reserveId}`;
            });
        }

        function waivePenalty(penaltyId) {
            customConfirm('Are you sure you want to waive this penalty? This action cannot be undone.', function() {
                window.location.href = `../controller/StaffController.php?action=waive_penalty&penalty_id=${penaltyId}`;
            });
        }

        function markAsPaid(penaltyId) {
            customConfirm('Mark this penalty as paid?', function() {
                window.location.href = `../controller/StaffController.php?action=mark_paid&penalty_id=${penaltyId}`;
            });
        }
    </script>
</body>
</html>