<?php
include("config.php");
requireLogin();

$user = getCurrentUser();
$conn = getConnection();

$stmt = $conn->prepare("SELECT * FROM shopping_history WHERE user_id = ? ORDER BY completed_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$histories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Belanjaan 💖</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .history-card {
            background: linear-gradient(135deg, #ffffff, #fff5f8);
            border-radius: 1rem;
            padding: 1.75rem;
            box-shadow: 0 4px 15px rgba(255, 105, 180, 0.2);
            margin-bottom: 1.5rem;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }
        
        .history-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(255, 105, 180, 0.3);
            border-color: var(--primary-color);
        }
        
        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        
        .history-title-section h3 {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }
        
        .history-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.95rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }
        
        .history-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .history-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            padding: 1rem;
            background: rgba(255, 182, 193, 0.1);
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .history-stat-item {
            text-align: center;
        }
        
        .history-stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .history-stat-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        
        .budget-comparison {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            background: rgba(255, 182, 193, 0.1);
            border-radius: 0.75rem;
            margin-bottom: 1rem;
        }
        
        .budget-bar {
            flex: 1;
            height: 10px;
            background: var(--border-color);
            border-radius: 5px;
            overflow: hidden;
        }
        
        .budget-fill {
            height: 100%;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .budget-under {
            background: linear-gradient(135deg, #90ee90, #98fb98);
        }
        
        .budget-over {
            background: linear-gradient(135deg, #ff6b9d, #ff1493);
        }
        
        .history-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid var(--border-color);
        }
        
        .item-card-history {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 0.75rem;
            margin-bottom: 0.75rem;
            border: 2px solid var(--border-color);
        }
        
        .item-card-history.checked {
            opacity: 0.7;
        }
        
        .item-card-history.checked .item-main h4 {
            text-decoration: line-through;
        }
        
        .item-checkbox-history {
            flex-shrink: 0;
            width: 24px;
            height: 24px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .item-checkbox-history.checked {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
            font-weight: bold;
        }
        
        .item-content-history {
            flex: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        
        .item-main h4 {
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
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
            padding: 0.125rem 0.5rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .priority-rendah {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        
        .priority-sedang {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }
        
        .priority-tinggi {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        
        .item-price-history {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <span class="nav-icon">🛒💖</span>
                <span class="nav-title">Shopping List</span>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">
                    <span class="nav-link-icon">📋</span>
                    List Aktif
                </a>
                <a href="history.php" class="nav-link active">
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
                <h1 class="page-title">📜 Riwayat Belanjaan</h1>
                <p class="page-subtitle">List belanjaan yang sudah selesai</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📜</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo count($histories); ?></div>
                    <div class="stat-label">Total Riwayat</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php 
                        $total_spent_all = array_sum(array_column($histories, 'total_spent'));
                        echo formatRupiah($total_spent_all); 
                        ?>
                    </div>
                    <div class="stat-label">Total Pengeluaran</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📦</div>
                <div class="stat-info">
                    <div class="stat-value">
                        <?php echo array_sum(array_column($histories, 'total_items')); ?>
                    </div>
                    <div class="stat-label">Total Barang</div>
                </div>
            </div>
        </div>

        <?php if (empty($histories)): ?>
            <div class="empty-state">
                <div class="empty-icon">📜</div>
                <h3>Belum Ada Riwayat</h3>
                <p>Riwayat belanjaan yang sudah selesai akan muncul di sini</p>
                <a href="index.php" class="btn btn-primary">
                    <span>📋</span> Lihat List Aktif
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($histories as $history): ?>
                <?php
                $items = json_decode($history['items_json'], true);
                $budget_percentage = $history['budget'] > 0 ? ($history['total_spent'] / $history['budget']) * 100 : 0;
                ?>
                <div class="history-card">
                    <div class="history-header">
                        <div class="history-title-section">
                            <h3><?php echo htmlspecialchars($history['title']); ?></h3>
                            <span class="list-category"><?php echo htmlspecialchars($history['category']); ?></span>
                        </div>
                        <button class="btn-icon" onclick="deleteHistory(<?php echo $history['id']; ?>)" title="Hapus">
                            🗑️
                        </button>
                    </div>
                    
                    <div class="history-meta">
                        <span>📅 Tanggal: <?php echo formatDateIndo($history['shopping_date']); ?></span>
                        <span>✅ Selesai: <?php echo formatDateIndo($history['completed_date']); ?></span>
                    </div>
                    
                    <?php if ($history['notes']): ?>
                        <div class="list-notes">
                            📝 <?php echo htmlspecialchars($history['notes']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="history-stats">
                        <div class="history-stat-item">
                            <div class="history-stat-value"><?php echo $history['total_items']; ?></div>
                            <div class="history-stat-label">Barang</div>
                        </div>
                        <div class="history-stat-item">
                            <div class="history-stat-value"><?php echo formatRupiah($history['total_spent']); ?></div>
                            <div class="history-stat-label">Total Belanja</div>
                        </div>
                        <div class="history-stat-item">
                            <div class="history-stat-value"><?php echo formatRupiah($history['budget']); ?></div>
                            <div class="history-stat-label">Budget</div>
                        </div>
                    </div>
                    
                    <?php if ($history['budget'] > 0): ?>
                        <div class="budget-comparison">
                            <span style="font-size: 0.85rem; color: var(--text-secondary); min-width: 80px;">
                                <?php echo number_format($budget_percentage, 1); ?>% dari budget
                            </span>
                            <div class="budget-bar">
                                <div class="budget-fill <?php echo $budget_percentage <= 100 ? 'budget-under' : 'budget-over'; ?>" 
                                     style="width: <?php echo min($budget_percentage, 100); ?>%"></div>
                            </div>
                            <?php if ($budget_percentage <= 100): ?>
                                <span style="font-size: 1.25rem;">✅</span>
                            <?php else: ?>
                                <span style="font-size: 1.25rem;">⚠️</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="history-actions">
                        <button class="btn btn-secondary btn-sm" onclick="viewHistoryDetails(<?php echo $history['id']; ?>)">
                            👁️ Lihat Detail
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="historyDetailModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2 id="modalTitle">Detail Riwayat</h2>
                <button class="modal-close" onclick="closeHistoryDetailModal()">&times;</button>
            </div>
            <div id="modalContent" style="padding: 1.5rem;">
            </div>
        </div>
    </div>

    <script>
        const histories = <?php echo json_encode($histories); ?>;
        
        function viewHistoryDetails(historyId) {
            const history = histories.find(h => h.id === historyId);
            if (!history) return;
            
            const items = JSON.parse(history.items_json);
            const modal = document.getElementById('historyDetailModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            
            modalTitle.textContent = history.title;
            
            let itemsHtml = '<div class="items-container">';
            items.forEach(item => {
                const checked = item.is_checked ? 'checked' : '';
                const checkMark = item.is_checked ? '✓' : '';
                itemsHtml += `
                    <div class="item-card-history ${checked}">
                        <div class="item-checkbox-history ${checked}">${checkMark}</div>
                        <div class="item-content-history">
                            <div class="item-main">
                                <h4>${item.item_name}</h4>
                                <div class="item-meta">
                                    <span class="item-quantity">📦 ${item.quantity}</span>
                                    <span class="item-category">🏷️ ${item.category}</span>
                                    <span class="item-priority priority-${item.priority}">
                                        ${item.priority === 'rendah' ? '🟢' : item.priority === 'sedang' ? '🟡' : '🔴'}
                                        ${item.priority.charAt(0).toUpperCase() + item.priority.slice(1)}
                                    </span>
                                </div>
                                ${item.notes ? `<div class="item-notes">💭 ${item.notes}</div>` : ''}
                            </div>
                            <div class="item-price-history">
                                Rp ${parseFloat(item.price).toLocaleString('id-ID')}
                            </div>
                        </div>
                    </div>
                `;
            });
            itemsHtml += '</div>';
            
            modalContent.innerHTML = itemsHtml;
            modal.classList.add('active');
        }
        
        function closeHistoryDetailModal() {
            document.getElementById('historyDetailModal').classList.remove('active');
        }
        
        async function deleteHistory(historyId) {
            if (!confirm('Yakin ingin menghapus riwayat ini?')) {
                return;
            }
            
            try {
                const response = await fetch('php/delete-history.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ history_id: historyId })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan');
            }
        }
        
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>