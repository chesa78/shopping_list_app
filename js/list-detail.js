// Modal Functions
function openAddItemModal() {
    document.getElementById('addItemModal').classList.add('active');
}

function closeAddItemModal() {
    document.getElementById('addItemModal').classList.remove('active');
    document.getElementById('addItemForm').reset();
}

function openEditItemModal() {
    document.getElementById('editItemModal').classList.add('active');
}

function closeEditItemModal() {
    document.getElementById('editItemModal').classList.remove('active');
}

function openEditListModal() {
    document.getElementById('editListModal').classList.add('active');
}

function closeEditListModal() {
    document.getElementById('editListModal').classList.remove('active');
}

// Add Item - FIXED VERSION
async function addItem(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    console.log('=== ADD ITEM DEBUG ===');
    console.log('Form data:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    try {
        const response = await fetch('php/add-item.php', {
            method: 'POST',
            body: formData
            // JANGAN tambahkan headers Content-Type untuk FormData!
        });
        
        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Error: Server response is not valid JSON\n\n' + responseText);
            return;
        }
        
        console.log('Parsed result:', result);
        
        if (result.success) {
            alert('✅ Barang berhasil ditambahkan!');
            closeAddItemModal();
            location.reload();
        } else {
            alert('❌ Error: ' + result.message);
        }
    } catch (error) {
        console.error('Fetch error:', error);
        alert('❌ Terjadi kesalahan saat menambah item: ' + error.message);
    }
}

// Toggle Item Checkbox
async function toggleItem(itemId) {
    try {
        const response = await fetch('php/toggle-item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ item_id: itemId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const itemCard = document.querySelector(`.item-card[data-item-id="${itemId}"]`);
            if (itemCard) {
                itemCard.classList.toggle('checked');
                updateItemsStats();
            }
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    }
}

// Edit Item
function editItem(itemId) {
    const item = items.find(i => i.id == itemId);
    
    if (item) {
        document.getElementById('editItemId').value = item.id;
        document.getElementById('editItemName').value = item.item_name;
        document.getElementById('editItemQuantity').value = item.quantity;
        document.getElementById('editItemPrice').value = item.price;
        document.getElementById('editItemCategory').value = item.category;
        document.getElementById('editItemPriority').value = item.priority;
        document.getElementById('editItemNotes').value = item.notes || '';
        
        openEditItemModal();
    }
}

// Update Item
async function updateItem(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    console.log('Updating item...');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    try {
        const response = await fetch('php/update-item.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('Update item result:', result);
        
        if (result.success) {
            alert('✅ Barang berhasil diupdate!');
            closeEditItemModal();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate item: ' + error.message);
    }
}

// Delete Item
async function deleteItem(itemId) {
    if (!confirm('Yakin ingin menghapus barang ini?')) {
        return;
    }
    
    try {
        const response = await fetch('php/delete-item.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ item_id: itemId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('✅ Barang berhasil dihapus!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus item: ' + error.message);
    }
}

// Update List - FIXED VERSION
async function updateList(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    console.log('=== UPDATE LIST DEBUG ===');
    console.log('Form data:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    try {
        const response = await fetch('php/update-list.php', {
            method: 'POST',
            body: formData
        });
        
        console.log('Response status:', response.status);
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (e) {
            console.error('JSON parse error:', e);
            alert('Error: Server response is not valid JSON\n\n' + responseText);
            return;
        }
        
        console.log('Parsed result:', result);
        
        if (result.success) {
            alert('✅ Info list berhasil diupdate!');
            closeEditListModal();
            location.reload();
        } else {
            alert('❌ Error: ' + result.message);
        }
    } catch (error) {
        console.error('Fetch error:', error);
        alert('❌ Terjadi kesalahan saat mengupdate list: ' + error.message);
    }
}

// Complete List
async function completeList(listId) {
    if (!confirm('Tandai list ini sebagai selesai dan simpan ke riwayat?\n\nList akan hilang dari daftar aktif dan pindah ke halaman Riwayat.')) {
        return;
    }
    
    console.log('Completing list ID:', listId);
    
    try {
        const response = await fetch('php/complete-list.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ list_id: listId })
        });
        
        const result = await response.json();
        console.log('Complete list result:', result);
        
        if (result.success) {
            alert('✅ List berhasil disimpan ke riwayat!');
            window.location.href = 'history.php';
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
    }
}

// Update Items Statistics
function updateItemsStats() {
    const itemCards = document.querySelectorAll('.item-card');
    const totalItems = itemCards.length;
    const checkedItems = document.querySelectorAll('.item-card.checked').length;
    
    let totalPrice = 0;
    items.forEach(item => {
        totalPrice += parseFloat(item.price) || 0;
    });
    
    const totalItemsEl = document.getElementById('totalItemsCount');
    const checkedItemsEl = document.getElementById('checkedItemsCount');
    const totalPriceEl = document.getElementById('totalPrice');
    
    if (totalItemsEl) totalItemsEl.textContent = totalItems;
    if (checkedItemsEl) checkedItemsEl.textContent = checkedItems;
    if (totalPriceEl) totalPriceEl.textContent = formatRupiah(totalPrice);
}

// Format Rupiah
function formatRupiah(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateItemsStats();
    console.log('List detail page loaded. List ID:', listId);
    console.log('Total items:', items.length);
});