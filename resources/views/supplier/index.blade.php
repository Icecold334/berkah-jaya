<x-body>
    <livewire:supplier.index />
    @push('scripts')
        <script>
            // Confirm dialog
            window.addEventListener('confirm', event => {
                Swal.fire({
                    title: event.detail.title,
                    text: event.detail.text,
                    icon: event.detail.icon,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        Livewire.dispatch('delete', {
                            id: event.detail.id
                        });
                    }
                });
            });

            // Toast feedback
            window.addEventListener('toast', event => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: event.detail.type,
                    title: event.detail.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true,
                });
            });

            // Modal
            Livewire.on('open-modal', () => {
                window.dispatchEvent(new CustomEvent('open-modal'));
            });
            Livewire.on('close-modal', () => {
                window.dispatchEvent(new CustomEvent('close-modal'));
            });
        </script>
    @endpush
</x-body>
