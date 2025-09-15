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
  </script>
  @endpush
</x-body>