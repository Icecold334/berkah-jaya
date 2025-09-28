<?php

namespace App\Livewire\Setting;

use App\Models\Setting;
use Livewire\Component;

class Index extends Component
{
    public $settings = [
        'presentase' => 0,
        'pajak'      => 0,
        // 'diskon'     => 0,
        'profit'     => 0,
    ];

    public function mount()
    {
        foreach ($this->settings as $key => $default) {
            $this->settings[$key] = Setting::getValue($key, $default);
        }
    }

    public function save($key)
    {
        $this->validate([
            "settings.$key" => 'required|numeric|min:0|max:100',
        ]);

        Setting::setValue($key, $this->settings[$key]);

        $this->dispatch(
            'toast',
            type: 'success',
            message: ucfirst($key) . ' berhasil disimpan!'
        );
    }


    public function render()
    {
        return view('livewire.setting.index');
    }
}
