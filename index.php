<?php
include("config.php");
requireLogin();

$user = getCurrentUser();
$conn = getConnection();

$stmt = $conn->prepare("SELECT * FROM shopping_lists WHERE user_id = ? ORDER BY shopping_date DESC, created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$lists = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping List App</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <span class="nav-icon">🛒</span>
                <span class="nav-title">Shopping List</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link active">
                    <span class="nav-link-icon">📋</span>
                    List Aktif
                </a>
                <a href="history.php" class="nav-link">
                    <span class="nav-link-icon">📜</span>
                    Riwayat
                </a>
                <div class="nav-user">
                    <span class="nav-username"><?php echo htmlspecialchars($user['username']); ?></span>
                    <a href="php/logout.php" class="btn-logout" title="Logout">🚪</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <div>
                <h1 class="page-title">Daftar Belanjaan Aktif</h1>
                <p class="page-subtitle">Kelola list belanjaan Anda dengan mudah</p>
            </div>
            <button class="btn btn-primary" onclick="openNewListModal()">
                <span>➕</span> Buat List Baru
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo count($lists); ?></div>
                    <div class="stat-label">List Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <div class="stat-value" id="totalBudget">Rp 0</div>
                    <div class="stat-label">Total Budget</div>
                </div>
            </div>
        </div>

        <div class="lists-grid" id="listsContainer">
            <?php if (empty($lists)): ?>
                <div class="empty-state">
                    <div class="empty-icon">🛒</div>
                    <h3>Belum Ada List Belanjaan</h3>
                    <p>Buat list belanjaan pertama Anda untuk memulai</p>
                    <button class="btn btn-primary" onclick="openNewListModal()">
                        <span>➕</span> Buat List Baru
                    </button>
                </div>
            <?php else: ?>
                <?php foreach ($lists as $list): ?>
                    <div class="list-card" data-list-id="<?php echo $list['id']; ?>">
                        <div class="list-header">
                            <div class="list-title-section">
                                <h3><?php echo htmlspecialchars($list['title']); ?></h3>
                                <span class="list-category"><?php echo htmlspecialchars($list['category']); ?></span>
                            </div>
                            <div class="list-actions">
                                <button class="btn-icon" onclick="viewList(<?php echo $list['id']; ?>)" title="Lihat Detail">
                                    👁️
                                </button>
                                <button class="btn-icon" onclick="deleteList(<?php echo $list['id']; ?>)" title="Hapus">
                                    🗑️
                                </button>
                            </div>
                        </div>
                        <div class="list-date">
                            📅 <?php echo formatDateIndo($list['shopping_date']); ?>
                        </div>
                        <?php if ($list['notes']): ?>
                            <div class="list-notes">
                                📝 <?php echo htmlspecialchars($list['notes']); ?>
                            </div>
                        <?php endif; ?>
                        <div class="list-footer">
                            <div class="list-budget">
                                💰 Budget: <?php echo formatRupiah($list['budget']); ?>
                            </div>
                            <button class="btn btn-secondary btn-sm" onclick="viewList(<?php echo $list['id']; ?>)">
                                Kelola
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div id="newListModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Buat List Belanjaan Baru</h2>
                <button class="modal-close" onclick="closeNewListModal()">&times;</button>
            </div>
            <form id="newListForm" onsubmit="createNewList(event)">
                <div class="form-group">
                    <label for="listTitle">Judul List</label>
                    <input type="text" id="listTitle" name="title" required 
                           placeholder="Contoh: Belanjaan Bulanan">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="listDate">Tanggal Belanja</label>
                        <input type="date" id="listDate" name="date" required 
                               value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="listCategory">Kategori</label>
                        <select id="listCategory" name="category">
                            <option value="Umum">Umum</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Sayuran">Sayuran</option>
                            <option value="Buah">Buah</option>
                            <option value="Daging">Daging</option>
                            <option value="Kesehatan">Kesehatan</option>
                            <option value="Kebersihan">Kebersihan</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="listBudget">Budget (Rp)</label>
                    <input type="number" id="listBudget" name="budget" 
                           placeholder="0" min="0" step="1000">
                </div>
                
                <div class="form-group">
                    <label for="listNotes">Catatan (Opsional)</label>
                    <textarea id="listNotes" name="notes" rows="3" 
                              placeholder="Tambahkan catatan..."></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeNewListModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        Buat List
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>