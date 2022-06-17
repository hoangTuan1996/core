<?php

namespace MediciVN\Core\Traits;

use Illuminate\Pipeline\Pipeline;

trait HasFilters
{
    /**
     * @param array $pipelines
     * @return $this
     */
    public function filter(array $pipelines): self
    {
        $this->model = app(Pipeline::class)
                ->send($this->model->query())
                ->through($pipelines)
                ->thenReturn();

        return $this;
    }
}
