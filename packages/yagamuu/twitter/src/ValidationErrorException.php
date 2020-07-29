<?php
declare(strict_types=1);

namespace yagamuu\TwitterClientForRtainjapan;

use RuntimeException;
use Throwable;

class ValidationErrorException extends RuntimeException
{
    public function __construct(array $errors, int $code = 0, Throwable $previous = null)
    {
        $messages = [];
        foreach ($errors as $key => $error) {
            $messages[] = $key . '="' . $error . '"';
        }
        return parent::__construct(
            implode(';', $messages),
            $code,
            $previous
        );
    }
}
