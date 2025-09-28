<?php

namespace App\Livewire\Setting;

use App\Models\AkunKas;
use App\Models\Setting;
use Livewire\Component;

class Index extends Component
{
    public $settings = [
        'presentase'     => 0,
        // 'pajak'          => 0,
        // 'profit'         => 0,
        'akun_penjualan' => null,
    ];

    public $akunKasOptions = [];

    public function mount()
    {
        foreach ($this->settings as $key => $default) {
            $this->settings[$key] = Setting::getValue($key, $default);
        }

        // ambil semua akun kas tipe masuk
        $this->akunKasOptions = AkunKas::get();
    }

    public function save($key)
    {
        $rules = [
            "settings.presentase"     => 'required|numeric|min:0|max:100',
            // "settings.pajak"          => 'required|numeric|min:0|max:100',
            // "settings.profit"         => 'required|numeric|min:0|max:100',
            "settings.akun_penjualan" => 'required|exists:akun_kas,id',
        ];

        $this->validate([
            "settings.$key" => $rules["settings.$key"],
        ]);

        Setting::setValue($key, $this->settings[$key]);

        $this->dispatch(
            'toast',
            type: 'success',
            message: ucfirst(str_replace('_', ' ', $key)) . ' berhasil disimpan!'
        );
    }



    public function render()
    {
        return view('livewire.setting.index');
    }
}
