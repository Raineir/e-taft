// Document ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables if any table has the datatable class
    if (document.querySelector('.datatable')) {
        $('.datatable').DataTable({
            responsive: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                paginate: {
                    previous: "&laquo;",
                    next: "&raquo;"
                }
            }
        });
    }
    
    // Confirm before delete
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this record?')) {
                e.preventDefault();
            }
        });
    });
    
    // Form validation
    document.querySelectorAll('form.needs-validation').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            form.classList.add('was-validated');
        }, false);
    });
    
    // Dynamic grade level and strand selection
    const gradeLevelSelect = document.getElementById('grade_level');
    const strandSelect = document.getElementById('strand');
    
    if (gradeLevelSelect && strandSelect) {
        gradeLevelSelect.addEventListener('change', function() {
            if (this.value >= 11 && this.value <= 12) {
                strandSelect.disabled = false;
            } else {
                strandSelect.disabled = true;
                strandSelect.value = 'N/A';
            }
        });
    }
    
    // Search functionality
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const query = document.getElementById('search-query').value.trim();
            
            if (query.length > 0) {
                // Perform search (this would be replaced with actual AJAX call)
                window.location.href = `search.php?q=${encodeURIComponent(query)}`;
            }
        });
    }
    
    // Print functionality
    const printButtons = document.querySelectorAll('.btn-print');
    printButtons.forEach(button => {
        button.addEventListener('click', function() {
            window.print();
        });
    });
    
    // Export to Excel
    const exportButtons = document.querySelectorAll('.btn-export');
    exportButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tableId = this.dataset.table;
            const table = document.getElementById(tableId);
            const html = table.outerHTML;
            
            // Create a Blob with the table data
            const blob = new Blob([html], {type: 'application/vnd.ms-excel'});
            const url = URL.createObjectURL(blob);
            
            // Create a download link
            const a = document.createElement('a');
            a.href = url;
            a.download = 'export.xls';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        });
    });
});