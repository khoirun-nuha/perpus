    </div> 
    <!-- End Main Content -->

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/script.js"></script>
    
    <style>
        .dataTables_wrapper .row:first-child,
        .dataTables_wrapper .row:last-child {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            margin-top: 1rem;
        }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
            text-align: left;
        }
        .dataTables_wrapper .dataTables_filter {
            justify-content: flex-end;
        }
        .dataTables_wrapper .dataTables_paginate {
            justify-content: flex-end;
        }
        .dataTables_wrapper .dataTables_length select {
            margin: 0 5px;
        }
    </style>
    <script>
        $(document).ready(function() {
            $('.data-table').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json"
                },
                "dom": '<"row"<"col-sm-6"l><"col-sm-6"f>>rt<"row"<"col-sm-6"i><"col-sm-6"p>>'
            });
        });

        function confirmDelete(url) {
            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            })
        }
    </script>
</body>
</html>
