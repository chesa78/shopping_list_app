function openNewListModal() {
    document.getElementById('newListModal').classList.add('active');
}

function closeNewListModal() {
    document.getElementById('newListModal').classList.remove('active');
    document.getElementById('newListForm').reset();
}

async function createNewList(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('php/create-list.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeNewListModal();
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat membuat list');
    }
}

function viewList(listId) {
    window.location.href = `list-detail.php?id=${listId}`;
}

async function deleteList(listId) {
    if (!confirm('Yakin ingin menghapus list ini?')) {
        return;
    }
    
    try {
        const response = await fetch('php/delete-list.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ list_id: listId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus list');
    }
}

function formatRupiah(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

function updateStats() {
    const listCards = document.querySelectorAll('.list-card');
    let totalBudget = 0;
    
    listCards.forEach(card => {
        const budgetText = card.querySelector('.list-budget').textContent;
        const budget = parseFloat(budgetText.replace(/[^0-9]/g, '')) || 0;
        totalBudget += budget;
    });
    
    const totalBudgetElement = document.getElementById('totalBudget');
    if (totalBudgetElement) {
        totalBudgetElement.textContent = formatRupiah(totalBudget);
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

document.addEventListener('DOMContentLoaded', function() {
    updateStats();
});