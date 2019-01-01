<?php
declare(strict_types=1);

namespace Barryosull\Slim;

class Renderer
{
    public function render(string $view, array $data): string
    {
        foreach ($data as $key => $value) {
            $$key = $value;
        }

        ob_start();

        require __DIR__ . "/views/" . $view . ".php";

        $view = ob_get_contents();

        ob_end_clean();

        return $view;
    }
}
