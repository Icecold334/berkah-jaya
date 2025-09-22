<x-body>
  @if ($type == 'beli')
  <livewire:pembelian.laporan />
  @else
  <livewire:penjualan.laporan />
  @endif
  @push('scripts')
  <script>
    window.addEventListener('open-pdf', event => {
          const { content, filename } = event.detail;
          const link = document.createElement('a');
          link.href = "data:application/pdf;base64," + content;
          link.download = filename;
          link.click();
      });
  </script>
  @endpush
</x-body>