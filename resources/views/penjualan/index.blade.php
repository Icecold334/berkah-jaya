<x-body>
    <livewire:penjualan.form />
    @push('scripts')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('focus-search', () => {
                    let input = document.getElementById('searchInput');
                    if (input) {
                        input.focus();
                    }
                });
            });
            window.addEventListener('toast', event => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: event.detail.type,
                    title: event.detail.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                })
            });
        </script>
    @endpush
</x-body>
