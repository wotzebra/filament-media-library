<?php

namespace Wotz\MediaLibrary\Views;

class Placeholder extends Picture
{
    public function render()
    {
        return $this->view('filament-media-library::components.placeholder');
    }
}
