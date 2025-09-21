<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Nota Pembelian {{ $pembelian->no_faktur }}</title>
  <style>
    body {
      font-family: sans-serif;
      font-size: 10px;
      margin: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      border: 1px solid #000;
      padding: 2px 4px;
    }

    th {
      background-color: #eee;
    }

    .text-right {
      text-align: right;
    }

    .no-border td {
      border: none;
      padding: 0 3px;
      vertical-align: top;
    }

    .company h2 {
      margin: 0;
      font-size: 12px;
      color: #c00;
    }

    .company p {
      margin: 1px 0;
    }

    .note {
      font-size: 9px;
      border: 1px solid #000;
      padding: 3px;
      width: 55%;
    }
  </style>
</head>

<body>
  {{-- HEADER --}}
  <table class="no-border">
    <tr>
      <td style="width: 15%; text-align:center;">
        <div style="width:50px;height:35px;border:1px solid #aaa;line-height:35px;font-size:8px;color:#999;">
          LOGO
        </div>
      </td>
      <td style="width: 55%;" class="company">
        <h2>{{ $alamat['label'] ?? '' }}</h2>
        <p>{!! nl2br(e($alamat['alamat'] ?? '')) !!}</p>
      </td>
      <td style="width: 30%; text-align:right; font-size:10px;">
        <p>{{ now()->format('d M Y') }}</p>
        <p>Kode: <strong>{{ $pembelian->no_faktur }}</strong></p>
      </td>
    </tr>
  </table>

  <p style="margin:4px 0;"><strong>Nota Pembelian</strong></p>

  {{-- SUPPLIER INFO --}}
  <table class="no-border" style="margin-bottom:8px;">
    <tr>
      <td style="width:20%;"><strong>Supplier</strong></td>
      <td style="width:80%;">{{ $pembelian->supplier->nama }}</td>
    </tr>
    <tr>
      <td><strong>Alamat</strong></td>
      <td>{{ $pembelian->supplier->alamat ?? '-' }}</td>
    </tr>
    <tr>
      <td><strong>Telepon</strong></td>
      <td>{{ $pembelian->supplier->telepon ?? '-' }}</td>
    </tr>
    <tr>
      <td><strong>Status Pajak</strong></td>
      <td>{{ $pembelian->kena_pajak ? 'Kena Pajak' : 'Non Pajak' }}</td>
    </tr>
  </table>

  {{-- TABEL BARANG --}}
  <table>
    <thead>
      <tr>
        <th style="width: 10%;">Qty</th>
        <th style="width: 40%;">Nama Barang</th>
        <th style="width: 20%;">Harga Beli</th>
        <th style="width: 30%;">Subtotal</th>
      </tr>
    </thead>
    <tbody>
      @php $subtotal = 0; @endphp
      @foreach ($pembelian->items as $item)
      @php $rowTotal = $item->qty * $item->harga_beli; @endphp
      <tr>
        <td class="text-right">{{ $item->qty }}</td>
        <td>{{ $item->produk->nama }}</td>
        <td class="text-right">Rp {{ number_format($item->harga_beli, 0, ',', '.') }}</td>
        <td class="text-right">Rp {{ number_format($rowTotal, 0, ',', '.') }}</td>
      </tr>
      @php $subtotal += $rowTotal; @endphp
      @endforeach
      <tr>
        <td colspan="3" class="text-right"><strong>Total</strong></td>
        <td class="text-right"><strong>Rp {{ number_format($subtotal, 0, ',', '.') }}</strong></td>
      </tr>
    </tbody>
  </table>

  {{-- FOOTER --}}
  <div style="display:flex;justify-content:space-between;margin-top:10px;">
    <div class="note">
      Barang yang sudah dibeli tidak dapat ditukar/dikembalikan
    </div>
    <div style="text-align:right;font-size:10px;">
      <p>Hormat kami,</p>
      <br><br><br>
      <p>(......................)</p>
    </div>
  </div>
</body>

</html>