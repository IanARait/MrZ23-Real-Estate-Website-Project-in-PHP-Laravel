$(document).ready(function() {
    if ($.fn.dataTable && $('#propertiesTable').length && !$('#propertiesTable_wrapper').length) {
        $('#propertiesTable').DataTable({
            pageLength: 25,
            order: [[1, 'desc']],
            language: { search: 'Search:', lengthMenu: 'Show _MENU_ entries' },
            dom: '<"row"<"col-sm-6"l><"col-sm-6"f>>rtip'
        });
    }

    if ($.fn.dataTable && $('#inquiriesTable').length && !$('#inquiriesTable_wrapper').length) {
        $('#inquiriesTable').DataTable({
            order: [[5, 'desc']],
            language: { search: 'Search:', lengthMenu: 'Show _MENU_ entries' }
        });
    }

    if ($.fn.dataTable && $('#agentsTable').length && !$('#agentsTable_wrapper').length) {
        $('#agentsTable').DataTable({
            order: [[1, 'asc']],
            language: { search: 'Search:', lengthMenu: 'Show _MENU_ entries' }
        });
    }

    if ($.fn.dataTable && $('#customersTable').length && !$('#customersTable_wrapper').length) {
        $('#customersTable').DataTable({
            order: [[6, 'desc']],
            language: { search: 'Search:', lengthMenu: 'Show _MENU_ entries' }
        });
    }

    $('.select2').select2({ theme: 'bootstrap-5' });

    $('select.form-select:not(.status-select):not(.no-select2)').each(function() {
        if ($(this).find('option').length > 5) {
            $(this).select2({ theme: 'bootstrap-5' });
        }
    });

    if (typeof flatpickr !== 'undefined') {
        flatpickr('.datepicker', { dateFormat: 'Y-m-d', allowInput: true });
        flatpickr('input[type=date]', { dateFormat: 'Y-m-d', allowInput: true });
    }

    $('form').on('submit', function() {
        var btn = $(this).find('button[type=submit]');
        if (btn.length) {
            btn.prop('disabled', true);
            if (btn.find('.spinner-border').length === 0) {
                btn.html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');
            }
        }
    });

    $('.alert').delay(5000).fadeOut(500);

    $('[data-bs-toggle="tooltip"]').tooltip();

    $('.status-select').on('change', function() {
        $(this).closest('form').submit();
    });

    if ($('#propertyModal').length) {
        document.querySelectorAll('.feature-check').forEach(function(cb) {
            cb.addEventListener('change', function() {
                var valInput = document.getElementById('feat_val_' + this.value);
                if (valInput) {
                    valInput.style.display = this.checked ? 'block' : 'none';
                }
            });
        });
    }
});

function confirmDelete(msg) {
    return confirm(msg || 'Are you sure you want to delete this item?');
}
