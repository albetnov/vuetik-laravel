<?php

namespace Vuetik\VuetikLaravel\Exceptions;

class TwitterParseException extends \Exception
{
    public readonly string $id;

    public readonly string $url;

    public function __construct(
        array $twitter,
        int $code = 0,
        \Throwable $previous = null
    ) {
        $this->id = $twitter['id'];
        $this->url = $twitter['url'];
        parent::__construct('Failed to parse twitter content', $code, $previous);
    }

    public function getTwitterAttrs(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
        ];
    }
}
