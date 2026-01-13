<?php
include("config.php");
requireLogin();

$list_id = $_GET['id'] ?? 0;

$conn = getConnection();

$stmt = $conn->prepare("SELECT * FROM shopping_lists WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $list_id, $_SESSION['user_id']);
$stmt->execute();
$list = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$list) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM shopping_items WHERE list_id = ? ORDER BY is_checked ASC, created_at ASC");
$stmt->bind_param("i", $list_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($list['title']); ?> 💖</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .list-detail-header {
            margin-bottom: 2rem;
        }
        
        .list-info-card {
            background: linear-gradient(135deg, #ffffff, #fff0f5);
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: 0 4px 15px rgba(255, 105, 180, 0.2);
            margin-bottom: 1rem;
            border: 2px solid var(--border-color);
        }
        
        .info-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        .info-icon {
            font-size: 1.75rem;
        }
        
        .info-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .info-notes {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border-color);
        }
        
        .list-actions-bar {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .items-section {
            background: linear-gradient(135deg, #ffffff, #fff5f8);
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: 0 4px 15px rgba(255, 105, 180, 0.2);
            border: 2px solid var(--border-color);
        }
        
        .items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .items-header h2 {
            font-size: 1.75rem;
            color: var(--primary-color);
            font-weight: 800;
        }
        
        .items-stats {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .stat-badge {
            padding: 0.625rem 1.25rem;
            background: rgba(255, 182, 193, 0.15);
            border-radius: 2rem;
            font-size: 0.95rem;
            color: var(--text-secondary);
            border: 2px solid var(--border-color);
        }
        
        .stat-badge span {
            font-weight: 800;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .item-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem;
            background: white;
            border-radius: 0.75rem;
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
            border: 2px solid var(--border-color);
        }
        
        .item-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(255, 105, 180, 0.2);
        }
        
        .item-card.checked {
            opacity: 0.65;
            background: rgba(255, 182, 193, 0.05);
        }
        
        .item-card.checked .item-main h4 {
            text-decoration: line-through;
        }
        
        .item-checkbox {
            flex-shrink: 0;
        }
        
        .item-checkbox input[type="checkbox"] {
            display: none;
        }
        
        .item-checkbox label {
            display: block;
            width: 28px;
            height: 28px;
            border: 3px solid var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .item-checkbox input[type="checkbox"]:checked + label {
            background: linear-gradient(135deg, #90ee90, #98fb98);
            border-color: #10b981;
        }
        
        .item-checkbox input[type="checkbox"]:checked + label::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .item-content {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        
        .item-main h4 {
            font-size: 1.1rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .item-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            font-size: 0.85rem;
        }
        
        .item-quantity,
        .item-category {
            color: var(--text-secondary);
        }
        
        .item-priority {
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 700;
        }
        
        .priority-rendah {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
        }
        
        .priority-sedang {
            background: rgba(245, 158, 11, 0.15);
            color: #f59e0b;
        }
        
        .priority-tinggi {
            background: rgba(239, 68, 68, 0.15);
            color: #ef4444;
        }
        
        .item-notes {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
            font-style: italic;
        }
        
        .item-price {
            font-weight: 800;
            color: var(--primary-color);
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .item-actions {
            display: flex;
            gap: 0.5rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="index.php" class="back-btn">← Kembali</a>
                <span class="nav-title"><?php echo htmlspecialchars($list['title']); ?></span>
            </div>
            <div class="nav-menu">
                <div class="nav-user">
                    <span class="nav-username"><?php echo htmlspecialchars($user['username']); ?></span>
                    <a href="php/logout.php" class="btn-logout" title="Logout">🚪</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="list-detail-header">
            <div class="list-info-card">
                <div class="info-row">
                    <div class="info-item">
                        <span class="info-icon">📅</span>
                        <div>
                            <div class="info-label">Tanggal Belanja</div>
                            <div class="info-value"><?php echo formatDateIndo($list['shopping_date']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">📂</span>
                        <div>
                            <div class="info-label">Kategori</div>
                            <div class="info-value"><?php echo htmlspecialchars($list['category']); ?></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">💰</span>
                        <div>
                            <div class="info-label">Budget</div>
                            <div class="info-value"><?php echo formatRupiah($list['budget']); ?></div>
                        </div>
                    </div>
                </div>
                <?php if ($list['notes']): ?>
                    <div class="info-notes">
                        <span class="info-icon">📝</span>
                        <div>
                            <div class="info-label">Catatan</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($list['notes'])); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="list-actions-bar">
                <button class="btn btn-success" onclick="completeList(<?php echo $list_id; ?>)">
                    ✅ Selesai & Simpan ke Riwayat
                </button>
                <button class="btn btn-secondary" onclick="openEditListModal()">
                    ✏️ Edit Info
                </button>
            </div>
        </div>

        <div class="items-section">
            <div class="items-header">
                <h2>💖 Daftar Barang</h2>
                <button class="btn btn-primary" onclick="openAddItemModal()">
                    ➕ Tambah Barang
                </button>
            </div>

            <div class="items-stats">
                <div class="stat-badge">
                    <span id="totalItemsCount"><?php echo count($items); ?></span> Total Barang
                </div>
                <div class="stat-badge">
                    <span id="checkedItemsCount">0</span> Sudah Dibeli
                </div>
                <div class="stat-badge">
                    <span id="totalPrice">Rp 0</span> Total Harga
                </div>
            </div>

            <div class="items-container" id="itemsContainer">
                <?php if (empty($items)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📦</div>
                        <h3>Belum Ada Barang</h3>
                        <p>Tambahkan barang yang ingin Anda beli</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($items as $item): ?>
                        <div class="item-card <?php echo $item['is_checked'] ? 'checked' : ''; ?>" 
                             data-item-id="<?php echo $item['id']; ?>">
                            <div class="item-checkbox">
                                <input type="checkbox" 
                                       id="item-<?php echo $item['id']; ?>"
                                       <?php echo $item['is_checked'] ? 'checked' : ''; ?>
                                       onchange="toggleItem(<?php echo $item['id']; ?>)">
                                <label for="item-<?php echo $item['id']; ?>"></label>
                            </div>
                            
                            <div class="item-content">
                                <div class="item-main">
                                    <h4><?php echo htmlspecialchars($item['item_name']); ?></h4>
                                    <div class="item-meta">
                                        <span class="item-quantity">
                                            📦 <?php echo htmlspecialchars($item['quantity']); ?>
                                        </span>
                                        <span class="item-category">
                                            🏷️ <?php echo htmlspecialchars($item['category']); ?>
                                        </span>
                                        <span class="item-priority priority-<?php echo $item['priority']; ?>">
                                            <?php 
                                            $priorityIcons = ['rendah' => '🟢', 'sedang' => '🟡', 'tinggi' => '🔴'];
                                            echo $priorityIcons[$item['priority']];
                                            echo ' ' . ucfirst($item['priority']); 
                                            ?>
                                        </span>
                                    </div>
                                    <?php if ($item['notes']): ?>
                                        <div class="item-notes">
                                            💭 <?php echo htmlspecialchars($item['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="item-price">
                                    <?php echo formatRupiah($item['price']); ?>
                                </div>
                            </div>
                            
                            <div class="item-actions">
                                <button class="btn-icon" onclick="editItem(<?php echo $item['id']; ?>)" title="Edit">
                                    ✏️
                                </button>
                                <button class="btn-icon" onclick="deleteItem(<?php echo $item['id']; ?>)" title="Hapus">
                                    🗑️
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal: Add Item -->
    <div id="addItemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>➕ Tambah Barang</h2>
                <button class="modal-close" onclick="closeAddItemModal()">&times;</button>
            </div>
            <form id="addItemForm" onsubmit="addItem(event)">
                <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                
                <div class="form-group">
                    <label for="itemName">💖 Nama Barang *</label>
                    <input type="text" id="itemName" name="item_name" required 
                           placeholder="Contoh: Beras 5kg">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="itemQuantity">📦 Jumlah</label>
                        <input type="text" id="itemQuantity" name="quantity" 
                               placeholder="Contoh: 2 kg" value="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="itemPrice">💰 Harga (Rp)</label>
                        <input type="number" id="itemPrice" name="price" 
                               placeholder="0" min="0" step="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="itemCategory">🏷️ Kategori</label>
                        <select id="itemCategory" name="category">
                            <option value="Lainnya">Lainnya</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Sayuran">Sayuran</option>
                            <option value="Buah">Buah</option>
                            <option value="Daging">Daging</option>
                            <option value="Ikan">Ikan</option>
                            <option value="Bumbu">Bumbu</option>
                            <option value="Snack">Snack</option>
                            <option value="Susu & Telur">Susu & Telur</option>
                            <option value="Roti">Roti</option>
                            <option value="Kebersihan">Kebersihan</option>
                            <option value="Kesehatan">Kesehatan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="itemPriority">⚡ Prioritas</label>
                        <select id="itemPriority" name="priority">
                            <option value="rendah">🟢 Rendah</option>
                            <option value="sedang" selected>🟡 Sedang</option>
                            <option value="tinggi">🔴 Tinggi</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="itemNotes">📝 Catatan (Opsional)</label>
                    <textarea id="itemNotes" name="notes" rows="2" 
                              placeholder="Tambahkan catatan..."></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAddItemModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💖 Tambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Item -->
    <div id="editItemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Barang</h2>
                <button class="modal-close" onclick="closeEditItemModal()">&times;</button>
            </div>
            <form id="editItemForm" onsubmit="updateItem(event)">
                <input type="hidden" id="editItemId" name="item_id">
                
                <div class="form-group">
                    <label for="editItemName">💖 Nama Barang *</label>
                    <input type="text" id="editItemName" name="item_name" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editItemQuantity">📦 Jumlah</label>
                        <input type="text" id="editItemQuantity" name="quantity">
                    </div>
                    
                    <div class="form-group">
                        <label for="editItemPrice">💰 Harga (Rp)</label>
                        <input type="number" id="editItemPrice" name="price" min="0" step="100">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editItemCategory">🏷️ Kategori</label>
                        <select id="editItemCategory" name="category">
                            <option value="Lainnya">Lainnya</option>
                            <option value="Makanan">Makanan</option>
                            <option value="Minuman">Minuman</option>
                            <option value="Sayuran">Sayuran</option>
                            <option value="Buah">Buah</option>
                            <option value="Daging">Daging</option>
                            <option value="Ikan">Ikan</option>
                            <option value="Bumbu">Bumbu</option>
                            <option value="Snack">Snack</option>
                            <option value="Susu & Telur">Susu & Telur</option>
                            <option value="Roti">Roti</option>
                            <option value="Kebersihan">Kebersihan</option>
                            <option value="Kesehatan">Kesehatan</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="editItemPriority">⚡ Prioritas</label>
                        <select id="editItemPriority" name="priority">
                            <option value="rendah">🟢 Rendah</option>
                            <option value="sedang">🟡 Sedang</option>
                            <option value="tinggi">🔴 Tinggi</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editItemNotes">📝 Catatan</label>
                    <textarea id="editItemNotes" name="notes" rows="2"></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditItemModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💖 Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit List -->
    <div id="editListModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>✏️ Edit Info List</h2>
                <button class="modal-close" onclick="closeEditListModal()">&times;</button>
            </div>
            <form id="editListForm" onsubmit="updateList(event)">
                <input type="hidden" name="list_id" value="<?php echo $list_id; ?>">
                
                <div class="form-group">
                    <label for="editListTitle">💖 Judul List</label>
                    <input type="text" id="editListTitle" name="title" required 
                           value="<?php echo htmlspecialchars($list['title']); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editListDate">📅 Tanggal Belanja</label>
                        <input type="date" id="editListDate" name="date" required 
                               value="<?php echo $list['shopping_date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="editListCategory">📂 Kategori</label>
                        <select id="editListCategory" name="category">
                            <option value="Umum" <?php echo $list['category'] === 'Umum' ? 'selected' : ''; ?>>Umum</option>
                            <option value="Makanan" <?php echo $list['category'] === 'Makanan' ? 'selected' : ''; ?>>Makanan</option>
                            <option value="Minuman" <?php echo $list['category'] === 'Minuman' ? 'selected' : ''; ?>>Minuman</option>
                            <option value="Sayuran" <?php echo $list['category'] === 'Sayuran' ? 'selected' : ''; ?>>Sayuran</option>
                            <option value="Buah" <?php echo $list['category'] === 'Buah' ? 'selected' : ''; ?>>Buah</option>
                            <option value="Daging" <?php echo $list['category'] === 'Daging' ? 'selected' : ''; ?>>Daging</option>
                            <option value="Kesehatan" <?php echo $list['category'] === 'Kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                            <option value="Kebersihan" <?php echo $list['category'] === 'Kebersihan' ? 'selected' : ''; ?>>Kebersihan</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="editListBudget">💰 Budget (Rp)</label>
                    <input type="number" id="editListBudget" name="budget" 
                           value="<?php echo $list['budget']; ?>" min="0" step="1000">
                </div>
                
                <div class="form-group">
                    <label for="editListNotes">📝 Catatan</label>
                    <textarea id="editListNotes" name="notes" rows="3"><?php echo htmlspecialchars($list['notes']); ?></textarea>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeEditListModal()">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        💖 Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const listId = <?php echo $list_id; ?>;
        const items = <?php echo json_encode($items); ?>;
    </script>
    <script src="js/list-detail.js"></script>
</body>
</html>