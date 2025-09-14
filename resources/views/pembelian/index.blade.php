<x-body>
  <livewire:pembelian.form>
    @push('scripts')
    <script>
      window.addEventListener('toast', event => {
        console.log(event);
        
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