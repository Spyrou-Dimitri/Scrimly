<?php

use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    //Composant livewire a afficher
    public ?string $current = null;
    //Potentiellement nul parce que je ne l'utilise pas pour le create
    public ?string $model_id = null;
    //Je l'utilise pour le add_note afin de passer le model que je veux pour le notable_type
    public ?string $model_type = null;

    #[On('open_modal')]
    public function open(array $payload): void
    {
        $this->current = $payload['form'];
        $this->model_id = $payload['model_id'] ?? null;
        $this->model_type = $payload['model_type'] ?? null;
    }

    #[On('close_modal')]
    public function close(): void
    {
        $this->current = null;
        $this->model_id = null;
        $this->model_type = null;
    }

};
?>

<div>
    @if(!is_null($current))
        <livewire:is :component="$current"  :model_id="$model_id" :model_type="$model_type"/>
    @endif
</div>
